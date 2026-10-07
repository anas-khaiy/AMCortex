@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">
                Administration
            </p>

            <h1 class="text-4xl font-black text-gray-950 mt-2">
                Paramètres du compte
            </h1>

            <p class="text-gray-500 mt-2">
                Gérez les informations de votre compte administrateur.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border border-red-100 p-4">
            <div class="flex items-center gap-2 text-red-600 mb-2 font-bold text-sm">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Veuillez corriger les erreurs suivantes :
            </div>
            <ul class="list-disc list-inside text-sm text-red-500 space-y-1 ml-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Informations du profil --}}
    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#EFE6DE] bg-[#FAF7F4]">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#9A0002] text-white flex items-center justify-center text-2xl font-black">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-950">{{ auth()->user()->full_name }}</h2>
                    <p class="text-xs font-black uppercase tracking-widest text-[#9A0002]">Administrateur</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.settings.profile') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-black uppercase text-gray-500">Prénom</label>
                        <input type="text" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}"
                               class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase text-gray-500">Nom</label>
                        <input type="text" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}"
                               class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                </div>

                <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001] transition">
                    Enregistrer le profil
                </button>
            </form>
        </div>
    </div>

    {{-- Changer le mot de passe --}}
    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl overflow-hidden">
        <div class="p-6 border-b border-[#EFE6DE]">
            <h2 class="text-2xl font-black text-gray-950">Changer le mot de passe</h2>
            <p class="text-gray-500 text-sm mt-1">Modifiez votre mot de passe de connexion.</p>
        </div>

        <div class="p-8">
            <form action="{{ route('admin.settings.password') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Mot de passe actuel</label>
                    <input type="password" name="current_password" placeholder="••••••••"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-xs font-black uppercase text-gray-500">Nouveau mot de passe</label>
                        <input type="password" name="password" placeholder="••••••••"
                               class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase text-gray-500">Confirmer</label>
                        <input type="password" name="password_confirmation" placeholder="••••••••"
                               class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                    </div>
                </div>

                <button class="px-8 py-4 rounded-2xl bg-gray-950 text-white font-black shadow-lg hover:bg-gray-800 transition">
                    Modifier le mot de passe
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
