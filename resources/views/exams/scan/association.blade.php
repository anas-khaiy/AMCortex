@extends('layouts.app')

@section('content')
<h2>Analyse AMC</h2>

<form method="POST" action="{{ route('exams.scan.analyse', $exam->id) }}">
    @csrf
    <button type="submit">Lancer analyse</button>
</form>
@endsection