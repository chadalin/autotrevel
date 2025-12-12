<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'горы', 'color' => '#4CAF50'],
            ['name' => 'озера', 'color' => '#2196F3'],
            ['name' => 'лес', 'color' => '#8BC34A'],
            ['name' => 'реки', 'color' => '#03A9F4'],
            ['name' => 'пляж', 'color' => '#FF9800'],
            ['name' => 'история', 'color' => '#9C27B0'],
            ['name' => 'культура', 'color' => '#E91E63'],
            ['name' => 'природа', 'color' => '#009688'],
            ['name' => 'фото', 'color' => '#FF5722'],
            ['name' => 'бездорожье', 'color' => '#795548'],
        ];

        foreach ($tags as $tag) {
            Tag::create([
                'name' => $tag['name'],
                'slug' => \Illuminate\Support\Str::slug($tag['name']),
                'color' => $tag['color'],
             //   'description' => 'Описание тега ' . $tag['name'],
            ]);
        }
    }
}