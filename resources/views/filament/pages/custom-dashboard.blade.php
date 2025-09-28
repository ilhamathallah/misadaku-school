<x-filament::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card Kiri -->
        <div class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 hover:shadow-xl">

            <!-- Header -->
            {{-- <h2 class="text-md font-bold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                Informasi Profil
            </h2> --}}

            <!-- Foto + Data User + Logout -->
            <div class="flex items-center justify-between gap-4">
                <!-- Kiri: Foto & Data -->
                <div class="flex items-center gap-4">
                    <img src="{{ asset('storage/images/misadaku.png') }}" alt="Profile" class="shadow-md"
                        style="width: 60px; height: 60px; object-fit: cover; object-position: center;">

                    {{-- <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . auth()->user()->name }}"
                        alt="Profile" class="rounded-full  shadow-md"
                        style="width: 50px; height: 50px; object-fit: cover; object-position: center;"> --}}

                    <div>
                        <p class="text-md font-semibold text-gray-900 dark:text-gray-100 capitalize">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>

                <!-- Kanan: Tombol Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-filament::button type="submit" color="danger" icon="heroicon-o-arrow-left-end-on-rectangle">
                        Logout
                    </x-filament::button>
                </form>

            </div>
        </div>

        {{-- <div class="bg-white w-full dark:bg-gray-900 shadow rounded-xl p-6 transition duration-300 hover:shadow-lg"> --}}

        <div class="flex gap-4 dark:text-gray-100">

            <!-- Total Siswa -->
            <div class="bg-white text-green-600 w-full dark:bg-gray-900 p-6 rounded-lg shadow flex flex-col items-start gap-4">
                <!-- Label + Icon -->
                <div class="flex items-center gap-2">
                    <x-heroicon-s-user-group class="w-6 h-6" />
                    {{-- <x-heroicon-s-user-group class="w-6 h-6" style="color: #16a34a;" /> --}}
                    <p class="text-md font-semibold dark:text-gray-300">Total Siswa</p>
                </div>
                <div class="mt-1 flex items-center justify-between w-full">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\StudentProfile::where('is_active', true)->count() }}
                    </p>
                    <a href="{{ route('filament.admin.resources.student-profiles.index') }}">
                        <x-heroicon-s-arrow-right class="w-5 h-5 text-gray-600 hover:text-blue-600 transition" />
                    </a>
                </div>
            </div>

            <!-- Total Guru -->
            <div class="bg-white w-full dark:bg-gray-900 p-6 rounded-lg shadow flex flex-col items-start gap-4">
                <!-- Label + Icon -->
                <div class="flex items-center gap-2">
                    <x-heroicon-s-building-library class="w-6 h-6 text-blue-800" />
                    {{-- <x-heroicon-s-user-group class="w-6 h-6" style="color: #16a34a;" /> --}}
                    <p class="text-md font-semibold dark:text-gray-300">Total Kelas</p>
                </div>
                <div class="mt-1 flex items-center justify-between w-full">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\Classroom::count() }}
                    </p>
                    <a href="{{ route('filament.admin.resources.classrooms.index') }}">
                        <x-heroicon-s-arrow-right class="w-5 h-5 text-gray-600 hover:text-blue-600 transition" />
                    </a>
                </div>
            </div>
        </div>
        {{-- </div> --}}

        <div class=" text-gray-600">
            {{-- Render widget FinanceChart --}}
            @livewire(\App\Filament\Widgets\FinanceChart::class)
        </div>

        <div class="text-gray-600 dark:text-gray-400">
            @livewire(\App\Filament\Widgets\StudentsPerClassChart::class)
        </div>
    </div>
</x-filament::page>
