@foreach (['colors', 'typography', 'spacing', 'effects'] as $tokens)
    <link rel="stylesheet" href="{{ asset("bickerton/css/{$tokens}.css") }}">
@endforeach
<link rel="stylesheet" href="{{ asset('css/openbka.css') }}">
