<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'quest_id',
        'title',
        'description',
        'type', // text, image, code, cipher, location, puzzle
        'content', // JSON с данными задания
        'order',
        'points',
        'time_limit_minutes',
        'hints_available',
        'required_answer',
        'next_task_id',
        'location_id', // Привязка к конкретному месту
        'is_required'
    ];

    protected $casts = [
        'content' => 'array',
        'is_required' => 'boolean'
    ];

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function location()
    {
        return $this->belongsTo(PointOfInterest::class, 'location_id');
    }

    public function nextTask()
    {
        return $this->belongsTo(QuestTask::class, 'next_task_id');
    }

    public function userProgress()
    {
        return $this->hasMany(QuestTaskProgress::class);
    }

    // Методы для работы с заданиями
    public function getFormattedContent()
    {
        switch ($this->type) {
            case 'text':
                return [
                    'text' => $this->content['text'] ?? '',
                    'background' => $this->content['background'] ?? null,
                ];
                
            case 'image':
                return [
                    'image_url' => asset('storage/' . ($this->content['image'] ?? '')),
                    'caption' => $this->content['caption'] ?? '',
                    'question' => $this->content['question'] ?? '',
                ];
                
            case 'code':
                return [
                    'description' => $this->content['description'] ?? '',
                    'code_format' => $this->content['format'] ?? 'numeric',
                    'length' => $this->content['length'] ?? 4,
                    'hint' => $this->content['hint'] ?? '',
                ];
                
            case 'cipher':
                return [
                    'cipher_text' => $this->content['text'] ?? '',
                    'cipher_type' => $this->content['type'] ?? 'caesar',
                    'key' => $this->content['key'] ?? null,
                    'description' => $this->content['description'] ?? 'Расшифруйте текст',
                ];
                
            case 'location':
                return [
                    'coordinates' => $this->content['coordinates'] ?? null,
                    'radius_meters' => $this->content['radius'] ?? 50,
                    'question' => $this->content['question'] ?? 'Доберитесь до указанной точки',
                    'clue' => $this->content['clue'] ?? '',
                ];
                
            case 'puzzle':
                return [
                    'puzzle_type' => $this->content['type'] ?? 'riddle',
                    'question' => $this->content['question'] ?? '',
                    'options' => $this->content['options'] ?? [],
                    'correct_index' => $this->content['correct'] ?? 0,
                ];
                
            default:
                return $this->content;
        }
    }

    public function getHints()
    {
        return $this->content['hints'] ?? [
            [
                'type' => 'location',
                'text' => 'Объект находится в районе...',
                'points_cost' => 10,
                'available_after_minutes' => 5,
            ],
            [
                'type' => 'decryption',
                'text' => 'Используйте шифр Цезаря со сдвигом...',
                'points_cost' => 15,
                'available_after_minutes' => 10,
            ],
            [
                'type' => 'direct',
                'text' => 'Ответ: [ПРАВИЛЬНЫЙ_ОТВЕТ]',
                'points_cost' => 25,
                'available_after_minutes' => 15,
            ]
        ];
    }

    public function checkAnswer($userAnswer)
    {
        $correctAnswer = $this->required_answer;
        
        if ($this->type === 'cipher') {
            // Для шифров проверяем разные варианты расшифровки
            return $this->checkCipherAnswer($userAnswer);
        }
        
        if ($this->type === 'location') {
            // Для локаций проверяем расстояние
            return $this->checkLocationAnswer($userAnswer);
        }
        
        return strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
    }

    private function checkCipherAnswer($userAnswer)
    {
        $correctAnswer = $this->required_answer;
        $userAnswer = strtolower(trim($userAnswer));
        $correctAnswer = strtolower(trim($correctAnswer));
        
        // Допускаем небольшие опечатки
        similar_text($userAnswer, $correctAnswer, $percent);
        return $percent >= 85; // 85% совпадения достаточно
    }

    private function checkLocationAnswer($userCoordinates)
    {
        if (!isset($this->content['coordinates'])) {
            return false;
        }
        
        $targetLat = $this->content['coordinates']['lat'];
        $targetLng = $this->content['coordinates']['lng'];
        $radius = $this->content['radius'] ?? 50;
        
        $distance = $this->calculateDistance(
            $targetLat, 
            $targetLng, 
            $userCoordinates['lat'], 
            $userCoordinates['lng']
        );
        
        return $distance <= $radius;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // в метрах
        
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;
        
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));
        
        return $angle * $earthRadius;
    }

    // Добавляем связь с заданиями
    public function tasks()
    {
        return $this->hasMany(QuestTask::class)->orderBy('order');
    }


      /**
     * Проверить, можно ли выполнить задание в текущей точке
     */
    public function canBeCompleted($checkpoint = null)
    {
        // Если задание не привязано к локации, его можно выполнить в любое время
        if ($this->location_id === null) {
            return true;
        }
        
        // Если задание привязано к локации, проверяем, совпадает ли с текущей точкой
        if ($checkpoint && $this->location_id == $checkpoint->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Accessor для иконки типа задания
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'text' => 'fas fa-font',
            'image' => 'fas fa-image',
            'code' => 'fas fa-code',
            'cipher' => 'fas fa-key',
            'location' => 'fas fa-map-marker-alt',
            'puzzle' => 'fas fa-puzzle-piece',
            'quiz' => 'fas fa-question-circle'
        ];
        
        return $icons[$this->type] ?? 'fas fa-tasks';
    }

    /**
     * Accessor для метки типа задания
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'text' => 'Текстовое',
            'image' => 'Фотография',
            'code' => 'Код',
            'cipher' => 'Шифр',
            'location' => 'Локация',
            'puzzle' => 'Головоломка',
            'quiz' => 'Викторина'
        ];
        
        return $labels[$this->type] ?? 'Задание';
    }
}