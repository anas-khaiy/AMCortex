@extends('layouts.admin')

@section('title', 'Statistiques')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-8">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">
                Analyse globale
            </p>

            <h1 class="text-4xl font-black text-gray-950 mt-2">
                Statistiques de la plateforme
            </h1>

            <p class="text-gray-500 mt-2">
                Vue complète des enseignants, examens, étudiants et corrections.
            </p>
        </div>
    </div>

    {{-- CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">

        <div class="bg-white rounded-[2rem] p-6 border border-[#EFE6DE] shadow-lg">
            <p class="text-gray-400 text-sm font-black uppercase">Enseignants</p>
            <h2 class="text-4xl font-black mt-3">{{ $teachers }}</h2>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border border-[#EFE6DE] shadow-lg">
            <p class="text-gray-400 text-sm font-black uppercase">Étudiants</p>
            <h2 class="text-4xl font-black mt-3">{{ $students }}</h2>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border border-[#EFE6DE] shadow-lg">
            <p class="text-gray-400 text-sm font-black uppercase">Examens</p>
            <h2 class="text-4xl font-black mt-3">{{ $exams }}</h2>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border border-[#EFE6DE] shadow-lg">
            <p class="text-gray-400 text-sm font-black uppercase">Questions</p>
            <h2 class="text-4xl font-black mt-3">{{ $questions }}</h2>
        </div>

        <div class="bg-white rounded-[2rem] p-6 border border-[#EFE6DE] shadow-lg">
            <p class="text-gray-400 text-sm font-black uppercase">Corrections</p>
            <h2 class="text-4xl font-black mt-3">{{ $sessions }}</h2>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        {{-- EXAMS PER TEACHER --}}
        <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">
            <h2 class="text-2xl font-black mb-6">
                Examens par enseignant
            </h2>

            <canvas id="teachersChart"></canvas>
        </div>

        {{-- STUDENTS PER TEACHER --}}
        <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">
            <h2 class="text-2xl font-black mb-6">
                Étudiants par enseignant
            </h2>

            <canvas id="studentsChart"></canvas>
        </div>

        {{-- QUESTIONS PER EXAM --}}
        <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">
            <h2 class="text-2xl font-black mb-6">
                Questions par examen
            </h2>

            <canvas id="questionsChart"></canvas>
        </div>

        {{-- EVOLUTION --}}
        <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">
            <h2 class="text-2xl font-black mb-6">
                Évolution des corrections
            </h2>

            <canvas id="sessionsChart"></canvas>
        </div>

    </div>

</div>

<script>

new Chart(document.getElementById('teachersChart'), {
    type: 'bar',
    data: {
        labels: @json($examsPerTeacher->pluck('teacher')),
        datasets: [{
            label: 'Examens',
            data: @json($examsPerTeacher->pluck('count')),
            backgroundColor: '#9A0002',
            borderRadius: 12
        }]
    }
});

new Chart(document.getElementById('studentsChart'), {
    type: 'doughnut',
    data: {
        labels: @json($studentsPerTeacher->pluck('teacher')),
        datasets: [{
            data: @json($studentsPerTeacher->pluck('count')),
            backgroundColor: [
                '#9A0002',
                '#C2410C',
                '#0F766E',
                '#1D4ED8',
                '#7C3AED',
                '#EA580C'
            ]
        }]
    }
});

new Chart(document.getElementById('questionsChart'), {
    type: 'line',
    data: {
        labels: @json($questionsPerExam->pluck('exam')),
        datasets: [{
            label: 'Questions',
            data: @json($questionsPerExam->pluck('count')),
            borderColor: '#9A0002',
            backgroundColor: '#9A0002',
            tension: 0.4
        }]
    }
});

new Chart(document.getElementById('sessionsChart'), {
    type: 'line',
    data: {
        labels: @json($sessionsEvolution->pluck('date')),
        datasets: [{
            label: 'Corrections',
            data: @json($sessionsEvolution->pluck('total')),
            borderColor: '#0F766E',
            backgroundColor: '#0F766E',
            tension: 0.4
        }]
    }
});

</script>

@endsection