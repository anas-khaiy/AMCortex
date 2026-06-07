@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="relative overflow-hidden rounded-[2.5rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative flex items-center gap-5">
            <div class="w-20 h-20 rounded-[2rem] bg-[#9A0002] text-white flex items-center justify-center text-3xl font-black shadow-lg">
                {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
            </div>

            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Mon compte</p>
                <h1 class="text-4xl font-black text-gray-950 mt-1">Profil</h1>
                <p class="text-gray-500 font-medium mt-1">Modifier vos informations personnelles.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="text-xs font-black uppercase text-gray-500">Nom complet</label>
                <input type="text" name="name" value="{{ old('name', auth()->user()->full_name) }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none transition">
                @error('name') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-black uppercase text-gray-500">Email</label>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none transition">
                @error('email') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
            </div>

            <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] hover:-translate-y-1 transition">
                Enregistrer
            </button>
        </form>
    </div>
</div>
@endsection