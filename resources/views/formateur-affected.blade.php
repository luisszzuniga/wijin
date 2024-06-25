<?php
use Carbon\Carbon;
Carbon::setLocale('fr');
?>

<p>Vous avez été affecté à la formation {{ $formation->module->name }} pour la promotion {{ $formation->promotion->name }} chez {{ $formation->promotion->school->name }}.</p>

<p>Les dates :</p>

@foreach ($dates as $date)
    @foreach ($date as $d)
        <p>{{ ucwords(Carbon::parse($d)->isoFormat('dddd D MMMM')) }}</p>
    @endforeach
@endforeach ()