@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Résultats : {{ $exam->title }}</h2>
                <p class="text-gray-500 mt-2">
                    <span class="font-semibold text-blue-600">{{ count($results) }}</span> copies traitées avec succès
                </p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                {{-- Bouton pour retourner au scan --}}
                <a href="{{ route('exams.scan', $exam->id) }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition-all flex items-center">
                    <i data-lucide="scan" class="w-4 h-4 mr-2"></i>
                    Scanner plus
                </a>

                {{-- Bouton de téléchargement CSV --}}
                <a href="{{ route('exams.downloadCsv', $exam->id) }}" class="px-5 py-2 bg-green-50 text-green-700 border border-green-200 rounded-xl font-medium hover:bg-green-100 transition-all flex items-center">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i>
                    Télécharger CSV
                </a>

                {{-- Bouton Print/PDF --}}
                <button onclick="window.print()" class="px-5 py-2 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all flex items-center">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    Imprimer
                </button>
            </div>
        </div>

        {{-- Table des résultats --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 shadow-sm">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase tracking-wider border-b">Code Étudiant</th>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase tracking-wider border-b">Nom & Prénom</th>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase tracking-wider border-b text-center">Note / {{ $exam->total_points }}</th>
                        <th class="p-4 text-sm font-bold text-gray-600 uppercase tracking-wider border-b text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($results as $res)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="p-4 font-mono text-sm text-blue-600 font-bold">
                            {{ $res['code'] }}
                        </td>
                        <td class="p-4 text-gray-900 font-medium">
                            {{-- On affiche le nom et le prénom s'ils sont séparés --}}
                            {{ $res['nom'] }} {{ $res['prenom'] }}
                        </td>
                        <td class="p-4 text-center">
                            <span class="text-lg font-black {{ $res['note'] >= ($exam->total_points / 2) ? 'text-green-600' : 'text-red-600' }}">
                                {{ $res['note'] }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($res['note'] >= ($exam->total_points / 2))
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase">Validé</span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold uppercase">Échec</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i data-lucide="alert-circle" class="h-12 w-12 text-gray-300 mb-4"></i>
                                <p>Aucune note disponible. Vérifiez que les scans ont bien été analysés.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Style pour l'impression (cache les boutons lors de l'impression PDF) --}}
<style>
    @media print {
        .px-5, .bg-gray-100, .bg-blue-600, a, button {
            display: none !important;
        }
        .container {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
        }
        .bg-white {
            box-shadow: none !important;
            border: none !important;
        }
    </tr>
</style>
@endsection