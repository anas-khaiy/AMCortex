<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Email oublié - AMCortex</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-10">

    <h1 class="text-3xl font-black text-[#9A0002]">
        Adresse email oubliée
    </h1>

    <p class="text-gray-500 mt-3 mb-6">
        Entrez votre nom d’utilisateur. AMCortex enverra votre adresse email et un lien de réinitialisation à l’adresse associée à votre compte.
    </p>

    @if(session('success'))
        <div class="mb-4 text-green-700 bg-green-50 p-4 rounded-2xl border border-green-100 text-sm font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 text-red-600 bg-red-50 p-4 rounded-2xl border border-red-100 text-sm font-bold">
            {{ $errors->first() }}
        </div>
    @endif

    

    <form method="POST" action="{{ route('forgot.email.send') }}" class="space-y-5">
        @csrf

        <input type="text"
               name="username"
               placeholder="Nom d'utilisateur"
               required
               class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE] focus:outline-none focus:border-[#9A0002]">

        <button type="submit"
                class="w-full bg-[#9A0002] hover:bg-red-700 text-white font-bold py-4 rounded-2xl shadow-lg">
            Envoyer à mon email
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-[#9A0002]">
            ← Retour à la connexion
        </a>
    </div>

</div>

</body>
</html>