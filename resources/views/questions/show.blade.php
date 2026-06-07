@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Banque Globale</h1>
            <p class="text-gray-500">Toutes vos questions triées par examen</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse($questions as $question)
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-start">
                    <div class="space-y-2">
                        {{-- Ici on accède à l'examen via la relation de la question --}}
                        <span class="px-2 py-1 rounded bg-blue-50 text-blue-600 text-xs font-bold uppercase">
                            Examen : {{ $question->exam->title }}
                        </span>
                        <p class="text-lg font-medium text-gray-900">{{ $question->question_text }}</p>
                    </div>
                    <span class="text-sm font-bold text-gray-400">{{ $question->points }} pts</span>
                </div>
                
                {{-- Liste des réponses en petit --}}
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach($question->answers as $answer)
                        <div class="text-sm {{ $answer->is_correct ? 'text-green-600 font-bold' : 'text-gray-400' }}">
                            • {{ $answer->answer_text }}
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-center py-10 text-gray-400">Aucune question trouvée.</p>
        @endforelse
    </div>
</div>
@endsection