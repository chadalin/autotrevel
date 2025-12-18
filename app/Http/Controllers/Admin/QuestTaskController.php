<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quest;
use App\Models\QuestTask;
use App\Models\PointOfInterest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuestTaskController extends Controller
{
    public function index(Quest $quest)
    {
        $tasks = $quest->tasks()->orderBy('order')->get();
        return view('admin.quests.tasks.index', compact('quest', 'tasks'));
    }

    public function create(Quest $quest)
    {
        $locations = PointOfInterest::all();
        $nextTasks = $quest->tasks()->get();
        
        return view('admin.quests.tasks.create', compact('quest', 'locations', 'nextTasks'));
    }

    public function store(Request $request, Quest $quest)
    {

         
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,image,code,cipher,location,puzzle,quiz',
            'order' => 'required|integer',
            'points' => 'required|integer|min:1',
            'time_limit_minutes' => 'required|integer|min:1',
            'hints_available' => 'required|integer|min:0',
            'required_answer' => 'nullable|string',
            'next_task_id' => 'nullable|exists:quest_tasks,id',
            'location_id' => 'nullable|exists:points_of_interest,id',
            'is_required' => 'boolean',
            
            // Типозависимые поля
            'content_text' => 'nullable|string',
            'content_background' => 'nullable|string',
            'content_image' => 'nullable|image|max:2048',
            'content_caption' => 'nullable|string',
            'content_question' => 'nullable|string',
            'content_description' => 'nullable|string',
            'content_format' => 'nullable|string',
            'content_length' => 'nullable|integer',
            'content_cipher_text' => 'nullable|string',
            'content_cipher_type' => 'nullable|string',
            'content_key' => 'nullable|string',
            'content_coordinates_lat' => 'nullable|numeric',
            'content_coordinates_lng' => 'nullable|numeric',
            'content_radius' => 'nullable|integer',
            'content_puzzle_type' => 'nullable|string',
            'content_options' => 'nullable|array',
            'content_correct_index' => 'nullable|integer',
            'content_hints' => 'nullable|array',
        ]);

        // Подготавливаем контент в зависимости от типа
        $content = $this->prepareContent($request, $validated['type']);

        // Загружаем изображение если есть
        if ($request->hasFile('content_image') && $request->file('content_image')->isValid()) {
            $path = $request->file('content_image')->store('quests/tasks', 'public');
            if ($validated['type'] === 'image') {
                $content['image'] = $path;
            }
        }

        QuestTask::create([
            'quest_id' => $quest->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => $content,
            'order' => $validated['order'],
            'points' => $validated['points'],
            'time_limit_minutes' => $validated['time_limit_minutes'],
            'hints_available' => $validated['hints_available'],
            'required_answer' => $validated['required_answer'],
            'next_task_id' => $validated['next_task_id'],
            'location_id' => $validated['location_id'],
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()->route('admin.quests.tasks.index', $quest)
            ->with('success', 'Задание создано успешно');
    }

    public function edit(Quest $quest, QuestTask $task)
    {
        $locations = PointOfInterest::all();
        $nextTasks = $quest->tasks()->where('id', '!=', $task->id)->get();
        
        return view('admin.quests.tasks.edit', compact('quest', 'task', 'locations', 'nextTasks'));
    }

    public function update(Request $request, Quest $quest, QuestTask $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:text,image,code,cipher,location,puzzle,quiz',
            'order' => 'required|integer',
            'points' => 'required|integer|min:1',
            'time_limit_minutes' => 'required|integer|min:1',
            'hints_available' => 'required|integer|min:0',
            'required_answer' => 'nullable|string',
            'next_task_id' => 'nullable|exists:quest_tasks,id',
            'location_id' => 'nullable|exists:points_of_interest,id',
            'is_required' => 'boolean',
            
            // Типозависимые поля
            'content_text' => 'nullable|string',
            'content_background' => 'nullable|string',
            'content_image' => 'nullable|image|max:2048',
            'content_caption' => 'nullable|string',
            'content_question' => 'nullable|string',
            'content_description' => 'nullable|string',
            'content_format' => 'nullable|string',
            'content_length' => 'nullable|integer',
            'content_cipher_text' => 'nullable|string',
            'content_cipher_type' => 'nullable|string',
            'content_key' => 'nullable|string',
            'content_coordinates_lat' => 'nullable|numeric',
            'content_coordinates_lng' => 'nullable|numeric',
            'content_radius' => 'nullable|integer',
            'content_puzzle_type' => 'nullable|string',
            'content_options' => 'nullable|array',
            'content_correct_index' => 'nullable|integer',
            'content_hints' => 'nullable|array',
        ]);

        $content = $this->prepareContent($request, $validated['type']);

        // Обновляем изображение если загружено новое
        if ($request->hasFile('content_image') && $request->file('content_image')->isValid()) {
            $path = $request->file('content_image')->store('quests/tasks', 'public');
            if ($validated['type'] === 'image') {
                $content['image'] = $path;
            }
        } elseif ($validated['type'] === 'image' && $task->content && isset($task->content['image'])) {
            // Сохраняем старое изображение
            $content['image'] = $task->content['image'];
        }

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'content' => $content,
            'order' => $validated['order'],
            'points' => $validated['points'],
            'time_limit_minutes' => $validated['time_limit_minutes'],
            'hints_available' => $validated['hints_available'],
            'required_answer' => $validated['required_answer'],
            'next_task_id' => $validated['next_task_id'],
            'location_id' => $validated['location_id'],
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()->route('admin.quests.tasks.index', $quest)
            ->with('success', 'Задание обновлено успешно');
    }

    public function destroy(Quest $quest, QuestTask $task)
    {
        $task->delete();
        
        return redirect()->route('admin.quests.tasks.index', $quest)
            ->with('success', 'Задание удалено успешно');
    }

    public function reorder(Request $request, Quest $quest)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:quest_tasks,id',
            'tasks.*.order' => 'required|integer',
        ]);

        foreach ($request->tasks as $taskData) {
            QuestTask::where('id', $taskData['id'])
                ->where('quest_id', $quest->id)
                ->update(['order' => $taskData['order']]);
        }

        return response()->json(['success' => true]);
    }

    private function prepareContent(Request $request, $type)
    {
        $content = [];

        switch ($type) {
            case 'text':
                $content = [
                    'text' => $request->input('content_text'),
                    'background' => $request->input('content_background'),
                ];
                break;

            case 'image':
                $content = [
                    'caption' => $request->input('content_caption'),
                    'question' => $request->input('content_question'),
                ];
                break;

            case 'code':
                $content = [
                    'description' => $request->input('content_description'),
                    'format' => $request->input('content_format', 'numeric'),
                    'length' => $request->input('content_length', 4),
                    'hint' => $request->input('content_hint', ''),
                ];
                break;

            case 'cipher':
                $content = [
                    'text' => $request->input('content_cipher_text'),
                    'type' => $request->input('content_cipher_type', 'caesar'),
                    'key' => $request->input('content_key'),
                    'description' => $request->input('content_description', 'Расшифруйте текст'),
                ];
                break;

            case 'location':
                $content = [
                    'coordinates' => [
                        'lat' => $request->input('content_coordinates_lat'),
                        'lng' => $request->input('content_coordinates_lng'),
                    ],
                    'radius' => $request->input('content_radius', 50),
                    'question' => $request->input('content_question', 'Доберитесь до указанной точки'),
                    'clue' => $request->input('content_clue', ''),
                ];
                break;

            case 'puzzle':
                $content = [
                    'type' => $request->input('content_puzzle_type', 'riddle'),
                    'question' => $request->input('content_question'),
                    'options' => $request->input('content_options', []),
                    'correct' => $request->input('content_correct_index', 0),
                ];
                break;
        }

        // Добавляем подсказки если есть
        if ($request->has('content_hints')) {
            $content['hints'] = $request->input('content_hints');
        }

        return array_filter($content);
    }
}