<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Добавление отзыва
    public function store(Request $request, Route $route)
    {
        // Проверяем, не оставлял ли пользователь уже отзыв
        $existingReview = Review::where('user_id', Auth::id())
            ->where('route_id', $route->id)
            ->first();

        if ($existingReview) {
            return redirect()->back()
                ->with('error', 'Вы уже оставляли отзыв на этот маршрут');
        }

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
            'scenery_rating' => 'required|integer|between:1,5',
            'road_quality_rating' => 'required|integer|between:1,5',
            'safety_rating' => 'required|integer|between:1,5',
            'infrastructure_rating' => 'required|integer|between:1,5',
        ]);

        $review = new Review($validated);
        $review->user_id = Auth::id();
        $review->route_id = $route->id;
        $review->save();

        // Обновляем средние рейтинги маршрута
        $this->updateRouteRatings($route);

        return redirect()->back()
            ->with('success', 'Отзыв успешно добавлен!');
    }

    // Обновление отзыва
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
            'scenery_rating' => 'required|integer|between:1,5',
            'road_quality_rating' => 'required|integer|between:1,5',
            'safety_rating' => 'required|integer|between:1,5',
            'infrastructure_rating' => 'required|integer|between:1,5',
        ]);

        $review->update($validated);

        // Обновляем средние рейтинги маршрута
        $this->updateRouteRatings($review->route);

        return redirect()->back()
            ->with('success', 'Отзыв успешно обновлен!');
    }

    // Удаление отзыва
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        
        $route = $review->route;
        $review->delete();

        // Обновляем средние рейтинги маршрута
        $this->updateRouteRatings($route);

        return redirect()->back()
            ->with('success', 'Отзыв успешно удален!');
    }

    // Обновление рейтингов маршрута
    private function updateRouteRatings(Route $route)
    {
        $reviews = $route->reviews;

        if ($reviews->count() > 0) {
            $route->update([
                'scenery_rating' => $reviews->avg('scenery_rating'),
                'road_quality_rating' => $reviews->avg('road_quality_rating'),
                'safety_rating' => $reviews->avg('safety_rating'),
                'infrastructure_rating' => $reviews->avg('infrastructure_rating'),
            ]);
        } else {
            $route->update([
                'scenery_rating' => 0,
                'road_quality_rating' => 0,
                'safety_rating' => 0,
                'infrastructure_rating' => 0,
            ]);
        }
    }
}