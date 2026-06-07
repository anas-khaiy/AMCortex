@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    {{-- HEADER --}}
    <div class="rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Étudiant</p>
        <h1 class="text-4xl font-black text-gray-950 mt-1">Ajouter un étudiant</h1>
        <p class="text-gray-500 mt-2 font-medium">
            Le code étudiant est utilisé pour la correction automatique AMC
        </p>
    </div>

    {{-- FORM --}}
    <div class="bg-white p-8 rounded-[2.5rem] border border-[#EFE6DE] shadow-xl space-y-6">

        <form action="{{ route('students.store') }}" method="POST" class="space-y-8">
            @csrf

            <input type="hidden" name="id_length" value="{{ $idLength }}">

            {{-- CODE --}}
            <div class="space-y-2">
                <label class="text-xs font-black uppercase text-[#9A0002] tracking-widest">
                    Code étudiant
                </label>

                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <i data-lucide="hash" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <input type="text"
                               name="student_code"
                               id="student_code"
                               required
                               value="{{ old('student_code') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full pl-12 pr-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] font-mono font-black tracking-[0.3em] text-lg focus:bg-white focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10 transition">
                               @error('student_code')
                                   <p class="text-red-500 text-xs font-bold mt-2">{{ $message }}</p>
                               @enderror
                    </div>

                    <button type="button"
                            onclick="generateCode()"
                            class="px-5 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] hover:bg-[#9A0002] hover:text-white transition">
                        <i data-lucide="refresh-cw"></i>
                    </button>
                </div>
            </div>

            {{-- NAMES --}}
            <div class="grid md:grid-cols-2 gap-6">

                <div>
                    <label class="text-xs font-black uppercase text-[#9A0002] tracking-widest">Prénom</label>
                    <input type="text"
                           name="first_name"
                           value="{{ old('first_name') }}"
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] transition">
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-[#9A0002] tracking-widest">Nom</label>
                    <input type="text"
                           name="last_name"
                           value="{{ old('last_name') }}"
                           class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] transition">
                </div>

            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-between items-center pt-6 border-t border-[#EFE6DE]">

                <a href="{{ route('students.index') }}"
                   class="font-bold text-gray-500 hover:text-[#9A0002] transition">
                    Annuler
                </a>

                <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition">
                    Enregistrer
                </button>

            </div>

        </form>

    </div>
</div>

<script>
function generateCode() {
    const length = {{ $idLength }};
    let code = '';
    for (let i = 0; i < length; i++) {
        code += Math.floor(Math.random() * 10);
    }
    document.getElementById('student_code').value = code;
}
</script>
@endsection