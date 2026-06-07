<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
AMCortex
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
<div style="text-align:center;">
    <strong style="color:#9A0002;">AMCortex</strong><br>
    Plateforme intelligente d’évaluation et d’analyse pédagogique<br><br>
    © {{ date('Y') }} AMCortex — Tous droits réservés
</div>
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>