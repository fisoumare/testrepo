@extends('errors.layouts.minimal')

@section('title', 'Erreur interne')
@section('status', 'Incident en cours')

@section('content')
    @include('errors.partials.unit', [
        'code' => 'ERR-500',
        'kicker' => 'Incident interne',
        'title' => 'Cette unité ne répond pas.',
        'text' => "Une erreur s'est produite de notre côté. Elle est enregistrée ; réessayez dans quelques instants.",
        'links' => ['Accueil' => url('/')],
    ])
@endsection
