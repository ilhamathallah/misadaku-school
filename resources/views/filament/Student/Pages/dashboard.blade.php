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
        <!-- Profile Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Foto Profil -->
            <div class="md:col-span-1 flex">
                <div
                    class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 flex flex-col items-center justify-center hover:shadow-xl h-full">
                    <img src="{{ $studentProfile?->photo ? asset('storage/' . $studentProfile->photo) : asset('storage/images/misadaku.png') }}"
                        alt="Foto Profil" class="bg-blue-900 shadow-md mb-4"
                        style="width: 120px; height: 120px; object-fit: cover; object-position: center;">

                    {{-- <img src="{{ auth()->user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . auth()->user()->name }}"
                        alt="Profile" class="rounded-full  shadow-md"
                        style="width: 50px; height: 50px; object-fit: cover; object-position: center;"> --}}

                </div>
            </div>

            <!-- Informasi Siswa -->
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
                                {{ $studentProfile?->classroom?->kelas }} {{ $studentProfile?->classroom?->jenjang }}
                                {{ $studentProfile?->classroom?->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Cards -->
        {{-- <div class="col-span-1 md:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Total Pembayaran -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 transition duration-300 hover:shadow-xl">
                <h2 class="text-md font-bold text-gray-800 dark:text-gray-100 mb-2">Total Pembayaran Kamu</h2>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                    Rp {{ number_format($totalPembayaran, 0, ',', '.') }}
                </p>
            </div>

            <!-- Tagihan Belum Dibayar -->
            <div
                class="relative overflow-hidden bg-white dark:bg-gray-900 w-full shadow rounded-xl p-6 transition duration-300 hover:shadow-xl">
                <h2 class="text-md font-bold text-gray-800 dark:text-gray-100 mb-2">Tagihan Belum Dibayar</h2>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                    Rp {{ number_format($sisaTagihan, 0, ',', '.') }}
                </p>
            </div>
        </div> --}}
    </div>

    <!-- Chart Section -->
    <!-- Riwayat Pembayaran dalam Card -->
    {{-- <div class="mt-8 bg-white dark:bg-gray-900 shadow rounded-xl p-6 transition duration-300 hover:shadow-xl">
        <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">
            Riwayat Pembayaran Bulanan
        </h2>

        @if ($studentPayments->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($studentPayments as $payment)
                    <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg shadow-inner">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                            <div class="space-y-1">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ $payment->bill->nama_tagihan ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    No. Kwitansi: {{ $payment->receipt_number ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Dibayar pada:
                                    {{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d F Y') }}
                                </div>
                            </div>

                            <div class="text-sm font-semibold text-green-600 dark:text-green-400">
                                Rp {{ number_format($payment->paid_amount, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Belum ada pembayaran tercatat.
            </p>
        @endif
    </div> --}}

</x-filament::page>
