<?php

namespace WinLocalInc\Chjs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use WinLocalInc\Chjs\Models\Metafield;

class MetafieldFactory extends Factory
{
    protected $model = Metafield::class;

    public function definition(): array
    {
        $key = Str::random();
        $value = Str::random();

        return [
            'key' => $key,
            'value' => $value,
            'sha1_hash' => sha1(mb_strtolower($key.''.$value)),
        ];
    }
}
