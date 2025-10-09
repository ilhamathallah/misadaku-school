<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kwitansi - Misadaku School</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/misadaku.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 148mm 105mm;
            margin: 5mm;
        }

        @media print {
            body {
                background: white !important;
                margin: 0;
                padding: 0;
                font-size: 9px;
                line-height: 1.2;
            }

            .print-area {
                width: 100%;
                height: auto;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-2 print:bg-white print:p-0">
    <div class="print-area mx-auto bg-white p-3 border border-gray-800">

        <div class="flex items-center mb-2">
            <div class="w-12">
                <img src="{{ asset('storage/images/misadaku.png') }}" class="h-10 w-auto">
            </div>
            <div class="flex-1 text-center">
                <h2 class="text-[10px] font-bold uppercase">Madrasah Ibtidaiyah Saadatuddarain</h2>
                <p class="text-[8px] leading-tight">Jl. Gandaria No. 44, RT 10/RW 3, Kel. Pekayon, Kec. Pasar Rebo,
                    Kota Jakarta Timur, DKI Jakarta 13710</p>
                <p class="text-[8px] leading-tight">Telp: (021) 8705791 | Email: misaadatuddarain@gmail.com</p>
            </div>
        </div>

        <hr class="border-black mb-2">

        <h1 class="text-center text-[12px] font-bold mb-6 underline">KWITANSI PEMBAYARAN SISWA</h1>

        <div class="flex items-center mb-1">
            <span class="w-24 text-[9px] font-semibold">No. Kwitansi</span>
            <div class="flex-1 text-[9px]">
                {{ $payment->receipt_number ?? '.............' }}
            </div>
        </div>

        <div class="flex items-start mb-1">
            <span class="w-24 text-[9px]">Sudah terima dari</span>
            <div class="flex-1 border-b border-dotted border-gray-500 text-[9px]">
                {{ \Illuminate\Support\Str::title($payment->student?->name ?? '............................') }}
                @if ($payment->student?->studentProfile?->classroom) - {{ $payment->student->studentProfile->classroom->kelas }}{{ $payment->student->studentProfile->classroom->name }} 
                @endif
            </div>
        </div>

        <div class="flex items-start mb-1">
            <span class="w-24 text-[9px]">Uang sejumlah</span>
            <div class="flex-1 border-b border-dotted border-gray-500 text-[9px] font-semibold">
                Rp {{ number_format($payment->paid_amount ?? 0, 0, ',', '.') }}
            </div>
        </div>

        <div class="flex items-start mb-1">
            <span class="w-24 text-[9px]">Untuk Pembayaran</span>
            <div class="flex-1 border-b border-dotted border-gray-500 text-[9px]">
                @if ($payment->bills && $payment->bills->count() > 0)
                    {{ $payment->bills->pluck('nama_tagihan')->map(fn($n) => ucwords($n))->join(', ') }}
                @else
                    ........................................
                @endif
            </div>
        </div>

        <div class="flex items-start mt-2">
            <span class="w-20 text-[9px]">Terbilang</span>
            <div class="flex-1 bg-gray-100 px-2 py-1 text-[9px] italic">
                {{ $payment->terbilang ? ucwords($payment->terbilang) : '(..........................)' }}
            </div>
        </div>

        <div class="mt-4 flex justify-between">
            <div class="text-center text-[8px]">
                <p>Penerima</p>
                <p class="mt-8">___________________</p>
            </div>
            <div class="text-center text-[8px]">
                <p>{{ now()->translatedFormat('d F Y') }}</p>
                <p>Bendahara</p>
                <p class="mt-8">___________________</p>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
