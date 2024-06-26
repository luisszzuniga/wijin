<?php

namespace App\Models;

use App\Observers\FormationObserver;
use Carbon\Carbon;
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

    public function getFacturationInfosAttribute(): string
    {
        return 'Module: ' . $this->module->name . '. Prix HT: ' . $this->getFormationPriceHT() . '€. Prix TTC: ' . $this->getFormationPriceTTC() . '€. Promo: ' . $this->promotion->name . '. Durée: ' . $this->getFormationDurationInHours() . 'h. Taux horaire HT: ' . $this->hourly_price_ht . '€. Formateur: ' . $this->interventions->first()->formateur->name . '. Entreprise: ' . $this->promotion->school->name . '(' . $this->promotion->school->formation_organism_num . ')';
    }

    public function getFormationDurationInHours(): float
    {
        $hours = 0;

        foreach ($this->interventions as $intervention) {
            $times = json_decode($intervention->time);
            foreach ($times as $time) {
                $start = Carbon::parse($time->start);
                $end = Carbon::parse($time->end);
                $hours += $start->diffInHours($end);
            }
        }

        return $hours;
    }

    public function getFormationPriceHT(): float
    {
        $hours = $this->getFormationDurationInHours();

        return $this->hourly_price_ht * $hours;
    }

    public function getFormationPriceTTC(): float
    {
        return $this->getFormationPriceHT() * 1.2;
    }
}
