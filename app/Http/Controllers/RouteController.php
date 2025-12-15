<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Tag;
use App\Models\PointOfInterest;
use App\Models\Review;
use App\Models\SavedRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    public function __construct()
    {
       // $this->middleware('auth')->except(['index', 'show', 'search']);
    }

    // Страница всех маршрутов
    public function index(Request $request)
    {
        $query = Route::published()->with(['user', 'tags']);

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
                $query->popular();
                break;
            case 'rating':
                $query->withAvg('reviews', 'scenery_rating')->orderBy('reviews_avg_scenery_rating', 'desc');
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

    // Детальная страница маршрута
   public function show(Route $route)
{
    try {
        // Увеличиваем счетчик просмотров
        $route->increment('views_count');
        
        // Загружаем все необходимые отношения
        $route->load([
            'user',
            'tags',
            'points' => function($query) {
                $query->orderBy('order');
            },
            'reviews.user'
        ]);
        
        // ВРЕМЕННО: отключаем сложный запрос для activeQuests
        $activeQuests = collect();
        
        // Похожие маршруты
        $similarRoutes = Route::whereHas('tags', function($query) use ($route) {
                $query->whereIn('tags.id', $route->tags->pluck('id'));
            })
            ->where('id', '!=', $route->id)
            ->where('is_published', true)
            ->withCount('reviews')
            ->limit(4)
            ->get();
        
        // Статистика для рейтингов
        $averageRatings = [
            'scenery' => $route->reviews->avg('scenery_rating') ?? 0,
            'road_quality' => $route->reviews->avg('road_quality_rating') ?? 0,
            'safety' => $route->reviews->avg('safety_rating') ?? 0,
            'infrastructure' => $route->reviews->avg('infrastructure_rating') ?? 0,
        ];
        
        // Проверка, сохранен ли маршрут в избранном
        $isSaved = false;
        if (auth()->check() && method_exists($route, 'favoritedByUsers')) {
            $isSaved = $route->favoritedByUsers()->where('user_id', auth()->id())->exists();
        }
        
        return view('routes.show', compact(
            'route',
            'similarRoutes',
            'averageRatings',
            'isSaved',
            'activeQuests'
        ));
        
    } catch (\Exception $e) {
        \Log::error('Ошибка при показе маршрута: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return view('routes.show', [
            'route' => $route,
            'similarRoutes' => collect(),
            'averageRatings' => [
                'scenery' => 0,
                'road_quality' => 0,
                'safety' => 0,
                'infrastructure' => 0,
            ],
            'isSaved' => false,
            'activeQuests' => collect(),
        ]);
    }
}

    // Форма создания маршрута
    public function create()
    {
        $tags = Tag::all();
        return view('routes.create', compact('tags'));
    }

    // Сохранение маршрута
  // Сохранение маршрута
