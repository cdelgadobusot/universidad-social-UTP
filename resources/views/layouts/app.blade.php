<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title','Universidad Social')</title>

  {{-- Vite (CSS + JS) --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="font-sans antialiased">

  {{-- Navbar (Breeze) --}}
@if (!in_array(Route::currentRouteName(), ['login', 'register', 'password.request', 'password.reset']))
    @include('layouts.navigation')
@endif


  {{-- Contenido principal --}}
  <main class="container animate-fade-in" style="min-height:60vh;">
    {{-- Flash messages --}}
    @if(session('ok'))
      <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    {{-- Soporta vistas con slot (Breeze) y con sections (tus páginas) --}}
    @if (isset($slot))
      {{ $slot }}
    @else
      @yield('content')
    @endif
  </main>

  {{-- Footer (tu componente) --}}
  @include('components.footer')

  @stack('scripts')
</body>
</html>
