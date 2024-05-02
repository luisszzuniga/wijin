<?php

namespace App\Models;

use App\Observers\FormationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(FormationObserver::class)]
class Formation extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'module_id',
        'promotion_id',
        'name',
        'description',
        'hourly_price_ht',
        'aleas',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('syllabus');

        $this->addMediaCollection('evaluation');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class);
    }
}
