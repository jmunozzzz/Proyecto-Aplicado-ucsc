{{-- ═══════════════════════════════════════════════
    VISTA: landing/index.blade.php
    Página principal — Neuro Management Landing Page
    Extiende el layout principal y ensambla
    todos los partials en orden narrativo correcto
══════════════════════════════════════════════ --}}

@extends('layouts.app')

@section('title', 'Neuro Management | Jorge Vargas — Consultoría Ejecutiva de Alto Nivel')

@section('content')

    {{-- ① Hero — Impactar desde el primer segundo --}}
    @include('partials.hero')

    {{-- ② El Problema — Conectar con el dolor del cliente --}}
    @include('partials.problema')

    {{-- ③ Propuesta de Valor — Los 4 pilares Neuro Management --}}
    @include('partials.propuesta')

    {{-- ④ Jorge en Acción — Multimedia Hub: YT · IG · LinkedIn --}}
    @include('partials.jorge-en-accion')

    {{-- ⑤ Testimonios — Prueba social & casos de éxito --}}
    @include('partials.testimonios')

    {{-- ⑥ Resultados — Métricas & stats animados --}}
    @include('partials.resultados')

    {{-- ⑦ Contacto — Formulario de conversión con validación Laravel --}}
    @include('partials.contacto')

@endsection
