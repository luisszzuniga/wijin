<?php

namespace App\Listeners;

use App\Enums\FormationStatusEnum;
use App\Mail\FormationBillable;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class MediaHasBeenAddedListener
{
    /**
     * Handle the event.
     *
     * @param  MediaHasBeenAddedEvent  $event
     * @return void
     */
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        // Check if the media belongs to the 'evaluation' collection
        if ($media->collection_name === 'evaluation') {
            $formation = Formation::select('formations.*', 'modules.name as module_name', 'promotions.name as promotion_name', 'schools.name as school_name')
                ->join('modules', 'formations.module_id', '=', 'modules.id')
                ->join('promotions', 'formations.promotion_id', '=', 'promotions.id')
                ->join('schools', 'promotions.school_id', '=', 'schools.id')
                ->find($media->model_id);

            if ($formation->status === FormationStatusEnum::EVALUATED->value) {
                return;
            }

            Formation::where('id', $media->model_id)
                ->update(['status' => FormationStatusEnum::EVALUATED]);

            // Send email to the user
            $admin = User::select('email')->where('role', 'admin')->first();

            Mail::to($admin->email)->send(new FormationBillable($formation));
        }
    }
}
