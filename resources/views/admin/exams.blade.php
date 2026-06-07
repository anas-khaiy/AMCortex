@extends('layouts.admin')

@section('title', 'Examens')

@section('content')
<div class="space-y-8">

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">

        <div class="p-6 border-b border-[#EFE6DE]">
            <h2 class="text-3xl font-black text-gray-950">
                Liste des examens
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-[#FAF7F4]">
                    <tr>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Examen</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Cours</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Questions</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Enseignant</th>
                        <th class="p-5 text-left text-xs font-black uppercase text-gray-500">Date</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($exams as $exam)

                        <tr class="border-t border-[#EFE6DE] hover:bg-[#FAF7F4]">

                            <td class="p-5 font-black">
                                {{ $exam->title }}
                            </td>

                            <td class="p-5">
                                {{ $exam->course_name }}
                            </td>

                            <td class="p-5">
                                {{ $exam->questions_count }}
                            </td>

                            <td class="p-5 text-gray-500">
                                {{ $exam->teacher?->full_name }}
                            </td>

                            <td class="p-5 text-gray-500">
                                {{ $exam->created_at->format('d/m/Y') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-500">
                                Aucun examen trouvé.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="p-6 border-t border-[#EFE6DE]">
            {{ $exams->links('pagination::tailwind') }}
        </div>

    </div>

</div>
@endsection