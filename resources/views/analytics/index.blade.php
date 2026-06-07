@extends('layouts.app')

@section('title', 'Statistiques et Analyses Pédagogiques')

@section('content')
<div class="space-y-8">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-[#111827] text-white p-8 shadow-2xl">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-[#9A0002]/40 rounded-full blur-3xl"></div>
        <div class="absolute -left-20 bottom-0 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-white/50">Analyse pédagogique</p>
            <h1 class="text-4xl font-black mt-2">Statistiques</h1>
            <p class="text-white/70 mt-2">
                Suivi intelligent des performances, corrections répétées et examens à renforcer.
            </p>
        </div>
    </div>

    {{-- KPI PRINCIPAUX --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        @foreach([
            ['label' => 'Examens', 'value' => $totalExams, 'icon' => 'file-text'],
            ['label' => 'Étudiants', 'value' => $totalStudents, 'icon' => 'users'],
            ['label' => 'Questions', 'value' => $totalQuestions, 'icon' => 'help-circle'],
            ['label' => 'Corrections', 'value' => $totalSessions, 'icon' => 'clipboard-check'],
        ] as $card)
            <div class="relative overflow-hidden rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-[#9A0002]/10 rounded-full blur-2xl"></div>

                <div class="relative">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-4xl font-black text-gray-950">{{ $card['value'] }}</p>
                        <div class="w-14 h-14 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                            <i data-lucide="{{ $card['icon'] }}" class="w-6 h-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- KPI PERFORMANCES --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <p class="text-xs font-black uppercase text-gray-400">Moyenne générale</p>
            <p class="text-4xl font-black text-[#9A0002] mt-2">{{ $average }}%</p>
            <p class="text-xs text-gray-400 mt-2">Moyenne calculée selon le barème de chaque examen.</p>
        </div>

        <div class="rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <p class="text-xs font-black uppercase text-gray-400">Réussite</p>
            <p class="text-4xl font-black text-gray-950 mt-2">{{ $successRate }}%</p>
            <p class="text-xs text-gray-400 mt-2">Étudiants ayant obtenu au moins 50%.</p>
        </div>

        <div class="rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <p class="text-xs font-black uppercase text-gray-400">Excellence</p>
            <p class="text-4xl font-black text-green-600 mt-2">{{ $excellentRate }}%</p>
            <p class="text-xs text-gray-400 mt-2">Résultats supérieurs ou égaux à 75%.</p>
        </div>

        <div class="rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <p class="text-xs font-black uppercase text-gray-400">À renforcer</p>
            <p class="text-4xl font-black text-red-600 mt-2">{{ $weakRate }}%</p>
            <p class="text-xs text-gray-400 mt-2">Résultats inférieurs à 50%.</p>
        </div>
    </div>

    {{-- NOTES PAR EXAMEN --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-black text-gray-950">Notes des étudiants par examen</h2>
                <p class="text-gray-500 text-sm mt-1">
                    Les corrections répétées du même étudiant apparaissent séparément avec le nom de la correction.
                </p>
            </div>

            <select id="examSelect"
                    class="px-5 py-3 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-bold outline-none focus:border-[#9A0002]">
                @foreach($examPerformance as $exam)
                    <option value="{{ $exam['id'] }}">{{ $exam['title'] }}</option>
                @endforeach
            </select>
        </div>

        <canvas id="studentExamChart" height="115"></canvas>
    </div>

    {{-- GRAPHES PRO --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <div class="mb-6">
                <h2 class="text-2xl font-black text-gray-950">Examens les mieux maîtrisés</h2>
                <p class="text-sm text-gray-500 mt-1">Top examens selon la moyenne globale en pourcentage.</p>
            </div>
            <canvas id="bestExamsChart" height="140"></canvas>
        </div>

        <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
            <div class="mb-6">
                <h2 class="text-2xl font-black text-gray-950">Examens à renforcer</h2>
                <p class="text-sm text-gray-500 mt-1">Examens avec les performances moyennes les plus faibles.</p>
            </div>
            <canvas id="riskExamsChart" height="140"></canvas>
        </div>
    </div>

    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] p-6 shadow-xl">
        <div class="mb-6">
            <h2 class="text-2xl font-black text-gray-950">Étudiants avec plusieurs corrections</h2>
            <p class="text-sm text-gray-500 mt-1">
                Utile pour repérer les étudiants présents dans plusieurs sessions du même examen.
            </p>
        </div>

        <canvas id="attemptsChart" height="110"></canvas>
    </div>

    {{-- TABLEAU --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#EFE6DE]">
            <h2 class="text-2xl font-black text-gray-950">Étude par examen</h2>
            <p class="text-gray-500 mt-1">Résumé pédagogique par examen.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-[#FAF7F4]">
                    <tr class="text-left text-xs font-black uppercase tracking-widest text-gray-500">
                        <th class="px-6 py-4">Examen</th>
                        <th class="px-6 py-4">Moyenne</th>
                        <th class="px-6 py-4">Réussite</th>
                        <th class="px-6 py-4">Excellence</th>
                        <th class="px-6 py-4">Copies corrigées</th>
                        <th class="px-6 py-4">Questions</th>
                        <th class="px-6 py-4">Sessions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#EFE6DE]">
                    @forelse($examPerformance as $item)
                        <tr class="hover:bg-[#FAF7F4] transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="font-black text-gray-950">{{ $item['title'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-black text-[#9A0002]">{{ $item['average'] }}%</td>
                            <td class="px-6 py-4 font-bold text-green-600">{{ $item['success_rate'] }}%</td>
                            <td class="px-6 py-4 font-bold text-violet-600">{{ $item['excellent_rate'] }}%</td>
                            <td class="px-6 py-4">{{ $item['copies'] }}</td>
                            <td class="px-6 py-4">{{ $item['questions'] }}</td>
                            <td class="px-6 py-4">{{ $item['sessions'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                Aucun résultat disponible pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const examDetails = @json($examDetails);
const bestExams = @json($bestExams);
const riskExams = @json($riskExams);
const studentAttempts = @json($studentAttempts);

const modernGrid = {
    color: 'rgba(17,24,39,0.08)',
    drawBorder: false
};

let studentExamChart = null;

function emptyChartText(chart, text = 'Aucune donnée disponible') {
    const { ctx, chartArea } = chart;
    if (!chartArea) return;

    ctx.save();
    ctx.font = 'bold 14px Plus Jakarta Sans, Arial';
    ctx.fillStyle = '#9CA3AF';
    ctx.textAlign = 'center';
    ctx.fillText(text, (chartArea.left + chartArea.right) / 2, (chartArea.top + chartArea.bottom) / 2);
    ctx.restore();
}

const emptyPlugin = {
    id: 'emptyPlugin',
    afterDraw(chart) {
        const datasets = chart.data.datasets || [];
        const hasData = datasets.some(ds => Array.isArray(ds.data) && ds.data.length > 0);
        if (!hasData) {
            emptyChartText(chart);
        }
    }
};

Chart.register(emptyPlugin);

function loadExamChart(examId) {
    const data = examDetails[examId] || {
        labels: [],
        notes: [],
        colors: [],
        mainColor: '#9A0002'
    };

    if (studentExamChart) {
        studentExamChart.destroy();
    }

    studentExamChart = new Chart(document.getElementById('studentExamChart'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Performance (%)',
                data: data.notes,
                backgroundColor: data.colors && data.colors.length ? data.colors : data.mainColor,
                borderRadius: 14,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    padding: 12,
                    cornerRadius: 14,
                    callbacks: {
                        label: function(context) {
                            return 'Performance : ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: modernGrid,
                    ticks: {
                        callback: value => value + '%',
                        font: { weight: 'bold' }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { weight: 'bold' },
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            }
        }
    });
}

const examSelect = document.getElementById('examSelect');

if (examSelect && examSelect.value) {
    loadExamChart(examSelect.value);

    examSelect.addEventListener('change', function () {
        loadExamChart(this.value);
    });
}

new Chart(document.getElementById('bestExamsChart'), {
    type: 'bar',
    data: {
        labels: bestExams.map(e => e.title),
        datasets: [{
            label: 'Moyenne (%)',
            data: bestExams.map(e => e.average),
            backgroundColor: bestExams.map(e => e.color),
            borderRadius: 14,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                cornerRadius: 14,
                callbacks: {
                    label: ctx => 'Moyenne : ' + ctx.parsed.y + '%'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                grid: modernGrid,
                ticks: { callback: value => value + '%' }
            },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('riskExamsChart'), {
    type: 'bar',
    data: {
        labels: riskExams.map(e => e.title),
        datasets: [{
            label: 'Moyenne (%)',
            data: riskExams.map(e => e.average),
            backgroundColor: riskExams.map(e => e.color),
            borderRadius: 14,
            borderSkipped: false
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                cornerRadius: 14,
                callbacks: {
                    label: ctx => 'Moyenne : ' + ctx.parsed.x + '%'
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                max: 100,
                grid: modernGrid,
                ticks: { callback: value => value + '%' }
            },
            y: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('attemptsChart'), {
    type: 'bar',
    data: {
        labels: studentAttempts.map(s => s.label),
        datasets: [{
            label: 'Nombre de corrections',
            data: studentAttempts.map(s => s.attempts),
            backgroundColor: '#9A0002',
            borderRadius: 14,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111827',
                cornerRadius: 14,
                callbacks: {
                    label: ctx => 'Corrections : ' + ctx.parsed.y
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 },
                grid: modernGrid
            },
            x: {
                grid: { display: false },
                ticks: {
                    maxRotation: 35,
                    minRotation: 0
                }
            }
        }
    }
});

if (window.lucide) lucide.createIcons();
</script>
@endsection