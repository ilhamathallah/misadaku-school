<x-guest-layout>
        <div class="text-center bg-white p-10 rounded-xl w-full max-w-md">
            <div class="flex justify-center items-center">
                <a href="/" class="bg-yellow-100 text-yellow-600 rounded-full p-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" class="w-16 h-16">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                    </svg>
                </a>
            </div>

            <h1 class="text-2xl font-bold text-gray-800">Menunggu Persetujuan Operator</h1>
            <p class="text-gray-600 mt-4 mb-6">
                Akun Anda sedang dalam proses verifikasi.<br>
                Silakan tunggu hingga admin menyetujui permintaan akses Anda.
            </p>

            {{--
            <form action="{{ route('request-role') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Minta Persetujuan ke Admin
                </button>
            </form>

            @if (session('status'))
                <p class="mt-4 text-green-600">{{ session('status') }}</p>
            @endif
            --}}
        </div>
</x-guest-layout>
