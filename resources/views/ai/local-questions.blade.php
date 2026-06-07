@extends('layouts.app')

@section('title', 'Suggestions IA')

@section('content')
<div class="max-w-5xl mx-auto space-y-8">

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">

        <div class="flex justify-end">
    <a href="{{ route('questions.global') }}"
       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 font-black hover:text-[#9A0002] transition">
        Retour
        <i data-lucide="arrow-right" class="w-5 h-5"></i>
    </a>
</div>

        <h1 class="text-4xl font-black text-gray-950">Suggestions IA de questions</h1>
        
        <p class="text-gray-500 mt-2">
            Examen :
            <span class="font-black text-[#9A0002]">{{ $exam->title }}</span>
        </p>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-100 text-red-700 rounded-2xl p-4 font-bold">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('ai.local.questions.suggest', $exam->id) }}"
          class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8 space-y-5">
        @csrf

        <div>
            <label class="font-black text-gray-700">Sujet du cours</label>
            <textarea name="topic" rows="4" required
                class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] outline-none focus:border-[#9A0002]"
                placeholder="Ex : histoire, géographie, biologie, énergie, climat...">{{ old('topic', $topic) }}</textarea>
        </div>

        <button class="px-7 py-4 rounded-2xl bg-[#111827] text-white font-black hover:bg-black transition">
            Chercher des suggestions IA
        </button>
    </form>

    @if(count($suggestions))
        <div class="space-y-5">
            <h2 class="text-2xl font-black text-gray-950">Questions suggérées</h2>

            @foreach($suggestions as $index => $q)
                <div class="bg-white rounded-[2rem] border border-[#EFE6DE] shadow-lg p-6">
                    <div class="flex justify-between gap-4">
                        <div>
                            <p class="text-xs font-black text-[#9A0002] uppercase">
                                Similarité : {{ round(($q['score'] ?? 0) * 100) }}%
                                • {{ $q['language'] ?? '' }}
                                • {{ $q['category'] ?? '' }}
                            </p>

                            <h3 class="text-xl font-black text-gray-950 mt-2">
                                {{ $q['question_text'] }}
                            </h3>
                        </div>

                        <form method="POST"
                              action="{{ route('ai.local.questions.add', $exam->id) }}">
                            @csrf
                            <input type="hidden" name="index" value="{{ $index }}">

                            <button class="px-5 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition">
                                Ajouter
                            </button>
                        </form>
                    </div>

                    <div class="mt-5 grid md:grid-cols-2 gap-3">
                        @foreach($q['answers'] as $answer)
                            <div class="rounded-2xl p-4 border
                                {{ !empty($answer['is_correct'])
                                    ? 'bg-green-50 border-green-100 text-green-700'
                                    : 'bg-[#FAF7F4] border-[#EFE6DE] text-gray-600' }}">
                                {{ $answer['answer_text'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($topic)
        <div class="bg-white rounded-[2rem] border border-[#EFE6DE] p-8 text-center text-gray-500 font-bold">
            Aucune suggestion trouvée pour ce sujet.
        </div>
    @endif

</div>
@endsection