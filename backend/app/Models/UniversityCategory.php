<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UniversityCategory extends Model
{
    protected $fillable = ['name', 'name_en', 'slug', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function universities(): HasMany
    {
        return $this->hasMany(University::class);
    }
}
