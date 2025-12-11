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
        $this->middleware('auth')->except(['index', 'show', 'search']);
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
    public function show($id)
    {
        $route = Route::with([
            'user',
            'tags',
            'points' => function ($query) {
                $query->orderBy('order');
            },
            'reviews.user'
        ])->findOrFail($id);

        // Проверка публикации
        if (!$route->is_published && $route->user_id !== auth()->id()) {
            abort(404);
        }

        // Увеличиваем счетчик просмотров
        $route->incrementViews();

        // Проверяем, сохранен ли маршрут у текущего пользователя
        $isSaved = auth()->check() 
            ? SavedRoute::where('user_id', auth()->id())
                ->where('route_id', $route->id)
                ->exists()
            : false;

        // Получаем средние рейтинги
        $averageRatings = [
            'scenery' => $route->reviews->avg('scenery_rating') ?? 0,
            'road_quality' => $route->reviews->avg('road_quality_rating') ?? 0,
            'safety' => $route->reviews->avg('safety_rating') ?? 0,
            'infrastructure' => $route->reviews->avg('infrastructure_rating') ?? 0,
        ];

        // Похожие маршруты
        $similarRoutes = Route::published()
            ->where('id', '!=', $route->id)
            ->whereHas('tags', function ($query) use ($route) {
                $query->whereIn('tags.id', $route->tags->pluck('id'));
            })
            ->limit(4)
            ->get();

        return view('routes.show', compact('route', 'isSaved', 'averageRatings', 'similarRoutes'));
    }

    // Форма создания маршрута
    public function create()
    {
        $tags = Tag::all();
        return view('routes.create', compact('tags'));
    }

    // Сохранение маршрута
    public function store(Request $request)
    {
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
            'points' => 'nullable|array',
            'points.*.title' => 'required|string|max:255',
            'points.*.description' => 'nullable|string',
            'points.*.type' => 'required|in:viewpoint,cafe,hotel,attraction,gas_station,camping,photo_spot,nature,historical,other',
            'points.*.lat' => 'required|numeric|between:-90,90',
            'points.*.lng' => 'required|numeric|between:-180,180',
            'points.*.photos' => 'nullable|array',
            'points.*.photos.*' => 'image|max:2048',
        ]);

        // Обработка обложки
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('routes/covers', 'public');
            $validated['cover_image'] = $path;
        }

        // Создаем маршрут
        $route = Auth::user()->routes()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . time(),
            'description' => $validated['description'],
            'short_description' => Str::limit($validated['description'], 150),
            'length_km' => $validated['length_km'],
            'duration_minutes' => $validated['duration_minutes'],
            'difficulty' => $validated['difficulty'],
            'road_type' => $validated['road_type'],
            'cover_image' => $validated['cover_image'] ?? null,
            'start_coordinates' => $validated['start_coordinates'],
            'end_coordinates' => $validated['end_coordinates'],
            'path_coordinates' => $validated['path_coordinates'] ?? [],
            'is_published' => $request->has('publish'),
        ]);

        // Привязываем теги
        if (!empty($validated['tags'])) {
            $route->tags()->attach($validated['tags']);
        }

        // Создаем точки интереса
        if (!empty($validated['points'])) {
            foreach ($validated['points'] as $index => $pointData) {
                $point = new PointOfInterest([
                    'title' => $pointData['title'],
                    'description' => $pointData['description'] ?? null,
                    'type' => $pointData['type'],
                    'lat' => $pointData['lat'],
                    'lng' => $pointData['lng'],
                    'order' => $index,
                ]);

                // Обработка фото точек
                if (!empty($pointData['photos'])) {
                    $photos = [];
                    foreach ($pointData['photos'] as $photo) {
                        $path = $photo->store('routes/points', 'public');
                        $photos[] = $path;
                    }
                    $point->photos = $photos;
                }

                $route->points()->save($point);
            }
        }

        return redirect()->route('routes.show', $route)
            ->with('success', 'Маршрут успешно создан!');
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
}