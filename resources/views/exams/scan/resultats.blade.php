@extends('layouts.app')

@section('content')
<h2>Résultats</h2>

<table border="1">
    <thead>
        <tr>
            <th>Copie</th>
            <th>Code</th>
            <th>Nom</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $r)
        <tr>
            <td>{{ $r['copie'] }}</td>
            <td>{{ $r['code'] }}</td>
            <td>{{ $r['nom'] }}</td>
            <td>{{ $r['note'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('exams.scan.export', $exam->id) }}">Télécharger CSV</a>
@endsection