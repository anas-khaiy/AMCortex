<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - AMCortex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
 <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">

    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10 border border-white">

        <!-- Logo -->
        <div class="flex items-center gap-3 mb-10">
            <div class="w-10 h-10 rounded-xl bg-[#9A0002] flex items-center justify-center">
                <i data-lucide="brain-circuit" class="text-white w-6 h-6"></i>
            </div>
            <span class="text-2xl font-bold text-[#9A0002]">AMCortex</span>
        </div>

        <!-- Title -->
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold text-gray-900">Nouveau mot de passe</h1>
            <p class="text-gray-500 text-sm mt-2">
                Choisissez un mot de passe sécurisé
            </p>
        </div>

        <!-- Errors -->
        @if($errors->any())
            <div class="mb-4 text-red-500 bg-red-50 p-3 rounded-xl border border-red-100 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <input type="hidden" name="email" value="{{ old('email', $email) }}">

            <input type="password" name="password" placeholder="Nouveau mot de passe" required
                class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE]">

            <input type="password" name="password_confirmation" placeholder="Confirmer le mot de passe" required
                class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE]">

            <button type="submit"
                class="w-full bg-[#9A0002] hover:bg-red-700 text-white font-bold py-4 rounded-2xl shadow-lg">
                Modifier le mot de passe
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-400">
            🔒 Votre sécurité est notre priorité
        </div>

    </div>
 </div>
<script>lucide.createIcons();</script>
</body>
</html>