@extends('layouts.app')

@section('content')
<h2>Association des copies</h2>

<form method="POST" action="{{ route('exams.scan.associate', $exam->id) }}">
    @csrf
    <button type="submit">Lancer association</button>
</form>
@endsection