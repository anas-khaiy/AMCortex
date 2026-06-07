@extends('layouts.admin')

@section('title', 'Étudiants')

@section('content')
<div class="space-y-8">

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">

        <div class="p-6 border-b border-[#EFE6DE]">
            <h2 class="text-3xl font-black text-gray-950">
                Liste des étudiants
            </h2>
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