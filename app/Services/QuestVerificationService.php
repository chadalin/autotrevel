<?php

namespace App\Services;

use App\Models\Route;
use App\Models\RouteCompletion;
use App\Models\User;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QuestVerificationService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Проверяет, можно ли зачесть прохождение маршрута
     */
    public function verifyRouteCompletion(User $user, Route $route, $photo, $questId = null): array
    {
        try {
            // 1. Проверяем, что файл является изображением
            if (!$photo->isValid() || !$this->isImage($photo)) {
                return [
                    'success' => false,
                    'message' => 'Файл не является изображением'
                ];
            }

            // 2. Проверяем размер файла (максимум 10MB)
            if ($photo->getSize() > 10 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'Размер файла не должен превышать 10MB'
                ];
            }

            // 3. Извлекаем EXIF данные из фото
            $exifData = $this->extractExifData($photo);
            
            if (!$exifData || !isset($exifData['GPS'])) {
                return [
                    'success' => false,
                    'message' => 'Фотография не содержит GPS данных. Разрешите доступ к геолокации в настройках камеры.'
                ];
            }

            // 4. Получаем координаты из EXIF
            $photoCoords = $this->getCoordinatesFromExif($exifData);
            
            if (!$photoCoords) {
                return [
                    'success' => false,
                    'message' => 'Не удалось определить координаты из фотографии'
                ];
            }

            // 5. Проверяем, что фото сделано недавно (в течение 24 часов)
            if (!$this->isPhotoRecent($exifData)) {
                return [
                    'success' => false,
                    'message' => 'Фотография должна быть сделана в течение последних 24 часов'
                ];
            }

            // 6. Проверяем, что пользователь находится вблизи маршрута
            $routeCoords = $this->getRouteCoordinates($route);
            $isNearRoute = $this->isNearRoute($photoCoords, $routeCoords);

            if (!$isNearRoute) {
                return [
                    'success' => false,
                    'message' => 'Вы находитесь слишком далеко от маршрута. Максимальное расстояние: 500 метров.'
                ];
            }

            // 7. Проверяем уникальность фото (хэш)
            $photoHash = $this->getPhotoHash($photo);
            $existingPhoto = RouteCompletion::where('photo_hash', $photoHash)->exists();
            
            if ($existingPhoto) {
                return [
                    'success' => false,
                    'message' => 'Это фото уже использовалось для верификации'
                ];
            }

            // 8. Сохраняем фото
            $photoPath = $this->savePhoto($photo, $user->id, $route->id);

            // 9. Создаем запись о прохождении
            $completion = RouteCompletion::create([
                'user_id' => $user->id,
                'route_id' => $route->id,
                'quest_id' => $questId,
                'photo_path' => $photoPath,
                'photo_hash' => $photoHash,
                'verification_data' => [
                    'coordinates' => $photoCoords,
                    'accuracy' => $exifData['GPS']['GPSDOP'] ?? 50,
                    'timestamp' => $exifData['DateTimeOriginal'] ?? now()->toDateTimeString(),
                    'device' => $exifData['Make'] ?? 'Unknown',
                    'exif' => $exifData
                ],
                'verified' => true,
                'completed_at' => now(),
            ]);

            // 10. Обновляем прогресс в квесте (если есть)
            if ($questId) {
                $this->updateQuestProgress($user, $questId, $route->id);
            }

            return [
                'success' => true,
                'completion' => $completion,
                'message' => 'Маршрут успешно пройден!'
            ];

        } catch (\Exception $e) {
            Log::error('Ошибка верификации маршрута: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'route_id' => $route->id,
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Произошла ошибка при проверке фотографии. Пожалуйста, попробуйте еще раз.'
            ];
        }
    }

    /**
     * Проверяет, является ли файл изображением
     */
    private function isImage($file): bool
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($file->getMimeType(), $allowedMimes);
    }

    /**
     * Извлекает EXIF данные из фото
     */
    private function extractExifData($photo): ?array
    {
        try {
            $image = $this->imageManager->read($photo->getRealPath());
            return $image->exif() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Получает координаты из EXIF данных
     */
    private function getCoordinatesFromExif(array $exifData): ?array
    {
        if (!isset($exifData['GPS'])) {
            return null;
        }

        $gps = $exifData['GPS'];

        if (isset($gps['GPSLatitude']) && isset($gps['GPSLongitude'])) {
            $lat = $this->gpsToDecimal($gps['GPSLatitude'], $gps['GPSLatitudeRef'] ?? 'N');
            $lng = $this->gpsToDecimal($gps['GPSLongitude'], $gps['GPSLongitudeRef'] ?? 'E');
            
            return ['lat' => $lat, 'lng' => $lng];
        }

        return null;
    }

    /**
     * Конвертирует GPS координаты из формата EXIF в десятичные градусы
     */
    private function gpsToDecimal($coords, $hemisphere): float
    {
        if (is_string($coords)) {
            $coords = explode(',', $coords);
        }

        $degrees = count($coords) > 0 ? $this->gpsCoordToNumber($coords[0]) : 0;
        $minutes = count($coords) > 1 ? $this->gpsCoordToNumber($coords[1]) : 0;
        $seconds = count($coords) > 2 ? $this->gpsCoordToNumber($coords[2]) : 0;

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        return ($hemisphere == 'S' || $hemisphere == 'W') ? $decimal * -1 : $decimal;
    }

    /**
     * Конвертирует координату в число
     */
    private function gpsCoordToNumber($coord): float
    {
        if (is_numeric($coord)) {
            return (float) $coord;
        }

        $parts = explode('/', $coord);
        if (count($parts) == 1) {
            return (float) $parts[0];
        }

        return (float) $parts[0] / (float) $parts[1];
    }

    /**
     * Проверяет, что фото сделано недавно
     */
    private function isPhotoRecent(array $exifData): bool
    {
        if (!isset($exifData['DateTimeOriginal'])) {
            return true; // Если дата неизвестна, принимаем
        }

        try {
            $photoDate = \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $exifData['DateTimeOriginal']);
            return $photoDate->diffInHours(now()) <= 24;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Получает координаты маршрута
     */
    private function getRouteCoordinates(Route $route): array
    {
        $coords = [];

        // Добавляем стартовую точку
        if ($route->start_lat && $route->start_lng) {
            $coords[] = ['lat' => $route->start_lat, 'lng' => $route->start_lng];
        }

        // Добавляем точки маршрута
        $routeCoords = json_decode($route->coordinates, true) ?? [];
        foreach ($routeCoords as $coord) {
            if (isset($coord['lat']) && isset($coord['lng'])) {
                $coords[] = ['lat' => $coord['lat'], 'lng' => $coord['lng']];
            }
        }

        // Добавляем конечную точку
        if ($route->end_lat && $route->end_lng) {
            $coords[] = ['lat' => $route->end_lat, 'lng' => $route->end_lng];
        }

        return $coords;
    }

    /**
     * Проверяет, что пользователь находится вблизи маршрута
     */
    private function isNearRoute(array $photoCoords, array $routeCoords, int $maxDistanceMeters = 500): bool
    {
        foreach ($routeCoords as $routeCoord) {
            $distance = $this->calculateDistance(
                $photoCoords['lat'],
                $photoCoords['lng'],
                $routeCoord['lat'],
                $routeCoord['lng']
            );

            if ($distance <= $maxDistanceMeters) {
                return true;
            }
        }

        return false;
    }

    /**
     * Рассчитывает расстояние между двумя точками в метрах
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Радиус Земли в метрах

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) * sin($deltaLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Генерирует хэш фото для проверки уникальности
     */
    private function getPhotoHash($photo): string
    {
        return hash_file('sha256', $photo->getRealPath());
    }

    /**
     * Сохраняет фото
     */
    private function savePhoto($photo, $userId, $routeId): string
    {
        $path = "quests/completions/{$userId}/{$routeId}/" . uniqid() . '.' . $photo->getClientOriginalExtension();
        
        Storage::disk('public')->put($path, file_get_contents($photo->getRealPath()));
        
        return $path;
    }

    /**
     * Обновляет прогресс квеста
     */
    private function updateQuestProgress(User $user, $questId, $routeId): void
    {
        $userQuest = $user->userQuests()
            ->where('quest_id', $questId)
            ->where('status', 'started')
            ->first();

        if (!$userQuest) {
            return;
        }

        // Обновляем прогресс
        $progress = $userQuest->progress ?? [];
        $completedRoutes = $progress['completed_routes'] ?? [];
        
        if (!in_array($routeId, $completedRoutes)) {
            $completedRoutes[] = $routeId;
        }

        $userQuest->update([
            'progress' => ['completed_routes' => $completedRoutes],
            'current_route_index' => count($completedRoutes),
        ]);

        // Проверяем, выполнен ли квест
        $this->checkQuestCompletion($userQuest);
    }

    /**
     * Проверяет, выполнен ли квест
     */
    private function checkQuestCompletion(UserQuest $userQuest): void
    {
        $quest = $userQuest->quest;
        $requiredRoutes = $quest->requirements['routes_required'] ?? $quest->routes()->count();
        $completedRoutes = count($userQuest->progress['completed_routes'] ?? []);

        if ($completedRoutes >= $requiredRoutes) {
            $userQuest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Начисляем награду
            $user = $userQuest->user;
            $user->increment('experience', $quest->reward_xp);
            
            if ($quest->badge) {
                $user->badges()->attach($quest->badge_id);
            }

            // Отправляем уведомление
            // Notification::send($user, new QuestCompletedNotification($userQuest));
        }
    }
}