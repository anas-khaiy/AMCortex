@extends('layouts.admin')

@section('title', 'Étudiants')

@section('content')
<div class="space-y-8">

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">

        <div class="p-6 border-b border-[#EFE6DE] flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-black text-gray-950">
                    Liste des étudiants
                </h2>
                <p class="text-gray-500 text-sm mt-1">Gérez tous les étudiants inscrits sur la plateforme.</p>
            </div>
            
            <form action="{{ route('admin.students') }}" method="GET" class="relative w-full md:w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, prénom ou code..." 
                       class="w-full pl-10 pr-4 py-3 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none text-sm">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
            </form>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-[#FAF7F4]">
                    <tr>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Code</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Prénom</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Nom</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Enseignant</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($students as $student)

                        <tr class="border-t border-[#EFE6DE] hover:bg-[#FAF7F4]">

                            <td class="p-5 font-bold">
                                {{ $student->student_code }}
                            </td>

                            <td class="p-5">
                                {{ $student->first_name }}
                            </td>

                            <td class="p-5">
                                {{ $student->last_name }}
                            </td>

                            <td class="p-5 text-gray-500">
                                {{ $student->teacher?->full_name }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="p-10 text-center text-gray-500">
                                Aucun étudiant trouvé.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="p-6 border-t border-[#EFE6DE]">
            {{ $students->links('pagination::tailwind') }}
        </div>

    </div>

</div>
@endsection