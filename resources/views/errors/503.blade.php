@extends('errors.layouts.minimal')

@section('title', 'Maintenance en cours')
@section('status', 'Maintenance en cours')

@section('content')
    @include('errors.partials.unit', [
        'code' => 'ERR-503',
        'kicker' => 'Maintenance',
        'title' => 'Mise à jour en cours.',
        'text' => "Le site est momentanément indisponible, le temps d'une mise à jour. Réessayez dans quelques minutes.",
        'links' => ['Accueil' => url('/')],
    ])
@endsection
