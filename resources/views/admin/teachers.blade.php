@extends('layouts.admin')

@section('title', 'Gestion des enseignants')

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

    {{-- HEADER --}}
    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Utilisateurs</p>
            <h1 class="text-4xl font-black text-gray-950 mt-2">Enseignants</h1>
            <p class="text-gray-500 mt-2 font-medium">
                Consultez et gérez les comptes enseignants de la plateforme.
            </p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">

        <div class="p-6 border-b border-[#EFE6DE] flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-950">Liste des enseignants</h2>
                <p class="text-gray-500 text-sm mt-1">Tous les comptes avec le rôle professeur.</p>
            </div>

            <a href="{{ route('admin.teachers.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001]">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
                Ajouter
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#FAF7F4] text-left">
                    <tr>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Enseignant</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Username</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Email</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500">Inscription</th>
                        <th class="p-5 text-xs font-black uppercase text-gray-500 text-right">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($teachers as $teacher)
                        <tr class="border-t border-[#EFE6DE] hover:bg-[#FAF7F4]">
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center font-black">
                                        {{ strtoupper(substr($teacher->first_name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="font-black text-gray-950">{{ $teacher->full_name }}</p>
                                        <p class="text-xs text-gray-400 font-bold">Professeur</p>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5 text-gray-600 font-semibold">
                                {{ $teacher->username }}
                            </td>

                            <td class="p-5 text-gray-600 font-semibold">
                                {{ $teacher->email }}
                            </td>

                            <td class="p-5 text-gray-500 font-semibold">
                                {{ $teacher->created_at->format('d/m/Y') }}
                            </td>

                            <td class="p-5 text-right">
                                <a href="{{ route('admin.teachers.edit', $teacher->id) }}"
                                   class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] text-gray-700 font-black hover:bg-[#9A0002] hover:text-white transition">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    Modifier
                                </a>
                                <form action="{{ route('admin.teachers.delete', $teacher->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Supprimer cet enseignant ? Cette action peut supprimer son accès à la plateforme.')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-red-50 text-red-600 font-black hover:bg-red-600 hover:text-white transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-500 font-bold">
                                Aucun enseignant trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-[#EFE6DE]">
            {{ $teachers->links('pagination::tailwind') }}
        </div>

    </div>

</div>
@endsection