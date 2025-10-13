<x-filament::page>
    @php

        $user = auth()->user();
        $studentProfile = $user->studentProfile;

        // Total tagihan hanya milik siswa ini
        $totalTagihan = \App\Models\Bill::where('student_profile_id', $studentProfile->id)->sum('amount');

        // Total pembayaran hanya milik siswa ini
        $totalPembayaran = \App\Models\StudentPayment::where('student_id', $user->id)->sum('paid_amount');

        // Hitung sisa tagihan
        $sisaTagihan = $totalTagihan - $totalPembayaran;

        // Riwayat Pembayaran Bulanan
        $monthlyPayments = \App\Models\StudentPayment::selectRaw(
            "DATE_FORMAT(created_at, '%Y-%m') as bulan, SUM(paid_amount) as total",
        )
            ->where('student_id', $user->id)
            ->groupBy('bulan')
            ->orderBy('bulan', 'desc')
            ->get();

        $studentPayments = \App\Models\StudentPayment::with('bill')->where('student_id', $user->id)->latest()->get();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 flex">
                <div
                    class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 flex flex-col items-center justify-center hover:shadow-xl h-full">
                    <img src="{{ $studentProfile?->photo ? asset('storage/' . $studentProfile->photo) : asset('storage/images/misadaku.png') }}"
                        alt="Foto Profil" class="bg-blue-900 shadow-md mb-4 rounded-full"
                        style="width: 120px; height: 120px; object-fit: cover; object-position: center;">
                </div>
            </div>

            <div class="md:col-span-2 flex">
                <div
                    class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 hover:shadow-xl h-full">
                    <h2 class="text-md font-bold text-gray-800 dark:text-gray-100 mb-4">Informasi Profil</h2>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-700 dark:text-gray-200">
                        <div>
                            <p class="font-semibold">Nama</p>
                            <p class="capitalize">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Email</p>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Role</p>
                            <p>
                                {{ $user->role === 'student' ? 'Murid' : Str::headline($user->role) }}
                            </p>
                        </div>
                        <div>
                            <p class="font-semibold">NIS</p>
                            <p>{{ $studentProfile?->nis ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="font-semibold">Kelas</p>
                            <p>
                                {{ $studentProfile?->classroom?->kelas }} 
                                {{-- {{ $studentProfile?->classroom?->jenjang }} --}}
                                {{ $studentProfile?->classroom?->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-filament::page>
