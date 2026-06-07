@extends('layouts.admin')

@section('title', 'Modifier enseignant')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-[2.5rem] border border-[#EFE6DE] shadow-xl p-8">

        <form action="{{ route('admin.teachers.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Prénom</label>

                    <input type="text"
                           name="first_name"
                           value="{{ old('first_name', $user->first_name) }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Nom</label>

                    <input type="text"
                           name="last_name"
                           value="{{ old('last_name', $user->last_name) }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
                </div>

            </div>

            <div>
                <label class="text-xs font-black uppercase text-gray-500">Username</label>

                <input type="text"
                       name="username"
                       value="{{ old('username', $user->username) }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
            </div>

            <div>
                <label class="text-xs font-black uppercase text-gray-500">Email</label>

                <input type="email"
                       name="email"
                       value="{{ old('email', $user->email) }}"
                       class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
            </div>

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Nouveau mot de passe
                    </label>

                    <input type="password"
                           name="password"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">
                        Confirmation
                    </label>

                    <input type="password"
                           name="password_confirmation"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4]">
                </div>

            </div>

            <div class="flex justify-end">
                <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black">
                    Enregistrer les modifications
                </button>
            </div>

        </form>

    </div>

</div>
@endsection