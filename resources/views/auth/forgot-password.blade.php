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
            <h1 class="text-2xl font-extrabold text-gray-900">Mot de passe oublié</h1>
            <p class="text-gray-500 text-sm mt-2">
                Entrez votre email pour recevoir un lien de réinitialisation
            </p>
        </div>

        <!-- Success -->
        @if(session('success'))
            <div class="mb-4 text-green-600 bg-green-50 p-3 rounded-xl border border-green-100 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <input type="email" name="email" placeholder="prof@universite.ma" required
                class="w-full px-4 py-4 rounded-2xl 
                border border-[#EFE6DE] 
                bg-[#EFE6DE] 
                focus:border-[#9A0002] 
                focus:outline-none">

            @error('email')
                <div class="text-red-500 text-sm">{{ $message }}</div>
            @enderror

            <button type="submit"
                class="w-full bg-[#9A0002] hover:bg-red-700 text-white font-bold py-4 rounded-2xl shadow-lg">
                Envoyer le lien
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-[#9A0002]">
                ← Retour à la connexion
            </a>
        </div>

    </div>
 </div>
<script>lucide.createIcons();</script>
</body>
</html>