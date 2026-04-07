<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasLocalizedName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentType extends Model
{
    use HasFactory, HasLocalizedName, SoftDeletes;

    protected $fillable = [
        'name_en',
        'name_ru',
        'name_he',
        'slug',
        'icon',
        'folder_name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class)->orderBy('sort_order', 'asc');
    }

    public function getActiveCustomFieldsAttribute()
    {
        return $this->customFields()->whereNull('deleted_at')->get();
    }
}
