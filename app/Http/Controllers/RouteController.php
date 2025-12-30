<?php

namespace App\Http\Controllers;

use App\Models\TravelRoute;
use App\Models\Tag;
use App\Models\PointOfInterest;
use App\Models\RouteCheckpoint;
use App\Models\Review;
use App\Models\SavedRoute;
use App\Models\RouteCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except(['index', 'show', 'search']);
    }

    // Страница всех маршрутов
    public function index(Request $request)
    {
        $query = TravelRoute::where('is_published', true)->with(['user', 'tags']);

        // Фильтры
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->has('road_type') && $request->road_type) {
            $query->where('road_type', $request->road_type);
        }

        if ($request->has('tags') && is_array($request->tags)) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('tags.id', $request->tags);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sort = $request->get('sort', 'new');
        switch ($sort) {
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('scenery_rating', 'desc');
                break;
            case 'length_asc':
                $query->orderBy('length_km', 'asc');
                break;
            case 'length_desc':
                $query->orderBy('length_km', 'desc');
                break;
            default:
                $query->latest();
        }

        $routes = $query->paginate(12);
        $tags = Tag::all();

        return view('routes.index', compact('routes', 'tags', 'sort'));
    }

    // Детальная страница маршрута - УПРОЩЕННАЯ ВЕРСИЯ
    public function show($slug)
{
    try {
        // Включаем подробные ошибки
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        $route = TravelRoute::with(['user', 'points', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();
            
        // Простейший массив данных
        $data = [
            'route' => $route,
            'averageRatings' => [
                'scenery' => 0,
                'road_quality' => 0,
                'safety' => 0,
                'infrastructure' => 0
            ],
            'similarRoutes' => collect(),
            'isSaved' => false,
            'userCompleted' => false
        ];
        
        return view('routes.show', $data);
        
    } catch (\Exception $e) {
        // Показываем реальную ошибку
        dd([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

    // Форма создания маршрута
    public function create()
    {
        $tags = Tag::all();
        return view('routes.create', compact('tags'));
    }

    /**
     * Сохранение маршрута
     */
    public function store(Request $request)
    {
        Log::info('Начало создания маршрута', ['user_id' => Auth::id()]);

        DB::beginTransaction();

        try {
            // Валидация основной информации
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string|min:10',
                'length_km' => 'required|numeric|min:0.1',
                'duration_minutes' => 'required|integer|min:1',
                'difficulty' => 'required|in:easy,medium,hard',
                'road_type' => 'required|in:asphalt,gravel,offroad,mixed',
                'start_coordinates' => 'required|json',
                'end_coordinates' => 'required|json',
                'path_coordinates' => 'required|json',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'publish' => 'nullable|boolean',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'points' => 'nullable|array',
            ]);

            // Декодируем координаты
            $pathCoords = json_decode($validated['path_coordinates'], true);

            if (!is_array($pathCoords) || empty($pathCoords)) {
                throw new \Exception('Некорректные координаты маршрута');
            }

            // Создаем уникальный slug
            $slug = Str::slug($validated['title']);
            $counter = 1;
            $originalSlug = $slug;

            // Проверяем уникальность slug
            while (TravelRoute::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Создаем массив данных для маршрута
            $routeData = [
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'slug' => $slug,
                'description' => $validated['description'],
                'short_description' => Str::limit($validated['description'], 150),
                'length_km' => (float) $validated['length_km'],
                'duration_minutes' => (int) $validated['duration_minutes'],
                'difficulty' => $validated['difficulty'],
                'road_type' => $validated['road_type'],
                'start_coordinates' => $validated['start_coordinates'],
                'end_coordinates' => $validated['end_coordinates'],
                'path_coordinates' => $validated['path_coordinates'],
                'coordinates' => $validated['path_coordinates'],
                'is_published' => $request->has('publish') ? (bool) $request->publish : false,
                'scenery_rating' => 0.0,
                'road_quality_rating' => 0.0,
                'safety_rating' => 0.0,
                'infrastructure_rating' => 0.0,
                'views_count' => 0,
                'favorites_count' => 0,
                'completions_count' => 0,
                'is_featured' => false,
            ];

            // Создаем маршрут
            $route = TravelRoute::create($routeData);

            // Загрузка обложки
            if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
                try {
                    $path = $request->file('cover_image')->store('routes/covers', 'public');
                    $route->cover_image = $path;
                    $route->save();
                } catch (\Exception $e) {
                    Log::warning('Не удалось загрузить обложку: ' . $e->getMessage());
                }
            }

            // Привязка тегов
            if ($request->has('tags') && is_array($request->tags)) {
                try {
                    $route->tags()->attach($request->tags);
                } catch (\Exception $e) {
                    Log::warning('Ошибка привязки тегов: ' . $e->getMessage());
                }
            }

            // Обработка точек интереса
            if ($request->has('points') && is_array($request->points)) {
                foreach ($request->points as $index => $pointData) {
                    if (!isset($pointData['title']) || !isset($pointData['type']) ||
                        !isset($pointData['lat']) || !isset($pointData['lng'])) {
                        continue;
                    }

                    try {
                        $point = PointOfInterest::create([
                            'route_id' => $route->id,
                            'title' => $pointData['title'],
                            'description' => $pointData['description'] ?? null,
                            'type' => $pointData['type'],
                            'lat' => (float) $pointData['lat'],
                            'lng' => (float) $pointData['lng'],
                            'order' => $index,
                        ]);

                        // Обработка фотографий точки
                        if (isset($pointData['photos']) && is_array($pointData['photos'])) {
                            $photoPaths = [];
                            foreach ($pointData['photos'] as $photo) {
                                if ($photo && $photo->isValid()) {
                                    try {
                                        $photoPath = $photo->store('points/photos', 'public');
                                        $photoPaths[] = $photoPath;
                                    } catch (\Exception $e) {
                                        continue;
                                    }
                                }
                            }
                            if (!empty($photoPaths)) {
                                $point->photos = json_encode($photoPaths);
                                $point->save();
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Ошибка создания точки интереса: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('routes.show', $route->slug)
                ->with('success', 'Маршрут успешно создан!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ошибка создания маршрута: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Произошла ошибка при создании маршрута: ' . $e->getMessage());
        }
    }

    // Форма редактирования маршрута
    public function edit(TravelRoute $route)
    {
        $this->authorize('update', $route);

        $tags = Tag::all();
        return view('routes.edit', compact('route', 'tags'));
    }

    // Обновление маршрута
    public function update(Request $request, TravelRoute $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100',
            'length_km' => 'required|numeric|min:1',
            'duration_minutes' => 'required|integer|min:10',
            'difficulty' => 'required|in:easy,medium,hard',
            'road_type' => 'required|in:asphalt,gravel,offroad,mixed',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'cover_image' => 'nullable|image|max:5120',
            'start_coordinates' => 'required|array|size:2',
            'end_coordinates' => 'required|array|size:2',
            'path_coordinates' => 'nullable|array',
            'is_published' => 'boolean',
        ]);

        // Обработка обложки
        if ($request->hasFile('cover_image')) {
            // Удаляем старую обложку
            if ($route->cover_image) {
                Storage::disk('public')->delete($route->cover_image);
            }

            $path = $request->file('cover_image')->store('routes/covers', 'public');
            $validated['cover_image'] = $path;
        } else {
            unset($validated['cover_image']);
        }

        $validated['short_description'] = Str::limit($validated['description'], 150);

        $route->update($validated);

        // Обновляем теги
        $route->tags()->sync($validated['tags'] ?? []);

        return redirect()->route('routes.show', $route)
            ->with('success', 'Маршрут успешно обновлен!');
    }

    // Удаление маршрута
    public function destroy(TravelRoute $route)
    {
        $this->authorize('delete', $route);

        // Удаляем обложку
        if ($route->cover_image) {
            Storage::disk('public')->delete($route->cover_image);
        }

        $route->delete();

        return redirect()->route('profile.routes')
            ->with('success', 'Маршрут успешно удален!');
    }

    // Сохранение маршрута в избранное
    public function save(Request $request, TravelRoute $route)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Не авторизован'], 401);
        }

        $saved = SavedRoute::firstOrCreate([
            'user_id' => $user->id,
            'route_id' => $route->id,
        ]);

        if ($saved->wasRecentlyCreated) {
            $route->increment('favorites_count');
            return response()->json(['status' => 'saved', 'count' => $route->favorites_count]);
        } else {
            $saved->delete();
            $route->decrement('favorites_count');
            return response()->json(['status' => 'removed', 'count' => $route->favorites_count]);
        }
    }

    // Добавление точки интереса
    public function addPoint(Request $request, TravelRoute $route)
    {
        $this->authorize('update', $route);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:viewpoint,cafe,hotel,attraction,gas_station,camping,photo_spot,nature,historical,other',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:2048',
        ]);

        $point = new PointOfInterest($validated);

        // Обработка фото
        if ($request->hasFile('photos')) {
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('routes/points', 'public');
                $photos[] = $path;
            }
            $point->photos = $photos;
        }

        $route->points()->save($point);

        return response()->json([
            'success' => true,
            'point' => $point->load('route')
        ]);
    }

    // Удаление точки интереса
    public function deletePoint(PointOfInterest $point)
    {
        $this->authorize('update', $point->route);

        // Удаляем фото
        if ($point->photos) {
            foreach ($point->photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $point->delete();

        return response()->json(['success' => true]);
    }

    // Экспорт маршрута в GPX
    public function exportGpx(TravelRoute $route)
    {
        $gpx = '<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="AutoRuta" xmlns="http://www.topografix.com/GPX/1/1">
    <metadata>
        <name>' . htmlspecialchars($route->title) . '</name>
        <desc>' . htmlspecialchars($route->short_description) . '</desc>
        <author>
            <name>' . htmlspecialchars($route->user->name) . '</name>
        </author>
    </metadata>
    
    <trk>
        <name>' . htmlspecialchars($route->title) . '</name>
        <trkseg>';

        if ($route->path_coordinates) {
            $pathCoords = json_decode($route->path_coordinates, true);
            foreach ($pathCoords as $coordinate) {
                $gpx .= '
            <trkpt lat="' . $coordinate[0] . '" lon="' . $coordinate[1] . '">
            </trkpt>';
            }
        }

        $gpx .= '
        </trkseg>
    </trk>';

        // Добавляем точки интереса как waypoints
        foreach ($route->points as $point) {
            $gpx .= '
    <wpt lat="' . $point->lat . '" lon="' . $point->lng . '">
        <name>' . htmlspecialchars($point->title) . '</name>
        <desc>' . htmlspecialchars($point->description ?? '') . '</desc>
        <type>' . $point->type . '</type>
    </wpt>';
        }

        $gpx .= '
</gpx>';

        return response($gpx, 200, [
            'Content-Type' => 'application/gpx+xml',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($route->title) . '.gpx"',
        ]);
    }

    // Получение данных маршрута для карты
    public function mapData(TravelRoute $route)
    {
        $data = [
            'id' => $route->id,
            'title' => $route->title,
            'start' => json_decode($route->start_coordinates, true),
            'end' => json_decode($route->end_coordinates, true),
            'path' => json_decode($route->path_coordinates, true),
            'difficulty' => $route->difficulty,
            'length' => $route->length_km,
            'difficulty_color' => $this->getDifficultyColor($route->difficulty),
            'points' => $route->points->map(function ($point) {
                return [
                    'id' => $point->id,
                    'title' => $point->title,
                    'lat' => $point->lat,
                    'lng' => $point->lng,
                    'type' => $point->type,
                    'type_label' => $point->getTypeLabelAttribute(),
                    'type_icon' => $point->getTypeIconAttribute(),
                    'description' => $point->description,
                    'photos' => $point->photos,
                ];
            })->toArray(),
        ];

        return response()->json($data);
    }

    public function complete(TravelRoute $route, Request $request)
    {
        $user = Auth::user();

        // Получаем активные квесты пользователя, которые включают этот маршрут
        $userQuests = $user->userQuests()
            ->where('status', 'started')
            ->whereHas('quest', function ($query) use ($route) {
                $query->whereHas('routes', function ($q) use ($route) {
                    $q->where('travel_routes.id', $route->id);
                });
            })
            ->with('quest')
            ->get();

        // Проверяем, не пройден ли уже этот маршрут в рамках квестов
        $completedQuests = [];
        foreach ($userQuests as $userQuest) {
            $completedRoutes = $userQuest->progress['completed_routes'] ?? [];
            if (in_array($route->id, $completedRoutes)) {
                $completedQuests[] = $userQuest->quest->id;
            }
        }

        return view('routes.complete', compact('route', 'userQuests', 'completedQuests'));
    }

    // Вспомогательные методы
    private function getDifficultyColor($difficulty)
    {
        return match ($difficulty) {
            'easy' => 'bg-green-100 text-green-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'hard' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}