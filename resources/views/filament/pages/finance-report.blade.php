<x-filament::page>
    <div class="space-y-6">

        {{-- Header dan Tombol Export --}}
        <div class="flex justify-end">
            <x-filament::button wire:click="exportGeneralExpense" color="success" icon="heroicon-s-document-arrow-down">
                Export Pemasukan & Pengeluaran
            </x-filament::button>
        </div>

        <x-filament::card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label for="startDate" class="text-sm font-medium text-gray-800 dark:text-gray-100">
                        Dari Tanggal
                    </label>
                    <input type="date" id="startDate" wire:model.defer="startDate"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                </div>

                <div class="space-y-1">
                    <label for="endDate" class="text-sm font-medium text-gray-800 dark:text-gray-100">
                        Sampai Tanggal
                    </label>
                    <input type="date" id="endDate" wire:model.lazy="endDate"
                        class="w-full rounded-lg border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                </div>

            </div>
        </x-filament::card>

        {{-- Ringkasan Keuangan --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-filament::card class="text-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Tagihan</h3>
                <p class="text-3xl font-bold {{ $balance >= 0 ? 'text-blue-600' : 'text-red-700' }} mt-2">
                    Rp {{ number_format($billTotal, 0, ',', '.') }}
                </p>
            </x-filament::card>

            <x-filament::card class="text-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Pemasukan</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">
                    Rp {{ number_format($incomeTotal, 0, ',', '.') }}
                </p>
            </x-filament::card>

            <x-filament::card class="text-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Pengeluaran</h3>
                <p class="text-3xl font-bold text-red-600 mt-2">
                    Rp {{ number_format($expenseTotal, 0, ',', '.') }}
                </p>
            </x-filament::card>

            <x-filament::card class="text-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Saldo Akhir</h3>
                <p class="text-3xl font-bold {{ $balance >= 0 ? 'text-blue-600' : 'text-red-700' }} mt-2">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </p>
            </x-filament::card>

        </div>

    </div>
</x-filament::page>
