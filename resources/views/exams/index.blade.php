@extends('layouts.app')

@section('title', 'Gestion des Examens')

@section('content')
<div class="space-y-8">

    {{-- HERO --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-xl border border-[#EFE6DE]">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#9A0002]/10 blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-sm font-black uppercase tracking-widest text-[#9A0002]">Espace enseignant</p>
                <h1 class="mt-2 text-4xl font-black text-gray-950">Gestion des examens</h1>
                <p class="mt-2 text-gray-500 font-medium">Créez, organisez et générez vos examens.</p>
            </div>

            <div class="flex gap-3 flex-wr">

                {{-- CREATE --}}
                <a href="{{ route('exams.create') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-[#9A0002] px-6 py-4 text-white font-black shadow-lg hover:-translate-y-1 hover:shadow-xl transition-all">
                    <i data-lucide="plus" class="h-5 w-5"></i>
                    Créer un examen
                </a>

               {{-- IMPORT --}}
                <a href="{{ route('exams.import.form') }}"
                    class="inline-flex items-center gap-2 rounded-2xl bg-white border border-[#EFE6DE] px-6 py-4 text-[#9A0002] font-black shadow hover:bg-[#FAF7F4] hover:-translate-y-1 transition-all">
                    <i data-lucide="upload" class="h-5 w-5"></i>
                    Importer un examen
                </a>

            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="rounded-[2rem] bg-white p-5 shadow-lg border border-[#EFE6DE]">
        <form method="GET" action="{{ route('exams.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            <div class="relative lg:col-span-6">
                <i data-lucide="search" class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Rechercher un examen..."
                       class="w-full rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] pl-12 pr-4 py-4 font-semibold outline-none focus:border-[#9A0002] focus:ring-4 focus:ring-[#9A0002]/10">
            </div>

            <select name="status"
                    class="lg:col-span-3 rounded-2xl border border-[#EFE6DE] bg-[#FAF7F4] px-4 py-4 font-semibold outline-none focus:border-[#9A0002]">
                <option value="">Tous les statuts</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Disponible</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>En préparation</option>
            </select>

            <button type="submit"
                    class="lg:col-span-2 rounded-2xl bg-[#9A0002] px-5 py-4 text-white font-black hover:bg-[#7A0001] hover:-translate-y-1 transition-all">
                Rechercher
            </button>

            @if(request('search') || request('status'))
                <a href="{{ route('exams.index') }}"
                   class="lg:col-span-1 rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] px-5 py-4 text-center font-black text-gray-600 hover:text-[#9A0002]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($exams as $exam)
            <div class="group relative overflow-hidden rounded-[2.5rem] bg-white p-6 shadow-lg border border-[#EFE6DE] hover:-translate-y-2 hover:shadow-2xl transition-all duration-500">

                <div class="absolute inset-x-0 top-0 h-2 bg-[#9A0002]"></div>
                <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-[#9A0002]/10 blur-3xl"></div>

                <div class="relative flex items-start justify-between">
                    <div class="h-16 w-16 rounded-[1.5rem] bg-[#9A0002]/10 text-[#9A0002] flex items-center justify-center group-hover:bg-[#9A0002] group-hover:text-white group-hover:rotate-6 transition-all">
                        <i data-lucide="file-text" class="h-7 w-7"></i>
                    </div>

                    <span class="rounded-full px-4 py-2 text-xs font-black
                        {{ $exam->questions_count > 0 ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $exam->questions_count > 0 ? 'Disponible' : 'En préparation' }}
                        @if($exam->is_locked)
                        <div class="mt-3">
                            <span class="px-3 py-2 rounded-full bg-red-100 text-red-700 text-xs font-black">
                                Verrouillé après correction
                            </span>
                        </div>
                        @endif

                    </span>
                </div>

                <div class="relative mt-6">
                    <h3 class="text-2xl font-black text-gray-950 group-hover:text-[#9A0002] transition">
                        {{ $exam->title }}
                    </h3>

                    <p class="mt-1 text-sm font-semibold text-gray-500">
                        {{ $exam->course_name }}
                    </p>

                    @if($exam->description)
                        <p class="mt-3 text-sm text-gray-500 line-clamp-2">
                            {{ $exam->description }}
                        </p>
                    @endif
                </div>

                <div class="relative mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-[#FAF7F4] p-4 text-center border border-[#EFE6DE]">
                        <p class="text-2xl font-black text-[#9A0002]">{{ $exam->questions_count }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase">Questions</p>
                    </div>

                    <div class="rounded-2xl bg-[#FAF7F4] p-4 text-center border border-[#EFE6DE]">
                        <p class="text-sm font-black text-gray-950">{{ $exam->created_at->format('d-m-Y') }}</p>
                        <p class="text-[10px] font-black text-gray-400 uppercase">Créé</p>
                    </div>
                </div>

                <div class="relative mt-6 flex flex-wrap gap-2 pt-5 border-t border-[#EFE6DE]">
                    @if($exam->questions_count > 0)
                        <a href="{{ route('exams.generate', $exam->id) }}"
                           class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-[#9A0002] px-4 py-3 text-white font-black text-sm hover:bg-[#7A0001] hover:-translate-y-1 transition-all">
                            <i data-lucide="settings-2" class="h-4 w-4"></i>
                            Continuer Vers Génération
                        </a>
                    @else
                        <button disabled
                                class="flex flex-1 items-center justify-center gap-2 rounded-2xl bg-gray-100 px-4 py-3 text-gray-400 font-black text-sm cursor-not-allowed">
                            <i data-lucide="lock" class="h-4 w-4"></i>
                            Continuer Vers Génération
                        </button>
                    @endif

                    <a href="{{ route('exams.show', $exam->id) }}"
                       class="rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] px-4 py-3 text-gray-700 hover:text-[#9A0002] hover:bg-white transition">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                    </a>

                    @if(!$exam->is_locked)
                    <a href="{{ route('exams.edit', $exam->id) }}"
                       class="rounded-2xl bg-[#FAF7F4] border border-[#EFE6DE] px-4 py-3 text-gray-700 hover:text-[#9A0002] hover:bg-white transition">
                        <i data-lucide="edit" class="h-4 w-4"></i>
                    </a>
                    @else
                    <div class="rounded-2xl bg-gray-100 border border-gray-200 px-4 py-3 text-gray-400 cursor-not-allowed">
                        <i data-lucide="lock" class="h-4 w-4"></i>
                    </div>
                    @endif
                    
                    @if(!$exam->is_locked)
                    <form action="{{ route('exams.destroy', $exam->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-red-600 hover:bg-red-100 transition">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </form>
                    @else
                        <div class="rounded-2xl bg-gray-100 border border-gray-200 px-4 py-3 text-gray-400 cursor-not-allowed">
                            <i data-lucide="shield-lock" class="h-4 w-4"></i>
                        </div>
                    @endif

                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[2.5rem] bg-white border-2 border-dashed border-[#EFE6DE] p-14 text-center">
                <i data-lucide="folder-open" class="mx-auto h-14 w-14 text-gray-300 mb-4"></i>
                <p class="text-gray-500 font-bold">Aucun examen créé pour le moment.</p>
                <a href="{{ route('exams.create') }}" class="mt-3 inline-block text-[#9A0002] font-black hover:underline">
                    Créer votre premier examen
                </a>
            </div>
        @endforelse
    </div>
</div>
<div class="mt-12 flex justify-center">
    <div class="inline-flex items-center gap-2 rounded-[2rem] bg-white p-3 shadow-lg border border-[#EFE6DE]">
        {{ $exams->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    if (window.lucide) lucide.createIcons();
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Supprimer cet examen ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#9A0002',
            cancelButtonColor: '#E5E7EB',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            background: '#ffffff',
            borderRadius: '25px',
            customClass: {
                popup: 'shadow-2xl',
                title: 'text-2xl font-black text-gray-900',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold text-gray-700'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

@endsection