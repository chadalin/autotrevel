<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointOfInterest extends Model
{
    use HasFactory;


        protected $table = 'points_of_interest';
    protected $fillable = [
        'route_id',
        'title',
        'description',
        'type',
        'lat',
        'lng',
        'order',
        'photos',
        'metadata',
    ];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'photos' => 'array',
        'metadata' => 'array',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'viewpoint' => 'Смотровая площадка',
            'cafe' => 'Кафе/ресторан',
            'hotel' => 'Отель/гостиница',
            'attraction' => 'Достопримечательность',
            'gas_station' => 'АЗС',
            'camping' => 'Кемпинг/стоянка',
            'photo_spot' => 'Место для фото',
            'nature' => 'Природный объект',
            'historical' => 'Исторический объект',
            'other' => 'Другое',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'viewpoint' => 'fas fa-binoculars',
            'cafe' => 'fas fa-utensils',
            'hotel' => 'fas fa-bed',
            'attraction' => 'fas fa-landmark',
            'gas_station' => 'fas fa-gas-pump',
            'camping' => 'fas fa-campground',
            'photo_spot' => 'fas fa-camera',
            'nature' => 'fas fa-tree',
            'historical' => 'fas fa-monument',
            'other' => 'fas fa-map-marker-alt',
        ];

        return $icons[$this->type] ?? 'fas fa-map-marker-alt';
    }

    public function getMainPhotoAttribute()
    {
        if ($this->photos && count($this->photos) > 0) {
            return $this->photos[0];
        }

        return null;
    }
}