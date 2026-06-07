@extends('layouts.app')

@section('title', 'Gestion des Étudiants')

@section('content')
<div class="space-y-8">

@if(session('success'))
    <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold shadow-sm">
        {{ session('success') }}
    </div>
@endif

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center justify-between flex-wrap gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Base étudiants</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">Gestion des étudiants</h1>
                <p class="text-gray-500 font-medium mt-2">Gérez votre base de données étudiantes</p>
            </div>

            <div class="flex gap-3">
                <button onclick="document.getElementById('csv_file').click()"
                    class="flex items-center gap-2 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] px-5 py-3 text-gray-700 hover:text-[#9A0002] hover:bg-white transition-all shadow-sm font-black">
                    <i data-lucide="upload" class="h-4 w-4"></i>
                    Import CSV
                </button>

                <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" id="csv_file" name="file" onchange="this.form.submit()">
                </form>

                <a href="{{ route('students.create') }}"
                   class="flex items-center gap-2 rounded-2xl bg-[#9A0002] px-5 py-3 text-white hover:bg-[#7A0001] hover:-translate-y-1 transition-all shadow-lg font-black">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Add Student
                </a>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="rounded-[2rem] bg-white p-5 shadow-lg border border-[#EFE6DE]">
        <form action="{{ route('students.index') }}" method="GET" class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by name or code"
                   class="w-full rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] pl-12 pr-4 py-4 font-semibold focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 focus:outline-none transition-all">
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-[2rem] bg-white p-6 shadow-lg border border-[#EFE6DE] hover:-translate-y-1 hover:shadow-xl transition-all">
            <div class="flex items-center gap-4">
                <div class="rounded-2xl p-4 bg-[#9A0002]/10 text-[#9A0002]">
                    <i data-lucide="users" class="h-7 w-7"></i>
                </div>
                <div>
                    <p class="text-4xl font-black text-gray-950">{{ $students->total() }}</p>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-wider">Total étudiants</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] bg-white p-6 shadow-lg border border-[#EFE6DE] hover:-translate-y-1 hover:shadow-xl transition-all">
            <div class="flex items-center gap-4">
                <div class="rounded-2xl p-4 bg-[#FAF7F4] text-[#9A0002] border border-[#EFE6DE]">
                    <i data-lucide="zap" class="h-7 w-7"></i>
                </div>
                <div>
                    <p class="text-4xl font-black text-gray-950">{{ $newCount ?? 0 }}</p>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-wider">Nouveaux cette semaine</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="rounded-[2.5rem] bg-white shadow-xl border border-[#EFE6DE] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#FAF7F4] border-b border-[#EFE6DE]">
                    <tr>
                        <th class="px-6 py-5 text-xs font-black text-gray-500 uppercase tracking-widest">Code d' étudiant</th>
                        <th class="px-6 py-5 text-xs font-black text-gray-500 uppercase tracking-widest">Nom Complet</th>
                        <th class="px-6 py-5 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#EFE6DE]">
                    @forelse($students as $student)
                    <tr class="hover:bg-[#FAF7F4] transition-colors group">
                        <td class="px-6 py-4">
                            <span class="font-mono font-black text-[#9A0002] bg-[#9A0002]/10 px-3 py-1 rounded-xl">
                                {{ $student->student_code }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center font-black shadow-sm">
                                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                </div>
                                <div class="font-black text-gray-950">
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('students.edit', $student->id) }}"
                                   class="rounded-xl p-2.5 text-gray-500 bg-[#FAF7F4] border border-[#EFE6DE] hover:bg-[#9A0002] hover:text-white transition-all">
                                    <i data-lucide="edit-3" class="h-4 w-4"></i>
                                </a>

                                <form action="{{ route('students.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Delete student?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-xl p-2.5 text-red-500 bg-red-50 border border-red-100 hover:bg-red-500 hover:text-white transition-all">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="rounded-full bg-[#FAF7F4] p-6 mb-4 border border-[#EFE6DE]">
                                    <i data-lucide="users-2" class="h-12 w-12 text-gray-300"></i>
                                </div>
                                <p class="text-gray-500 font-black text-lg">No students found.</p>
                                <p class="text-gray-400 text-sm">Try adjusting your search or add a new student.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="px-6 py-4 bg-[#FAF7F4] border-t border-[#EFE6DE]">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
@endsection