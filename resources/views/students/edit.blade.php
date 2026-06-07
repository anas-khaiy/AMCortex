@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Modification</p>
        <h1 class="text-4xl font-black text-gray-950 mt-1">Modifier étudiant</h1>
    </div>

    {{-- FORM --}}
    <div class="bg-white p-8 rounded-[2.5rem] border border-[#EFE6DE] shadow-xl">

        <form action="{{ route('students.update', $student->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- FIRST NAME --}}
            <div>
                <label class="text-xs font-black uppercase text-gray-500">Prénom</label>
                <input type="text"
                       name="first_name"
                       value="{{ old('first_name', $student->first_name) }}"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] transition">
            </div>

            {{-- LAST NAME --}}
            <div>
                <label class="text-xs font-black uppercase text-gray-500">Nom</label>
                <input type="text"
                       name="last_name"
                       value="{{ old('last_name', $student->last_name) }}"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] transition">
            </div>

            {{-- CODE --}}
            <div>
                <label class="text-xs font-black uppercase text-gray-500">Code étudiant</label>
                <input type="text"
                       name="student_code"
                       value="{{ old('student_code', $student->student_code) }}"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-mono tracking-[0.2em] focus:bg-white focus:border-[#9A0002] transition">
                       @error('student_code')
                           <p class="text-red-500 text-xs font-bold mt-2">{{ $message }}</p>
                       @enderror
            </div>

            {{-- ACTIONS --}}
            <div class="flex gap-4 pt-6 border-t border-[#EFE6DE]">

                <a href="{{ route('students.index') }}"
                   class="flex-1 text-center py-4 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] font-bold hover:bg-white">
                    Annuler
                </a>

                <button class="flex-1 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition">
                    Sauvegarder
                </button>

            </div>

        </form>

    </div>
</div>
@endsection