<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag_category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TagCategory::class, 'tag_category_id');
    }

    public function meals(): BelongsToMany
    {
        return $this->belongsToMany(Meal::class, 'meal_tag')->withTimestamps();
    }

    public function foodItems(): BelongsToMany
    {
        return $this->belongsToMany(FoodItem::class, 'food_item_tag')->withTimestamps();
    }
}

