@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-8">

    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-red-50 border border-red-100 text-red-700 px-5 py-4 font-bold">
            {{ session('error') }}
        </div>
    @endif

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Supervision globale</p>
                <h1 class="text-4xl font-black text-gray-950 mt-2">Espace Administrateur</h1>
                <p class="text-gray-500 mt-2 font-medium">
                    Suivez l’activité générale de la plateforme AMCortex.
                </p>
            </div>

            <div class="hidden md:flex w-20 h-20 rounded-[2rem] bg-[#9A0002] text-white items-center justify-center shadow-xl">
                <i data-lucide="shield-check" class="w-10 h-10"></i>
            </div>
        </div>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

        @php
            $cards = [
                ['label' => 'Enseignants', 'value' => $teachers, 'icon' => 'users'],
                ['label' => 'Administrateurs', 'value' => $admins, 'icon' => 'shield'],
                ['label' => 'Examens', 'value' => $exams, 'icon' => 'file-text'],
                ['label' => 'Questions', 'value' => $questions, 'icon' => 'help-circle'],
                ['label' => 'Étudiants', 'value' => $students, 'icon' => 'graduation-cap'],
                ['label' => 'Corrections', 'value' => $sessions, 'icon' => 'scan-line'],
            ];
        @endphp

        @foreach($cards as $card)
            <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-lg p-6 hover:-translate-y-1 transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest">{{ $card['label'] }}</p>
                        <h2 class="text-4xl font-black text-gray-950 mt-3">{{ $card['value'] }}</h2>
                    </div>

                    <div class="w-16 h-16 rounded-2xl bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center">
                        <i data-lucide="{{ $card['icon'] }}" class="w-8 h-8"></i>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- LATEST TEACHERS --}}
    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#EFE6DE] flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-950">Derniers enseignants</h2>
                <p class="text-gray-500 text-sm font-medium mt-1">Comptes professeurs récemment créés.</p>
            </div>

            <a href="{{ route('admin.teachers') }}"
               class="px-5 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001]">
                Voir tout
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#FAF7F4] text-left">
                    <tr>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Nom</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Username</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Email</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($latestTeachers as $teacher)
                        <tr class="border-t border-[#EFE6DE] hover:bg-[#FAF7F4]">
                            <td class="p-5 font-black">{{ $teacher->full_name }}</td>
                            <td class="p-5 text-gray-600">{{ $teacher->username }}</td>
                            <td class="p-5 text-gray-600">{{ $teacher->email }}</td>
                            <td class="p-5 text-gray-500">{{ $teacher->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-500 font-bold">
                                Aucun enseignant trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection