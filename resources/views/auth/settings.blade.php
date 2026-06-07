@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="space-y-8">

    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border border-green-100 text-green-700 px-5 py-4 font-bold">
            {{ session('success') }}
        </div>
    @endif

    <div class="relative overflow-hidden rounded-[3rem] bg-white border border-[#EFE6DE] shadow-xl p-8">
        <div class="absolute -right-24 -top-24 w-80 h-80 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Compte</p>
            <h1 class="text-4xl font-black text-gray-950 mt-2">Paramètres</h1>
            <p class="text-gray-500 mt-2 font-medium">Gérez votre profil, vos notifications et l’aide AMCortex.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

        {{-- LEFT MENU --}}
        <aside class="xl:col-span-3">
            <div class="sticky top-8 bg-white border border-[#EFE6DE] rounded-[2.5rem] shadow-xl p-4 space-y-2">
                <button onclick="showTab('profile')" id="tab-profile"
                        class="settings-tab active-tab w-full flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-left">
                    <i data-lucide="user" class="w-5 h-5"></i>
                    Profil
                </button>

                <button onclick="showTab('notifications')" id="tab-notifications"
                        class="settings-tab w-full flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-left">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    Notifications
                </button>

                <button onclick="showTab('help')" id="tab-help"
                        class="settings-tab w-full flex items-center gap-3 px-5 py-4 rounded-2xl font-black text-left">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                    Aide / Documentation
                </button>
            </div>
        </aside>

        {{-- CONTENT --}}
        <section class="xl:col-span-9">

            {{-- PROFILE --}}
<div id="panel-profile" class="settings-panel bg-white border border-[#EFE6DE] rounded-[2.5rem] shadow-xl overflow-hidden">

    {{-- Profile hero with character --}}
    <div class="relative overflow-hidden bg-[#FAF7F4] p-8 border-b border-[#EFE6DE]">
        <div class="absolute -right-16 -top-16 w-72 h-72 bg-[#9A0002]/10 rounded-full blur-3xl"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
            <div class="lg:col-span-2 flex items-center gap-5">
                <div class="w-24 h-24 rounded-[2rem] bg-[#9A0002] text-white flex items-center justify-center text-4xl font-black shadow-lg">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                </div>

                <div>
                    <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Profil enseignant</p>
                    <h2 class="text-3xl font-black text-gray-950 mt-1">
                        Bonjour, {{ auth()->user()->full_name }}
                    </h2>
                    <p class="text-gray-500 font-medium mt-1">Modifier les informations du compte.</p>
                </div>
            </div>

            {{-- Character illustration --}}
            <div class="relative hidden lg:flex justify-end">
                <div class="relative w-56 h-44 rounded-[2rem] bg-white border border-[#EFE6DE] shadow-lg overflow-hidden">
                    <div class="absolute bottom-0 left-0 right-0 h-16 bg-[#9A0002]/10"></div>

                    {{-- head --}}
                    <div class="absolute top-8 left-20 w-16 h-16 rounded-full bg-[#F2B8A0] shadow-md"></div>

                    {{-- hair --}}
                    <div class="absolute top-5 left-[68px] w-20 h-12 rounded-t-full bg-gray-950"></div>
                    <div class="absolute top-10 left-16 w-7 h-7 rounded-full bg-gray-950"></div>

                    {{-- body --}}
                    <div class="absolute top-24 left-16 w-24 h-28 rounded-t-[2rem] bg-[#9A0002]"></div>

                    {{-- laptop/book --}}
                    <div class="absolute bottom-6 right-8 w-20 h-12 rounded-xl bg-[#FAF7F4] border border-[#EFE6DE] shadow-sm"></div>
                    <div class="absolute bottom-12 right-14 w-8 h-2 rounded-full bg-[#9A0002]/30"></div>

                    {{-- decorative dots --}}
                    <div class="absolute top-8 right-8 w-3 h-3 bg-[#9A0002] rounded-full"></div>
                    <div class="absolute top-16 right-14 w-2 h-2 bg-gray-300 rounded-full"></div>
                    <div class="absolute bottom-10 left-8 w-3 h-3 bg-[#9A0002]/30 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8">
        <form action="{{ route('settings.profile') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->full_name) }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none">
                    @error('name') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                           class="w-full mt-2 px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none">
                    @error('email') <p class="text-red-500 text-xs font-bold mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <button class="px-8 py-4 rounded-2xl bg-[#9A0002] text-white font-black shadow-lg hover:bg-[#7A0001]">
                Enregistrer le profil
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-[#EFE6DE]">
            <h3 class="text-2xl font-black text-gray-950 mb-5">Changer le mot de passe</h3>

            <form action="{{ route('settings.password') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <input type="password" name="current_password" placeholder="Mot de passe actuel"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none">
                @error('current_password') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror

                <input type="password" name="password" placeholder="Nouveau mot de passe"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none">
                @error('password') <p class="text-red-500 text-xs font-bold">{{ $message }}</p> @enderror

                <input type="password" name="password_confirmation" placeholder="Confirmer le nouveau mot de passe"
                       class="w-full px-5 py-4 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] focus:bg-white focus:border-[#9A0002] outline-none">

                <button class="px-8 py-4 rounded-2xl bg-gray-950 text-white font-black">
                    Modifier le mot de passe
                </button>
            </form>
        </div>
    </div>
</div>

            {{-- NOTIFICATIONS --}}
            <div id="panel-notifications" class="settings-panel hidden bg-white border border-[#EFE6DE] rounded-[2.5rem] shadow-xl p-8">
                <h2 class="text-3xl font-black text-gray-950">Notifications</h2>
                <p class="text-gray-500 font-medium mt-2">Choisissez les alertes que vous voulez recevoir.</p>

                <div class="mt-8 space-y-4">
                    @foreach([
                        ['key' => 'amc_ready', 'title' => 'Résultats AMC prêts', 'desc' => 'Recevoir une alerte quand la correction est terminée.'],
                        ['key' => 'scan_error', 'title' => 'Erreur de scan', 'desc' => 'Recevoir une alerte si une copie ne peut pas être analysée.'],
                        ['key' => 'import_done', 'title' => 'Import terminé', 'desc' => 'Recevoir une confirmation après import CSV ou JSON.'],
                    ] as $item)
                        <div class="flex items-center justify-between rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] p-5">
                            <div>
                                <p class="font-black text-gray-950">{{ $item['title'] }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ $item['desc'] }}</p>
                            </div>

                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" checked class="sr-only peer notification-toggle" data-key="{{ $item['key'] }}">
                                <div class="w-14 h-8 bg-gray-200 rounded-full peer peer-checked:bg-[#9A0002] after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:w-6 after:h-6 after:rounded-full after:transition-all peer-checked:after:translate-x-6"></div>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- HELP --}}
