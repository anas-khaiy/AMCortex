@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">

    <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-[#EFE6DE] p-8 shadow-xl">
        <h1 class="text-3xl font-black text-gray-900 mb-2">Importer un examen</h1>

        <p class="text-gray-500 mb-6">
            Importez un examen complet avec ses questions et réponses.
        </p>

        @if(session('error'))
            <div class="mb-5 rounded-2xl bg-red-50 border border-red-100 text-red-700 p-4 font-bold">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('exams.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="border-2 border-dashed border-[#EFE6DE] rounded-2xl p-6 text-center bg-[#FAF7F4] hover:bg-white transition">
                <i data-lucide="upload-cloud" class="w-10 h-10 mx-auto text-[#9A0002] mb-3"></i>

                <input type="file" name="file" required accept=".json,.csv,.txt"
                       class="block w-full text-sm text-gray-600">

                <p class="text-xs text-gray-400 mt-2">
                    Formats acceptés : JSON / CSV avec séparateur ;
                </p>
            </div>

            <button type="submit"
                    class="w-full py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:-translate-y-1 transition">
                Importer l'examen
            </button>
        </form>
    </div>

    <aside class="bg-white rounded-[2.5rem] border border-[#EFE6DE] p-6 shadow-xl space-y-6">
        <div>
            <h2 class="text-2xl font-black text-gray-900">Format CSV</h2>
            <p class="text-sm text-gray-500 mt-1">Utilisez le point-virgule comme séparateur.</p>

            <pre class="mt-4 text-xs bg-[#FAF7F4] border border-[#EFE6DE] rounded-2xl p-4 overflow-x-auto">title;course_name;exam_language;question_text;question_type;points_correct;points_penalty;answer_1;correct_1;answer_2;correct_2;answer_3;correct_3
                Contrôle 1;Mathématiques;fr;2 + 2 = ?;single;1;0;4;1;3;0;5;0
                Contrôle 1;Mathématiques;fr;Nombres pairs ?;multiple;2;0;2;1;4;1;5;0</pre>
        </div>

        <div>
            <h2 class="text-2xl font-black text-gray-900">Format JSON</h2>

            <pre class="mt-4 text-xs bg-[#FAF7F4] border border-[#EFE6DE] rounded-2xl p-4 overflow-x-auto">{
                  "title": "Contrôle 1",
                  "course_name": "Mathématiques",
                  "exam_language": "fr",
                  "duration": 60,
                  "questions": [
                    {
                      "question_text": "2 + 2 = ?",
                      "question_type": "single",
                      "points_correct": 1,
                      "points_penalty": 0,
                      "answers": [
                        { "answer_text": "4", "is_correct": true },
                        { "answer_text": "3", "is_correct": false }
                      ]
                    }
                  ]
                }</pre>
        </div>
    </aside>

</div>
@endsection