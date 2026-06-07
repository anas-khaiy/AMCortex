<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMCortex - Créer un compte</title>
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
        
        <div class="w-full md:w-1/2 p-10 md:p-14 flex flex-col">
            <div class="flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-amc-red flex items-center justify-center shadow-lg shadow-red-200">
                    <i data-lucide="brain-circuit" class="text-white w-6 h-6"></i>
                </div>
                <span class="text-2xl font-bold tracking-tighter text-[#1D3557]">AMCortex</span>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Create an account</h1>
                <p class="text-gray-500 font-medium">Rejoignez la plateforme de correction intelligente.</p>
            </div>
            
            <div class="flex-1 flex flex-col justify-center">
                <form action="{{ route('register') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">
                                Prénom
                            </label>

                            <input type="text"
                                   name="first_name"
                                   required
                                   class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE] focus:border-[#9A0002] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">
                                Nom
                            </label>

                            <input type="text"
                                   name="last_name"
                                   required
                                   class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE] focus:border-[#9A0002] focus:outline-none">
                        </div>

                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">
                            Nom d'utilisateur
                        </label>

                        <input type="text"
                               name="username"
                               required
                               class="w-full px-4 py-4 rounded-2xl border border-[#EFE6DE] bg-[#EFE6DE] focus:border-[#9A0002] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Email</label>
                        <input type="email" name="email" placeholder="prof@universite.ma" required
                            class="w-full px-4 py-4 rounded-2xl 
                            border border-[#EFE6DE] 
                            bg-[#EFE6DE] 
                            focus:bg-[#EFE6DE] 
                            focus:border-[#9A0002] 
                            focus:ring-0 
                            focus:outline-none 
                            transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Password</label>
                            <input type="password" name="password" placeholder="••••••••" required
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
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 ml-1">Confirm</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••" required
                                class="w-full px-4 py-4 rounded-2xl 
                                border border-[#EFE6DE] 
                                bg-[#EFE6DE] 
                                focus:bg-[#EFE6DE] 
                                focus:border-[#9A0002] 
                                focus:ring-0 
                                focus:outline-none 
                                transition-all">
                        </div>
                    </div>

                    <div class="pt-4 space-y-3">
                        <button type="submit" class="w-full bg-amc-red hover:bg-[#d4313d] text-white font-bold py-4 rounded-2xl shadow-lg shadow-red-100 transition-all active:scale-[0.98]">
                            Create Account
                        </button>
                        <a href="{{ route('login') }}" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold py-4 rounded-2xl transition-all">
                            Sign in
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-10 flex items-center gap-2 text-gray-400 hover:text-amc-red transition-colors cursor-pointer w-fit">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
                <span class="text-xs font-semibold uppercase tracking-widest">Support@AMCortex.com</span>
            </div>
        </div>

        <div class="hidden md:flex w-1/2 bg-[#F8F9FB] relative overflow-hidden items-end justify-center">
            
            <div class="absolute inset-0 bg-gradient-to-t from-gray-100 to-transparent opacity-50"></div>

            <div class="absolute inset-0 w-full h-full">
                @if(file_exists(public_path('images/illustration-3d.png')))
                    <img src="{{ asset('images/illustration-3d.png') }}" alt="Correction 3D" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-amc-dark flex items-center justify-center">
                         <i data-lucide="image" class="text-white/20 w-20 h-20"></i>
                    </div>
                @endif
            </div>

            
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>