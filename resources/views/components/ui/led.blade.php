@props(['color' => null, 'on' => true])
<span {{ $attributes->merge(['class' => 'led '.($on ? 'led-on' : 'led-off')]) }} @if($color) style="background: {{ $color }}; box-shadow: 0 0 0 3px color-mix(in srgb, {{ $color }} 18%, transparent), 0 0 10px color-mix(in srgb, {{ $color }} 60%, transparent)" @endif aria-hidden="true"></span>
