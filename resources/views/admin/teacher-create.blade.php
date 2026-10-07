@extends('layouts.admin')

@section('title', 'Ajouter un enseignant')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">
                Administration
            </p>

            <h1 class="text-4xl font-black text-gray-950 mt-2">
                Ajouter un enseignant
            </h1>

            <p class="text-gray-500 mt-2">
                Créer un nouveau compte enseignant pour la plateforme AMCortex.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold">
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

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">

        <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Prénom
                    </label>

                    <input type="text"
                           name="first_name"
                           value="{{ old('first_name') }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">

                    @error('first_name')
                        <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Nom
                    </label>

                    <input type="text"
                           name="last_name"
                           value="{{ old('last_name') }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">

                    @error('last_name')
                        <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div>
                <label class="text-xs font-black uppercase text-gray-500">
                    Username
                </label>

                <input type="text"
                       name="username"
                       value="{{ old('username') }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">

                @error('username')
                    <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-black uppercase text-gray-500">
                    Email
                </label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">

                @error('email')
                    <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Mot de passe
                    </label>

                    <input type="password"
                           name="password"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">

                    @error('password')
                        <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Confirmation
                    </label>

                    <input type="password"
                           name="password_confirmation"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:border-[#9A0002] outline-none">
                </div>

            </div>

            <div class="flex justify-end">
                <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black hover:bg-[#7A0001] transition shadow-xl">
                    Ajouter l'enseignant
                </button>
            </div>

        </form>

    </div>

</div>
@endsection