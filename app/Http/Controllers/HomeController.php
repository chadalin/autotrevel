<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Tag;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredRoutes = Route::published()
            ->featured()
            ->with(['user', 'tags'])
            ->limit(6)
            ->get();

        $popularRoutes = Route::published()
            ->popular()
            ->with(['user', 'tags'])
            ->limit(8)
            ->get();

        $newRoutes = Route::published()
            ->with(['user', 'tags'])
            ->latest()
            ->limit(8)
            ->get();

        $tags = Tag::withCount('routes')
            ->orderBy('routes_count', 'desc')
            ->limit(12)
            ->get();

        return view('home', compact('featuredRoutes', 'popularRoutes', 'newRoutes', 'tags'));
    }

    public function search(Request $request)
    {
        $query = Route::published()->with(['user', 'tags']);

        // Поиск по тексту
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Фильтр по сложности
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        // Фильтр по типу дороги
        if ($request->has('road_type') && $request->road_type) {
            $query->where('road_type', $request->road_type);
        }

        // Фильтр по тегам
        if ($request->has('tags') && is_array($request->tags)) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('tags.id', $request->tags);
            });
        }

        // Фильтр по длине
        if ($request->has('length_min')) {
            $query->where('length_km', '>=', $request->length_min);
        }
        if ($request->has('length_max')) {
            $query->where('length_km', '<=', $request->length_max);
        }

        // Сортировка
        switch ($request->get('sort', 'popular')) {
            case 'new':
                $query->latest();
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
                $query->popular();
        }

        $routes = $query->paginate(12);

        if ($request->expectsJson()) {
            return response()->json([
                'routes' => $routes,
                'html' => view('partials.route-cards', compact('routes'))->render()
            ]);
        }

        $tags = Tag::all();
        
        return view('routes.index', compact('routes', 'tags'));
    }

    public function mapData()
    {
        $routes = Route::published()
            ->select('id', 'title', 'start_coordinates', 'end_coordinates', 'path_coordinates', 'difficulty', 'length_km')
            ->with('tags')
            ->get()
            ->map(function ($route) {
                return [
                    'id' => $route->id,
                    'title' => $route->title,
                    'start' => $route->start_coordinates,
                    'end' => $route->end_coordinates,
                    'path' => $route->path_coordinates,
                    'difficulty' => $route->difficulty,
                    'length' => $route->length_km,
                    'difficulty_color' => $route->difficulty_color,
                    'tags' => $route->tags->pluck('name')->toArray(),
                    'url' => route('routes.show', $route->id),
                ];
            });

        return response()->json($routes);
    }
}