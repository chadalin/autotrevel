<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestTask;
use App\Models\QuestTaskProgress;
use App\Models\Quest;
use App\Models\UserQuest;
use App\Models\CheckpointPhoto;
use App\Models\QuestProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TaskApiController extends Controller
{
    /**
     * Получить информацию о задании
     */
   public function show($id)
{
    try {
        $user = Auth::user();
        
        // 1. НАХОДИМ ЗАДАНИЕ
        $task = QuestTask::find($id);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Задание не найдено',
                'id' => $id
            ], 404);
        }
        
        // 2. ЗАГРУЖАЕМ СВЯЗИ
        // Правильно: вызываем load() на объекте модели
        $task->load(['quest', 'location']);
        
        // 3. ПРОВЕРЯЕМ QUEST
        if (!$task->quest) {
            \Log::warning('Quest not found for task', [
                'task_id' => $task->id,
                'quest_id' => $task->quest_id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Квест не найден',
                'task_id' => $task->id,
                'quest_id' => $task->quest_id
            ], 404);
        }
        
        // 4. ПРОВЕРЯЕМ ПРОГРЕСС (опционально)
        $progress = QuestTaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->first();
        
        $isCompleted = $progress && $progress->status === 'completed';
        
        // 5. ФОРМАТИРУЕМ КОНТЕНТ
        $formattedContent = $this->formatTaskContent($task);
        
        // 6. ВОЗВРАЩАЕМ ОТВЕТ
        return response()->json([
            'success' => true,
            'data' => [
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'type' => $task->type,
                    'type_label' => $this->getTaskTypeLabel($task->type),
                    'type_icon' => $this->getTaskTypeIcon($task->type),
                    'content' => $formattedContent,
                    'points' => $task->points,
                    'time_limit_minutes' => $task->time_limit_minutes,
                    'hints_available' => $task->hints_available,
                    'required_answer' => $task->required_answer,
                    'location' => $task->location,
                    'is_completed' => $isCompleted,
                    'user_answer' => $progress ? $progress->user_answer : null,
                    'metadata' => $task->metadata ?? [],
                    'quest' => [
                        'id' => $task->quest->id,
                        'title' => $task->quest->title,
                        'difficulty' => $task->quest->difficulty
                    ]
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Task API Error: ' . $e->getMessage(), [
            'id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Ошибка загрузки задания: ' . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Выполнить задание
     */
    public function complete(Request $request, $taskId)
    {
        $user = Auth::user();
        $task = QuestTask::with(['quest'])->findOrFail($taskId);
        
        // Валидация в зависимости от типа задания
        $validator = $this->getTaskValidator($task->type, $request);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Проверяем доступ к заданию
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $task->quest_id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$userQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Задание недоступно или квест не активен'
            ], 403);
        }
        
        // Проверяем, не выполнено ли уже задание
        $existingProgress = QuestTaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->where('status', 'completed')
            ->first();
        
        if ($existingProgress) {
            return response()->json([
                'success' => false,
                'message' => 'Задание уже выполнено'
            ], 400);
        }
        
        try {
            // Обрабатываем задание в зависимости от типа
            $result = $this->processTask($task, $request, $user);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
            
            // Создаем запись о прогрессе
            $taskProgress = QuestTaskProgress::create([
                'user_id' => $user->id,
                'quest_id' => $task->quest_id,
                'task_id' => $task->id,
                'status' => 'completed',
                'started_at' => now()->subMinutes(5), // Примерное время начала
                'completed_at' => now(),
                'points_earned' => $task->points,
                'user_answer' => $result['user_answer'],
                'is_correct' => $result['is_correct'],
                'metadata' => $result['metadata'] ?? [],
                'time_spent_seconds' => 300 // 5 минут в секундах
            ]);
            
            // Создаем доказательство выполнения
            if ($result['proof_data']) {
                QuestProof::create([
                    'user_id' => $user->id,
                    'quest_id' => $task->quest_id,
                    'route_id' => $request->route_id ?? null,
                    'task_id' => $task->id,
                    'type' => $task->type,
                    'file_path' => $result['proof_data']['file_path'] ?? null,
                    'secret_code' => $result['proof_data']['secret_code'] ?? null,
                    'comment' => $result['proof_data']['comment'] ?? null,
                    'metadata' => $result['proof_data']['metadata'] ?? [],
                    'approved' => true,
                    'approved_at' => now(),
                    'approved_by' => $user->id
                ]);
            }
            
            // Обновляем прогресс квеста
            $completedTasks = QuestTaskProgress::where('user_id', $user->id)
                ->where('quest_id', $task->quest_id)
                ->where('status', 'completed')
                ->count();
            
            $totalTasks = $task->quest->tasks()->count();
            $progressPercentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
            
            $userQuest->update([
                'progress_current' => $completedTasks,
                'progress_target' => $totalTasks
            ]);
            
            // Начисляем XP
            $xpEarned = $task->points * 10; // 10 XP за каждый балл задания
            $user->increment('exp', $xpEarned);
            
            // Обновляем статистику пользователя
            $this->updateUserStats($user, $xpEarned, $task->points);
            
            // Проверяем, выполнен ли весь квест
            if ($completedTasks >= $totalTasks) {
                $this->completeQuest($userQuest);
            }
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'task' => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'type' => $task->type,
                        'points' => $task->points,
                        'xp_earned' => $xpEarned,
                        'is_correct' => $result['is_correct'],
                        'user_answer' => $result['user_answer'],
                        'correct_answer' => $task->required_answer
                    ],
                    'progress' => [
                        'completed' => $completedTasks,
                        'total' => $totalTasks,
                        'percentage' => $progressPercentage
                    ],
                    'quest' => [
                        'id' => $task->quest->id,
                        'title' => $task->quest->title,
                        'reward_exp' => $task->quest->reward_exp,
                        'reward_coins' => $task->quest->reward_coins
                    ],
                    'user' => [
                        'xp' => $user->exp,
                        'level' => $user->level,
                        'coins' => $user->coins
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при выполнении задания: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Получить подсказку для задания
     */
    public function hint($taskId)
    {
        $user = Auth::user();
        $task = QuestTask::findOrFail($taskId);
        
        // Проверяем доступ к заданию
        $userQuest = UserQuest::where('user_id', $user->id)
            ->where('quest_id', $task->quest_id)
            ->where('status', 'in_progress')
            ->first();
        
        if (!$userQuest) {
            return response()->json([
                'success' => false,
                'message' => 'Задание недоступно'
            ], 403);
        }
        
        // Получаем прогресс задания
        $progress = QuestTaskProgress::where('user_id', $user->id)
            ->where('task_id', $task->id)
            ->first();
        
        // Проверяем лимит подсказок
        $hintsUsed = $progress ? ($progress->hints_used ?? []) : [];
        
        if (count($hintsUsed) >= $task->hints_available) {
            return response()->json([
                'success' => false,
                'message' => 'Лимит подсказок исчерпан'
            ], 400);
        }
        
        // Получаем подсказки из контента задания
        $content = $task->content ?? [];
        $hints = $content['hints'] ?? [];
        
        if (empty($hints)) {
            return response()->json([
                'success' => false,
                'message' => 'Подсказки недоступны для этого задания'
            ], 400);
        }
        
        // Выдаем следующую подсказку
        $hintIndex = count($hintsUsed);
        
        if ($hintIndex >= count($hints)) {
            return response()->json([
                'success' => false,
                'message' => 'Больше нет подсказок'
            ], 400);
        }
        
        // Добавляем подсказку в использованные
        $hintsUsed[] = [
            'hint' => $hints[$hintIndex],
            'used_at' => now()->toISOString()
        ];
        
        // Обновляем прогресс
        if ($progress) {
            $progress->update(['hints_used' => $hintsUsed]);
        } else {
            QuestTaskProgress::create([
                'user_id' => $user->id,
                'quest_id' => $task->quest_id,
                'task_id' => $task->id,
                'status' => 'in_progress',
                'hints_used' => $hintsUsed
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'hint' => $hints[$hintIndex],
                'hint_number' => $hintIndex + 1,
                'total_hints' => count($hints),
                'hints_remaining' => count($hints) - ($hintIndex + 1),
                'penalty' => $task->points * 0.1 // Штраф 10% от баллов за подсказку
            ]
        ]);
    }
    
    /**
     * Проверить ответ на задание без завершения
     */
    public function checkAnswer(Request $request, $taskId)
    {
        $user = Auth::user();
        $task = QuestTask::findOrFail($taskId);
        
        $validator = Validator::make($request->all(), [
            'answer' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $isCorrect = $this->checkTaskAnswer($task, $request->answer);
        
        return response()->json([
            'success' => true,
            'data' => [
                'is_correct' => $isCorrect,
                'hint' => !$isCorrect ? $this->getHintForAnswer($task, $request->answer) : null
            ]
        ]);
    }
    
    /**
     * Форматировать контент задания
     */
    private function formatTaskContent($task)
    {
        $content = $task->content ?? [];
        
        switch ($task->type) {
            case 'quiz':
                return [
                    'question' => $content['question'] ?? 'Вопрос не указан',
                    'options' => $content['options'] ?? [],
                    'multiple_choice' => $content['multiple_choice'] ?? false,
                    'correct_answer' => $content['correct_answer'] ?? null
                ];
                
            case 'code':
                return [
                    'code' => $content['code'] ?? '',
                    'language' => $content['language'] ?? 'javascript',
                    'description' => $content['description'] ?? 'Найдите ошибку в коде или напишите решение',
                    'expected_output' => $content['expected_output'] ?? null
                ];
                
            case 'cipher':
                return [
                    'cipher_text' => $content['cipher_text'] ?? '',
                    'cipher_type' => $content['cipher_type'] ?? 'caesar',
                    'hint' => $content['hint'] ?? null,
                    'key' => $content['key'] ?? null
                ];
                
            case 'puzzle':
                return [
                    'puzzle' => $content['puzzle'] ?? '',
                    'pieces' => $content['pieces'] ?? [],
                    'solution' => $content['solution'] ?? null,
                    'hints' => $content['hints'] ?? []
                ];
                
            case 'location':
                return [
                    'coordinates' => $content['coordinates'] ?? null,
                    'radius' => $content['radius'] ?? 100, // метров
                    'description' => $content['description'] ?? 'Доберитесь до указанной точки',
                    'qr_code' => $content['qr_code'] ?? null
                ];
                
            case 'text':
                return [
                    'question' => $content['question'] ?? 'Ответьте на вопрос',
                    'max_length' => $content['max_length'] ?? 500,
                    'min_length' => $content['min_length'] ?? 10
                ];
                
            case 'image':
                return [
                    'description' => $content['description'] ?? 'Сделайте фотографию',
                    'required_elements' => $content['required_elements'] ?? [],
                    'verification_type' => $content['verification_type'] ?? 'manual'
                ];
                
            default:
                return $content;
        }
    }
    
    /**
     * Получить валидатор для типа задания
     */
    private function getTaskValidator($taskType, Request $request)
    {
        $rules = [
            'task_id' => 'required|exists:quest_tasks,id'
        ];
        
        switch ($taskType) {
            case 'text':
                $rules['answer'] = 'required|string|min:1|max:1000';
                break;
                
            case 'image':
                $rules['photo'] = 'required|image|max:10240'; // 10MB
                $rules['comment'] = 'nullable|string|max:500';
                break;
                
            case 'quiz':
                $rules['answer'] = 'required|string';
                break;
                
            case 'code':
                $rules['code'] = 'required|string';
                break;
                
            case 'cipher':
                $rules['decoded_text'] = 'required|string';
                break;
                
            case 'puzzle':
                $rules['solution'] = 'required|array';
                break;
                
            case 'location':
                $rules['latitude'] = 'required|numeric|between:-90,90';
                $rules['longitude'] = 'required|numeric|between:-180,180';
                break;
        }
        
        return Validator::make($request->all(), $rules);
    }
    
    /**
     * Обработать задание
     */
    private function processTask($task, Request $request, $user)
    {
        switch ($task->type) {
            case 'text':
                return $this->processTextTask($task, $request);
                
            case 'image':
                return $this->processImageTask($task, $request, $user);
                
            case 'quiz':
                return $this->processQuizTask($task, $request);
                
            case 'code':
                return $this->processCodeTask($task, $request);
                
            case 'cipher':
                return $this->processCipherTask($task, $request);
                
            case 'puzzle':
                return $this->processPuzzleTask($task, $request);
                
            case 'location':
                return $this->processLocationTask($task, $request);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Неизвестный тип задания'
                ];
        }
    }
    
    /**
     * Обработать текстовое задание
     */
    private function processTextTask($task, Request $request)
    {
        $isCorrect = $this->checkTextAnswer($task, $request->answer);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Ответ принят!' : 'Ответ неверный, но задание засчитано',
            'user_answer' => $request->answer,
            'is_correct' => $isCorrect,
            'proof_data' => [
                'comment' => $request->answer
            ]
        ];
    }
    
    /**
     * Обработать задание с фото
     */
    private function processImageTask($task, Request $request, $user)
    {
        try {
            $path = $request->file('photo')->store('task-photos/' . date('Y/m'), 'public');
            
            // Сохраняем фото в галерею
            $photo = CheckpointPhoto::create([
                'checkpoint_id' => $request->checkpoint_id ?? null,
                'path' => $path,
                'caption' => $request->comment ?? 'Фото для выполнения задания',
                'uploaded_by' => $user->id
            ]);
            
            return [
                'success' => true,
                'message' => 'Фото успешно загружено!',
                'user_answer' => 'photo_uploaded',
                'is_correct' => true,
                'proof_data' => [
                    'file_path' => $path,
                    'comment' => $request->comment,
                    'metadata' => [
                        'photo_id' => $photo->id,
                        'file_size' => $request->file('photo')->getSize(),
                        'mime_type' => $request->file('photo')->getMimeType()
                    ]
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при загрузке фото: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Обработать викторину
     */
    private function processQuizTask($task, Request $request)
    {
        $isCorrect = $this->checkQuizAnswer($task, $request->answer);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Правильный ответ!' : 'Неправильный ответ',
            'user_answer' => $request->answer,
            'is_correct' => $isCorrect,
            'proof_data' => [
                'answer' => $request->answer
            ]
        ];
    }
    
    /**
     * Обработать задание с кодом
     */
    private function processCodeTask($task, Request $request)
    {
        $isCorrect = $this->checkCodeAnswer($task, $request->code);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Код правильный!' : 'Код содержит ошибки',
            'user_answer' => substr($request->code, 0, 500), // Ограничиваем длину
            'is_correct' => $isCorrect,
            'proof_data' => [
                'code' => $request->code,
                'language' => $task->content['language'] ?? 'unknown'
            ]
        ];
    }
    
    /**
     * Обработать задание с шифром
     */
    private function processCipherTask($task, Request $request)
    {
        $isCorrect = $this->checkCipherAnswer($task, $request->decoded_text);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Шифр расшифрован верно!' : 'Неверная расшифровка',
            'user_answer' => $request->decoded_text,
            'is_correct' => $isCorrect,
            'proof_data' => [
                'decoded_text' => $request->decoded_text
            ]
        ];
    }
    
    /**
     * Обработать головоломку
     */
    private function processPuzzleTask($task, Request $request)
    {
        $isCorrect = $this->checkPuzzleAnswer($task, $request->solution);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Головоломка решена!' : 'Решение неверное',
            'user_answer' => json_encode($request->solution),
            'is_correct' => $isCorrect,
            'proof_data' => [
                'solution' => $request->solution
            ]
        ];
    }
    
    /**
     * Обработать задание с локацией
     */
    private function processLocationTask($task, Request $request)
    {
        $isCorrect = $this->checkLocationAnswer($task, $request->latitude, $request->longitude);
        
        return [
            'success' => true,
            'message' => $isCorrect ? 'Вы в нужном месте!' : 'Вы не в нужном месте',
            'user_answer' => "{$request->latitude},{$request->longitude}",
            'is_correct' => $isCorrect,
            'proof_data' => [
                'coordinates' => [
                    'lat' => $request->latitude,
                    'lng' => $request->longitude
                ],
                'distance' => $this->calculateDistance(
                    $request->latitude,
                    $request->longitude,
                    $task->content['coordinates']['lat'] ?? 0,
                    $task->content['coordinates']['lng'] ?? 0
                )
            ]
        ];
    }
    
    /**
     * Проверить текстовый ответ
     */
    private function checkTextAnswer($task, $answer)
    {
        $requiredAnswer = strtolower(trim($task->required_answer ?? ''));
        $userAnswer = strtolower(trim($answer));
        
        if (empty($requiredAnswer)) {
            return true; // Если правильный ответ не задан, принимаем любой
        }
        
        // Проверяем точное совпадение или синонимы
        if ($userAnswer === $requiredAnswer) {
            return true;
        }
        
        // Проверяем частичное совпадение (например, ключевые слова)
        $keywords = explode(',', $requiredAnswer);
        foreach ($keywords as $keyword) {
            if (strpos($userAnswer, trim($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Проверить ответ на викторину
     */
    private function checkQuizAnswer($task, $answer)
    {
        $correctAnswer = $task->required_answer ?? $task->content['correct_answer'] ?? '';
        
        if (empty($correctAnswer)) {
            return true;
        }
        
        return strtolower(trim($answer)) === strtolower(trim($correctAnswer));
    }
    
    /**
     * Проверить код
     */
    private function checkCodeAnswer($task, $code)
    {
        $expectedOutput = $task->content['expected_output'] ?? '';
        
        if (empty($expectedOutput)) {
            return true; // Если ожидаемый вывод не задан
        }
        
        // В реальной системе здесь была бы проверка выполнения кода
        // Пока просто проверяем наличие ключевых слов
        $requiredKeywords = $task->content['required_keywords'] ?? [];
        
        foreach ($requiredKeywords as $keyword) {
            if (strpos($code, $keyword) === false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Проверить расшифровку шифра
     */
    private function checkCipherAnswer($task, $decodedText)
    {
        $correctAnswer = $task->required_answer ?? '';
        
        if (empty($correctAnswer)) {
            return true;
        }
        
        // Удаляем пробелы и приводим к нижнему регистру для сравнения
        $normalizedCorrect = preg_replace('/\s+/', '', strtolower($correctAnswer));
        $normalizedUser = preg_replace('/\s+/', '', strtolower($decodedText));
        
        return $normalizedUser === $normalizedCorrect;
    }
    
    /**
     * Проверить решение головоломки
     */
    private function checkPuzzleAnswer($task, $solution)
    {
        $correctSolution = $task->content['solution'] ?? [];
        
        if (empty($correctSolution)) {
            return true;
        }
        
        return json_encode($solution) === json_encode($correctSolution);
    }
    
    /**
     * Проверить локацию
     */
    private function checkLocationAnswer($task, $lat, $lng)
    {
        $targetLat = $task->content['coordinates']['lat'] ?? 0;
        $targetLng = $task->content['coordinates']['lng'] ?? 0;
        $radius = $task->content['radius'] ?? 100; // метров
        
        if (!$targetLat || !$targetLng) {
            return true;
        }
        
        $distance = $this->calculateDistance($lat, $lng, $targetLat, $targetLng);
        
        return $distance <= ($radius / 1000); // Переводим метры в километры
    }
    
    /**
     * Вычислить расстояние между точками
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // км
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Получить метку типа задания
     */
    private function getTaskTypeLabel($type)
    {
        $labels = [
            'text' => 'Текстовое задание',
            'image' => 'Фотография',
            'code' => 'Код',
            'cipher' => 'Шифр',
            'location' => 'Локация',
            'puzzle' => 'Головоломка',
            'quiz' => 'Викторина'
        ];
        
        return $labels[$type] ?? 'Задание';
    }
    
    /**
     * Получить иконку типа задания
     */
    private function getTaskTypeIcon($type)
    {
        $icons = [
            'text' => 'fas fa-font',
            'image' => 'fas fa-camera',
            'code' => 'fas fa-code',
            'cipher' => 'fas fa-key',
            'location' => 'fas fa-map-marker-alt',
            'puzzle' => 'fas fa-puzzle-piece',
            'quiz' => 'fas fa-question-circle'
        ];
        
        return $icons[$type] ?? 'fas fa-tasks';
    }
    
    /**
     * Обновить статистику пользователя
     */
    private function updateUserStats($user, $xpEarned, $pointsEarned)
    {
        // Обновляем статистику пользователя
        $user->increment('exp', $xpEarned);
        
        // Проверяем повышение уровня
        $newLevel = floor($user->exp / 1000) + 1;
        if ($newLevel > $user->level) {
            $user->update(['level' => $newLevel]);
        }
        
        // Обновляем или создаем статистику
        $userStats = $user->stats()->firstOrCreate(
            ['user_id' => $user->id],
            ['total_exp' => 0, 'total_coins' => 0, 'quests_completed' => 0]
        );
        
        $userStats->increment('total_exp', $xpEarned);
    }
    
    /**
     * Завершить квест
     */
    private function completeQuest($userQuest)
    {
        $userQuest->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_current' => $userQuest->progress_target
        ]);
        
        // Награждаем пользователя
        $user = $userQuest->user;
        $quest = $userQuest->quest;
        
        if ($quest->reward_exp > 0) {
            $user->increment('exp', $quest->reward_exp);
            
            if ($user->stats) {
                $user->stats->increment('total_exp', $quest->reward_exp);
                $user->stats->increment('quests_completed');
            }
        }
        
        if ($quest->reward_coins > 0) {
            $user->increment('coins', $quest->reward_coins);
            
            if ($user->stats) {
                $user->stats->increment('total_coins', $quest->reward_coins);
            }
        }
        
        // Выдаем значок если есть
        if ($quest->badge_id) {
            $user->badges()->attach($quest->badge_id, [
                'earned_at' => now(),
                'metadata' => [
                    'quest_id' => $quest->id,
                    'quest_title' => $quest->title,
                    'completed_at' => now()
                ]
            ]);
        }
        
        // Отмечаем завершение квеста
        $user->questCompletions()->create([
            'quest_id' => $quest->id,
            'proof_data' => json_encode(['completed_via' => 'mobile_app']),
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $user->id
        ]);
    }
}