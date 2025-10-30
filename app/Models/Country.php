<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Country extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'name_normalized',
        'capital',
        'region',
        'population',
        'currency_code',
        'exchange_rate',
        'estimated_gdp',
        'flag_url',
        'last_refreshed_at',
    ];

    protected $casts = [
        'population' => 'integer',
        'exchange_rate' => 'double',
        'estimated_gdp' => 'double',
        'last_refreshed_at' => 'datetime',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            if ($model->isDirty('name')) {
                $model->name_normalized = Str::slug(mb_strtolower($model->name));
            }
        });
    }
}