<div id="panel-help" class="settings-panel hidden bg-white border border-[#EFE6DE] rounded-[2.5rem] shadow-xl p-8">

    <div class="flex items-start justify-between gap-6 flex-wrap">
        <div>
            <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Documentation</p>
            <h2 class="text-4xl font-black text-gray-950 mt-2">Guide complet AMCortex</h2>
            <p class="text-gray-500 font-medium mt-3 max-w-3xl">
                Ce guide explique tout le parcours : création d’examen, importation, questions, génération PDF, scan, correction et consultation des résultats.
            </p>
        </div>

        <div class="w-20 h-20 rounded-[2rem] bg-[#9A0002] text-white flex items-center justify-center shadow-xl">
            <i data-lucide="book-open-check" class="w-10 h-10"></i>
        </div>
    </div>

    <div class="mt-10 space-y-6">

        {{-- 1 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">1. Créer un examen</h3>
            <p class="text-gray-600 mt-2">L’enseignant commence par créer un nouvel examen avec ses informations principales.</p>

            <div class="mt-5 grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Titre de l’examen</p>
                    <p class="text-sm text-gray-600 mt-1">Nom affiché sur la feuille générée.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Matière / Cours</p>
                    <p class="text-sm text-gray-600 mt-1">Nom du module concerné.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Durée</p>
                    <p class="text-sm text-gray-600 mt-1">Durée de l’examen en minutes.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Langue</p>
                    <p class="text-sm text-gray-600 mt-1">Français, Anglais ou Arabe.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Date</p>
                    <p class="text-sm text-gray-600 mt-1">Date prévue de l’examen.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Consignes</p>
                    <p class="text-sm text-gray-600 mt-1">Instructions visibles sur la copie.</p>
                </div>
            </div>
        </div>

        {{-- 2 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">2. Paramètres de l’examen</h3>
            <p class="text-gray-600 mt-2">Ces paramètres influencent la génération du PDF AMC.</p>

            <ul class="mt-5 space-y-3 text-sm text-gray-700 font-bold bg-white rounded-2xl border border-[#EFE6DE] p-5">
                <li>• Nombre de copies : nombre de feuilles à générer.</li>
                <li>• Format de page : A4 ou autre format disponible.</li>
                <li>• Longueur du code étudiant : nombre de chiffres à cocher.</li>
                <li>• Mélanger les questions : ordre différent selon les copies.</li>
                <li>• Mélanger les réponses : ordre des choix modifié automatiquement.</li>
                <li>• Mode anonyme : l’étudiant remplit son code.</li>
                <li>• Mode nominatif : les copies sont liées à une liste d’étudiants.</li>
            </ul>
        </div>

        {{-- 3 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">3. Ajouter les questions</h3>
            <p class="text-gray-600 mt-2">Chaque question doit contenir un texte, un type, des réponses et un barème.</p>

            <div class="mt-5 grid md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Choix unique</p>
                    <p class="text-sm text-gray-600 mt-1">Une seule réponse correcte.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Choix multiple</p>
                    <p class="text-sm text-gray-600 mt-1">Plusieurs réponses correctes.</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Vrai / Faux</p>
                    <p class="text-sm text-gray-600 mt-1">Question rapide avec deux réponses.</p>
                </div>
            </div>

            <div class="mt-5 bg-white rounded-2xl border border-[#EFE6DE] p-5">
                <p class="font-black text-gray-950">À vérifier avant génération :</p>
                <ul class="mt-3 space-y-2 text-sm text-gray-600 font-medium">
                    <li>• Chaque question doit avoir au moins une bonne réponse.</li>
                    <li>• Les points doivent être corrects.</li>
                    <li>• Les réponses ne doivent pas être vides.</li>
                    <li>• Pour l’arabe, éviter de mélanger beaucoup de texte français dans la même question.</li>
                </ul>
            </div>
        </div>

        {{-- 4 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">4. Importer un examen</h3>
            <p class="text-gray-600 mt-2">AMCortex permet aussi d’importer un examen complet avec un fichier CSV ou JSON.</p>

            <div class="mt-5 grid md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-[#EFE6DE]">
                    <p class="font-black text-gray-950">CSV</p>
                    <p class="text-sm text-gray-600 mt-2">
                        Utilisez un séparateur point-virgule <strong>;</strong>. Chaque ligne représente une question ou une réponse selon le format choisi.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-[#EFE6DE]">
                    <p class="font-black text-gray-950">JSON</p>
                    <p class="text-sm text-gray-600 mt-2">
                        Le JSON permet d’importer les questions, réponses, types et bonnes réponses d’une manière structurée.
                    </p>
                </div>
            </div>

            <div class="mt-5 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-2xl p-5 font-bold text-sm">
                Après importation, vérifiez toujours les questions avant de générer le PDF.
            </div>
        </div>

        {{-- 5 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">5. Générer le PDF AMC</h3>
            <p class="text-gray-600 mt-2">Après validation des questions, l’enseignant génère le sujet PDF.</p>

            <ul class="mt-5 space-y-3 text-sm text-gray-700 font-bold bg-white rounded-2xl border border-[#EFE6DE] p-5">
                <li>• Le PDF contient les questions et les cases à cocher.</li>
                <li>• Les marques noires AMC ne doivent jamais être supprimées.</li>
                <li>• Le PDF généré doit être imprimé proprement.</li>
                <li>• Après correction des copies, il ne faut plus modifier l’examen.</li>
            </ul>
        </div>

        {{-- 6 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">6. Parcours du scan</h3>
            <p class="text-gray-600 mt-2">La correction automatique suit plusieurs étapes dans l’ordre.</p>

            <div class="mt-5 space-y-4">
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">1. Importer les scans</p>
                    <p class="text-sm text-gray-600 mt-1">Ajouter les copies scannées en PDF, JPG ou PNG.</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">2. Lancer l’analyse</p>
                    <p class="text-sm text-gray-600 mt-1">AMC lit les cases cochées et détecte les copies.</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">3. Associer les copies</p>
                    <p class="text-sm text-gray-600 mt-1">Association automatique ou manuelle selon le mode choisi.</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">4. Calculer les notes</p>
                    <p class="text-sm text-gray-600 mt-1">AMC applique le barème et AMCortex crée l’historique.</p>
                </div>
            </div>
        </div>

        {{-- 7 --}}
        <div class="rounded-[2rem] bg-[#FAF7F4] border border-[#EFE6DE] p-6">
            <h3 class="text-2xl font-black text-[#9A0002]">7. Résultats et historique</h3>
            <p class="text-gray-600 mt-2">Après correction, chaque session est sauvegardée séparément.</p>

            <div class="mt-5 grid md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Voir les notes</p>
                    <p class="text-sm text-gray-600 mt-1">Afficher les notes des étudiants.</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Télécharger CSV</p>
                    <p class="text-sm text-gray-600 mt-1">Exporter les résultats.</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-[#EFE6DE]">
                    <p class="font-black">Copies corrigées</p>
                    <p class="text-sm text-gray-600 mt-1">Télécharger les copies annotées par AMC.</p>
                </div>
            </div>
        </div>
        {{-- DETAILS AVANCES --}}
        <div class="rounded-[2rem] bg-white border border-[#EFE6DE] p-6 shadow-sm">
            <h3 class="text-2xl font-black text-[#9A0002]">Détails importants du fonctionnement</h3>

            <div class="mt-6 space-y-5 text-sm text-gray-700 font-medium">

                <div>
                    <p class="font-black text-gray-950">Création de l’examen</p>
                    <p class="mt-1">
                        L’examen appartient uniquement à l’enseignant connecté. Chaque enseignant voit seulement ses propres examens,
                        questions, étudiants, scans et résultats.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Import CSV / JSON</p>
                    <p class="mt-1">
                        L’import permet de créer rapidement un examen complet. Le fichier doit respecter le format demandé.
                        Après importation, il faut toujours vérifier les questions, les réponses et les bonnes réponses avant de générer le PDF.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Questions et réponses</p>
                    <p class="mt-1">
                        Une question peut être à choix unique, choix multiple ou vrai/faux. Les réponses correctes sont utilisées par AMC
                        pour calculer automatiquement la note. Si une question n’a pas de bonne réponse, la correction peut être fausse.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Génération PDF</p>
                    <p class="mt-1">
                        Le PDF généré contient les repères AMC, les cases à cocher, le code étudiant ou les informations nominatives.
                        Il ne faut pas modifier manuellement ce PDF, car AMC utilise sa structure pour reconnaître les copies scannées.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Analyse des scans</p>
                    <p class="mt-1">
                        Pendant l’analyse, AMC lit les images des copies et détecte les cases cochées. Les scans doivent être clairs,
                        droits et correspondre au PDF généré pour cet examen.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Association des copies</p>
                    <p class="mt-1">
                        En mode nominatif, AMC peut associer automatiquement les copies aux étudiants. En mode anonyme ou si la reconnaissance
                        échoue, l’enseignant peut faire une association manuelle.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Correction et historique</p>
                    <p class="mt-1">
                        Quand les notes sont calculées, AMCortex sauvegarde une session de correction. Chaque session conserve son CSV,
                        ses lignes de résultats et ses copies corrigées si elles sont générées.
                    </p>
                </div>

                <div>
                    <p class="font-black text-gray-950">Verrouillage de l’examen</p>
                    <p class="mt-1">
                        Après correction, il est recommandé de ne plus modifier l’examen ni ses questions, car les résultats correspondent
                        à la version exacte du PDF déjà scanné.
                    </p>
                </div>

            </div>
        </div>

        {{-- 8 --}}
        <div class="rounded-[2.5rem] bg-[#111827] text-white p-8 relative overflow-hidden">
            <div class="absolute -right-24 -top-24 w-72 h-72 bg-[#9A0002]/30 rounded-full blur-3xl"></div>

            <div class="relative">
                <h3 class="text-2xl font-black">Conseils importants</h3>

                <div class="mt-5 grid md:grid-cols-2 gap-4">
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                        <p class="font-black text-[#FFB4B5]">Qualité du scan</p>
                        <p class="text-sm text-white/70 mt-1">Scanner droit, clair, sans ombre et avec bonne résolution.</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                        <p class="font-black text-[#FFB4B5]">Ne pas modifier</p>
                        <p class="text-sm text-white/70 mt-1">Ne modifiez pas l’examen après la correction.</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                        <p class="font-black text-[#FFB4B5]">Réponses correctes</p>
                        <p class="text-sm text-white/70 mt-1">Chaque question doit avoir une bonne réponse.</p>
                    </div>

                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                        <p class="font-black text-[#FFB4B5]">Historique</p>
                        <p class="text-sm text-white/70 mt-1">Chaque correction reste consultable séparément.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- DOWNLOAD DOC --}}
<div class="mt-10 rounded-[2.5rem] bg-[#9A0002] text-white p-8 relative overflow-hidden">

    <div class="absolute -right-20 -top-20 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>

    <div class="relative flex flex-col lg:flex-row items-center justify-between gap-6">

        <div>
            <p class="text-sm font-black uppercase tracking-widest text-white/60">
                Documentation AMCortex
            </p>

            <h3 class="text-3xl font-black mt-2">
                Télécharger le guide complet
            </h3>

            <p class="text-white/80 mt-3 max-w-2xl">
                Téléchargez la documentation complète AMCortex contenant
                toutes les explications sur les examens, questions,
                génération PDF, scan, correction et résultats.
            </p>
        </div>

        <div class="flex flex-wrap gap-4">

            <a href="{{ route('documentation.pdf') }}"
              class="px-7 py-4 rounded-2xl bg-white text-[#9A0002] font-black shadow-xl hover:-translate-y-1 transition-all flex items-center gap-3">
                <i data-lucide="download" class="w-5 h-5"></i>
                Télécharger PDF
            </a>


            <a href="{{ route('dashboard') }}"
               class="px-7 py-4 rounded-2xl bg-black/20 border border-white/20 text-white font-black hover:bg-black/30 transition-all flex items-center gap-3">

                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                Retour Dashboard
            </a>

        </div>
    </div>
</div>
    </div>
</div>


        </section>
    </div>
</div>

<style>
    .settings-tab {
        color: #4b5563;
        background: transparent;
    }
    .settings-tab.active-tab {
        color: white;
        background: #9A0002;
        box-shadow: 0 14px 30px rgba(154,0,2,0.18);
    }

@media print {
    body * {
        visibility: hidden;
    }

    #panel-help, #panel-help * {
        visibility: visible;
    }

    #panel-help {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
    }

    button, a[href="{{ route('dashboard') }}"] {
        display: none !important;
    }
}
</style>

<script>
    function showTab(tab) {
        document.querySelectorAll('.settings-panel').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.settings-tab').forEach(el => el.classList.remove('active-tab'));

        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('active-tab');

        if (window.lucide) lucide.createIcons();
    }

    if (window.lucide) lucide.createIcons();
</script>
<script>
document.querySelectorAll('.notification-toggle').forEach(toggle => {
    const key = 'notif_' + toggle.dataset.key;

    toggle.checked = localStorage.getItem(key) !== 'false';

    toggle.addEventListener('change', function () {
        localStorage.setItem(key, this.checked ? 'true' : 'false');
    });
});
</script>
@endsection