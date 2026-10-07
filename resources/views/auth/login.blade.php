<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('AMCortex - Connexion') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-amc-dark { background-color: #EFE6DE; }
        .bg-amc-red { background-color: #9A0002; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    

    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-5xl overflow-hidden flex min-h-[650px] border border-white">
        
        <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col items-center">
            
            <div class="w-full flex items-center gap-3 mb-12">
                <div class="w-10 h-10 rounded-xl bg-amc-red flex items-center justify-center shadow-lg shadow-red-200">
                    <i data-lucide="brain-circuit" class="text-white w-6 h-6"></i>
                </div>
                <span class="text-2xl font-bold tracking-tighter text-[#9A0002]">AMCortex</span>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">{{ __('AMCortex - Portail') }}</h1>
                <p class="text-gray-500 font-medium">{{ __('Espace Enseignants & Administration') }}</p>
            </div>

            <div class="w-full max-w-md flex-1 flex flex-col justify-center">
                @if($errors->any())
                    <div class="mb-4 text-sm text-red-500 bg-red-50 p-3 rounded-xl border border-red-100">
                        {{ $errors->first() }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-4 text-sm text-green-700 bg-green-50 p-3 rounded-xl border border-green-200 font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf
                    
                    <div>
                        <div class="mb-1.5 ml-1">
                            <label class="block text-sm font-semibold text-gray-700">{{ __('Adresse e-mail / Nom d\'utilisateur') }}</label>
                        </div>
                        <input type="text" name="login" placeholder="entrez votre email ou username" required
                            class="w-full px-4 py-4 rounded-2xl 
                            border border-[#EFE6DE] 
                            bg-[#EFE6DE] 
                            focus:bg-[#EFE6DE] 
                            focus:border-[#9A0002] 
                            focus:ring-0 
                            focus:outline-none 
                            transition-all">
                    </div>

                    <div>
                        <div class="mb-1.5 ml-1">
                            <label class="text-sm font-semibold text-gray-700">{{ __('Mot de passe') }}</label>
                        </div>
                        <input type="password" name="password" placeholder="••••••••••••" required
                            class="w-full px-4 py-4 rounded-2xl 
                            border border-[#EFE6DE] 
                            bg-[#EFE6DE] 
                            focus:bg-[#EFE6DE] 
                            focus:border-[#9A0002] 
                            focus:ring-0 
                            focus:outline-none 
                            transition-all">
                    </div>

                    <div class="pt-2 space-y-3">
                        <button type="submit" class="w-full bg-amc-red hover:bg-[#d4313d] text-white font-bold py-4 rounded-2xl shadow-lg shadow-red-100 transition-all active:scale-[0.98]">
                            {{ __('Se connecter') }}
                        </button>
                        <a href="{{ route('register') }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold py-4 rounded-2xl transition-all">
                            {{ __('Créer un compte') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-10 flex items-center gap-2 text-gray-400 hover:text-amc-red transition-colors cursor-pointer w-fit">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span class="text-xs font-semibold uppercase tracking-widest">Support@AMCortex.com</span>
            </div>
        </div>

        <div class="hidden md:flex w-1/2 bg-amc-dark relative overflow-hidden items-end justify-center">
            <div class="absolute top-[-10%] right-[-10%] w-64 h-64 bg-amc-red opacity-10 rounded-full blur-3xl"></div>
            
            <div class="absolute inset-0 w-full h-full">
                <img src="{{ asset('images/illustration-3d.png') }}" alt="Login Illustration" 
                     class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>