// Сохранение маршрута
public function store(Request $request)
{
    // Валидация основной информации
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string|min:100',
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
        'points.*.title' => 'required|string|max:255',
        'points.*.type' => 'required|in:viewpoint,cafe,hotel,attraction,gas_station,camping,photo_spot,nature,historical,other',
        'points.*.description' => 'nullable|string',
        'points.*.lat' => 'required|numeric',
        'points.*.lng' => 'required|numeric',
    ]);
    
    // Валидация фотографий точек (отдельно, так как могут быть множественные файлы)
    if ($request->has('points')) {
        foreach ($request->points as $index => $pointData) {
            $request->validate([
                "points.{$index}.photos.*" => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
        }
    }
    
    try {
        // Декодируем координаты
        $startCoords = json_decode($validated['start_coordinates'], true);
        $endCoords = json_decode($validated['end_coordinates'], true);
        $pathCoords = json_decode($validated['path_coordinates'], true);
        
        // Создаем маршрут
        $route = auth()->user()->routes()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'length_km' => $validated['length_km'],
            'duration_minutes' => $validated['duration_minutes'],
            'duration_hours' => ceil($validated['duration_minutes'] / 60),
            'difficulty' => $validated['difficulty'],
            'road_type' => $validated['road_type'],
            'start_lat' => $startCoords[0],
            'start_lng' => $startCoords[1],
            'end_lat' => $endCoords[0],
            'end_lng' => $endCoords[1],
            'coordinates' => json_encode($pathCoords),
            'is_published' => $request->boolean('publish', true),
        ]);
        
        // Загрузка обложки
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            try {
                $path = $request->file('cover_image')->store('routes/covers', 'public');
                $route->cover_image = $path;
                $route->save();
            } catch (\Exception $e) {
                \Log::warning('Не удалось загрузить обложку: ' . $e->getMessage());
                // Продолжаем без обложки
            }
        }
        
        // Привязка тегов
        if ($request->has('tags')) {
            $route->tags()->attach($request->tags);
        }
        
        // Обработка точек интереса
        if ($request->has('points')) {
            foreach ($request->points as $index => $pointData) {
                $point = $route->points()->create([
                    'title' => $pointData['title'] ?? "Точка " . ($index + 1),
                    'type' => $pointData['type'] ?? 'other',
                    'description' => $pointData['description'] ?? null,
                    'lat' => $pointData['lat'] ?? 0,
                    'lng' => $pointData['lng'] ?? 0,
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
                                \Log::warning('Не удалось загрузить фото точки: ' . $e->getMessage());
                                continue;
                            }
                        }
                    }
                    if (!empty($photoPaths)) {
                        $point->photos = json_encode($photoPaths);
                        $point->save();
                    }
                }
            }
        }
        
        // Логируем действие
        activity()
            ->causedBy(auth()->user())
            ->performedOn($route)
            ->log('created');
        
        return redirect()->route('routes.show', $route)
            ->with('success', 'Маршрут успешно создан!');
            
    } catch (\Exception $e) {
        \Log::error('Ошибка создания маршрута: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return back()
            ->withInput()
            ->with('error', 'Произошла ошибка при создании маршрута: ' . $e->getMessage());
    }
}

    // Форма редактирования маршрута
    public function edit(Route $route)
    {
        $this->authorize('update', $route);
        
        $tags = Tag::all();
        return view('routes.edit', compact('route', 'tags'));
    }

    // Обновление маршрута
    public function update(Request $request, Route $route)
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
    public function destroy(Route $route)
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
    public function save(Request $request, Route $route)
    {
        $user = Auth::user();

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
    public function addPoint(Request $request, Route $route)
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
    public function exportGpx(Route $route)
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
            foreach ($route->path_coordinates as $coordinate) {
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
    public function mapData(Route $route)
    {
        $data = [
            'id' => $route->id,
            'title' => $route->title,
            'start' => $route->start_coordinates,
            'end' => $route->end_coordinates,
            'path' => $route->path_coordinates,
            'difficulty' => $route->difficulty,
            'length' => $route->length_km,
            'difficulty_color' => $route->difficulty_color,
            'points' => $route->points->map(function ($point) {
                return [
                    'id' => $point->id,
                    'title' => $point->title,
                    'lat' => $point->lat,
                    'lng' => $point->lng,
                    'type' => $point->type,
                    'type_label' => $point->type_label,
                    'type_icon' => $point->type_icon,
                    'description' => $point->description,
                    'photos' => $point->photos,
                ];
            })->toArray(),
        ];

        return response()->json($data);
    }

    public function complete(Route $route, Request $request)
{
    $user = Auth::user();
    
    // Получаем активные квесты пользователя, которые включают этот маршрут
    $userQuests = $user->userQuests()
        ->where('status', 'started')
        ->whereHas('quest', function($query) use ($route) {
            $query->whereHas('routes', function($q) use ($route) {
                $q->where('routes.id', $route->id);
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
}