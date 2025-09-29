<?php

namespace App\Filament\Treasurer\Resources;

use Filament\Forms;
use App\Models\Bill;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;

use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\StudentPayment;
use App\Models\FinanceCategory;
use App\Models\StudentDiscount;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentPaymentResource\Pages;
use App\Filament\Resources\StudentPaymentResource\RelationManagers;

class StudentPaymentResource extends Resource
{
    protected static ?string $pluralModelLabel = 'Pemasukan Tagihan';
    protected static ?string $model = StudentPayment::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';
    protected static ?string $navigationLabel = 'Pemasukan Tagihan';
    protected static ?string $navigationGroup = 'Keuangan Sekolah';
    protected static ?string $modelLabel = 'Pemasukan Tagihan';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('student_id')
                    ->label('Siswa')
                    ->options(
                        \App\Models\User::with('studentProfile.classroom')
                            ->where('role', 'student')
                            ->get()
                            ->mapWithKeys(function ($user) {
                                $nis = $user->studentProfile->nis ?? '-';
                                $classroom = $user->studentProfile->classroom;
                                $kelas = Str::title($classroom->kelas ?? '-');
                                $jenjang = Str::title($classroom->jenjang ?? '-');
                                $namaKelas = Str::title($classroom->name ?? '-');
                                $name = Str::title($user->name);

                                return [
                                    $user->id => "{$name} - {$kelas} {$namaKelas}"
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                Select::make('bill_id')
                    ->label('Tagihan Yang Dibayar')
                    ->reactive()
                    ->live(onBlur: true)
                    ->options(function (Get $get) {
                        $studentId = $get('student_id');
                        if (!$studentId) return [];
                        $profile = \App\Models\StudentProfile::where('user_id', $studentId)->first();
                        if (!$profile) return [];
                        return \App\Models\Bill::where('student_profile_id', $profile->id)
                            ->get()
                            ->filter(fn($bill) => !$bill->payments()->latest()->first() || $bill->payments()->latest()->first()->sum === 'kurang')
                            ->mapWithKeys(fn($bill) => [
                                $bill->id => Str::title($bill->nama_tagihan) . ' - Rp ' . number_format($bill->amount)
                            ]);
                    }),

                Select::make('discount_id')
                    ->label('Diskon (jika ada)')
                    ->options(\App\Models\StudentDiscount::pluck('name', 'id'))
                    ->reactive()
                    ->live(onBlur: true)
                    ->afterStateUpdated(
                        fn(Set $set, Get $get) =>
                        \App\Filament\Resources\StudentPaymentResource::updateTotalAmount(
                            $get('bill_id'),
                            $get('discount_id'),
                            $set
                        )
                    ),

                TextInput::make('total_amount')
                    ->label('Total Pembayaran')
                    ->numeric()
                    ->live()
                    ->reactive()
                    ->readOnly()  // Hanya bisa dibaca, karena ini dihitung berdasarkan diskon
                    ->dehydrated()
                    ->prefix('Rp.')
                    ->hint(function (callable $get) {
                        $billId = $get('bill_id');
                        if (!$billId) return null;
                        $bill = \App\Models\Bill::find($billId);
                        return $bill ? 'Original bill: Rp ' . number_format($bill->amount, 0, ',', '.') : null;
                    }),

                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->label('Jumlah Yang Dibayar')
                    ->live()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set, Get $get) => $set(
                        'sum',
                        match (true) {
                            (int) $get('paid_amount') > (int) $get('total_amount') => 'lebih',
                            (int) $get('paid_amount') < (int) $get('total_amount') => 'kurang',
                            default => 'lunas',
                        }
                    )),

                Textarea::make('reason')
                    ->rows(3)->columnSpanFull()
                    ->helperText(new HtmlString(
                        'Nominal yang dimasukkan <strong>kurang atau lebih dari harga yang telah diakumulasi</strong>'
                    ))->label(new HtmlString('Alasan pembayaran lebih <strong>rendah atau tinggi</strong>'))
                    ->visible(fn(Get $get) => (int) $get('paid_amount') !== (int) $get('total_amount'))
                    ->required(fn(Get $get) => (int) $get('paid_amount') !== (int) $get('total_amount')),

                Textarea::make('note')
                    ->label('Keterangan')
                    ->required()
                    ->rows(2)->columnSpanFull(),

                Select::make('sum')
                    ->label('Status Pembayaran')
                    ->options([
                        'lebih' => 'Pembayaran Lebih',
                        'kurang' => 'Pembayaran Kurang',
                        'lunas' => 'Pembayaran Pas',
                    ])
                    ->default(fn(Get $get) => match (true) {
                        (int) $get('paid_amount') > (int) $get('total_amount') => 'lebih',
                        (int) $get('paid_amount') < (int) $get('total_amount') => 'kurang',
                        default => 'lunas',
                    })
                    ->reactive()->disabled()->dehydrated(),

                Select::make('method')->options([
                    'Transfer' => 'Transfer',
                    'Cash' => 'Cash'
                ])->label('Metode Pembayaran')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No. ')
                    ->rowIndex(),
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => Str::title($state)),
                TextColumn::make('kelas')
                    ->label('Kelas')
                    ->getStateUsing(function ($record) {
                        $classroom = $record->student?->studentProfile?->classroom;

                        if (!$classroom) {
                            return '-';
                        }

                        $kelas = Str::title($classroom->kelas ?? '-');
                        $jenjang = Str::title($classroom->jenjang ?? '-');
                        $namaKelas = Str::title($classroom->name ?? '-');

                        return "{$kelas} {$namaKelas}";
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill.nama_tagihan')
                    ->label('Nama Tagihan')
                    ->formatStateUsing(function ($state, $record) {
                        $categories = \App\Models\FinanceCategory::whereIn('id', $record->bill->category_ids ?? [])
                            ->pluck('name')
                            ->map(fn($name) => Str::title($name))
                            ->implode(', ');

                        $classroom = optional($record->bill->studentProfile->classroom);
                        $classroomName = $classroom
                            ? Str::title("{$classroom->kelas} {$classroom->jenjang} {$classroom->name}")
                            : 'Tanpa Kelas';

                        return Str::title($record->bill->nama_tagihan) . ' - ' . $categories . ' - ' . $classroomName;
                    })
                    ->limit(30)
                    ->tooltip(fn($state) => $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('Rp.')
                    ->searchable()
                    ->sortable()
                    ->label('Total Bayar'),
                TextColumn::make('paid_amount')
                    ->money('Rp.')
                    ->label('Jumlah Dibayar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sisa_kekurangan')
                    ->label('Kekurangan')
                    ->money('Rp.')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return max($record->total_amount - $record->paid_amount, 0);
                    })
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('sum')
                    ->label('Status Pelunasan')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'lunas' => 'success',
                        'kurang' => 'danger',
                        'lebih' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Metode Pembayaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('receipt_number')
                    ->label('No. Kwitansi')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bill.tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Waktu')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sum')
                    ->label('Status Pelunasan')
                    ->options([
                        'lunas' => 'Pembayaran Pas',
                        'kurang' => 'Pembayaran Kurang',
                        'lebih' => 'Pembayaran Lebih',
                    ]),

                Tables\Filters\SelectFilter::make('method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'Transfer' => 'Transfer',
                        'Cash' => 'Cash',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->tooltip('Riwayat')
                    ->icon('heroicon-o-clock')
                    ->color('info'),

                Tables\Actions\Action::make('kwitansi')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->tooltip('Cetak Kwitansi')
                    ->url(fn($record) => route('kwitansi.student-payment', ['id' => $record->id]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('sendReceipt')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->tooltip('Kirim Pesan ke Orang Tua')
                    ->label('')
                    ->form(function ($record) {
                        $phones = $record->student->studentProfile->parent_phones ?? [];

                        $options = collect($phones)->mapWithKeys(function ($p) {
                            return [$p['number'] => "{$p['name']} - {$p['number']}"];
                        })->toArray();

                        return [
                            Forms\Components\CheckboxList::make('selected_phones')
                                ->label('Pilih Nomor Tujuan')
                                ->options($options)
                                ->required(),
                        ];
                    })
                    ->action(function ($data, $record) {
                        foreach ($data['selected_phones'] as $number) {
                            $cleanNumber = preg_replace('/[^0-9]/', '', $number);

                            if (str_starts_with($cleanNumber, '0')) {
                                $cleanNumber = '62' . substr($cleanNumber, 1);
                            }

                            // Data kelas
                            $classroom = $record->student->studentProfile->classroom;
                            $kelasLengkap = "{$classroom->kelas} {$classroom->jenjang} {$classroom->name}";

                            // Format pesan
                            $message = "Assalamu'alaikum Bapak/Ibu,\n\n" .
                                "Kami informasikan bahwa pembayaran atas nama siswa:\n" .
                                "Nama Siswa       : {$record->student->name}\n" .
                                "Kelas            : {$kelasLengkap}\n" .
                                "Tanggal Bayar    : {$record->created_at->format('d-m-Y')}\n" .
                                "Jumlah           : Rp " . number_format($record->total_amount, 0, ',', '.') . "\n\n" .
                                "No. Kwitansi     : {$record->receipt_number}\n\n" .
                                "Telah kami terima dengan baik.\n\n" .
                                "Terima kasih atas kerja sama dan perhatiannya.\n\n" .
                                "Hormat kami,\nBendahara Sekolah";
                            $url = "https://wa.me/{$cleanNumber}?text=" . urlencode($message);
                            return redirect()->away($url);
                        }
                    }),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentPayments::route('/'),
            // 'create' => Pages\CreateStudentPayment::route('/create'),
            // 'view' => Pages\ViewStudentPayment::route('/{record}'),
            // 'edit' => Pages\EditStudentPayment::route('/{record}/edit'),
        ];
    }

    // ============================================

    public static function calculateAmount(int $billId, ?int $discountId): float
    {
        $bill = Bill::find($billId);
        if (!$bill) return 0;

        $billAmount = $bill->amount;

        // Apply discount
        if ($discountId) {
            $discount = StudentDiscount::find($discountId);
            if ($discount && $discount->percentage) {
                $billAmount -= ($billAmount * ($discount->percentage / 100));
            }
        }

        // Subtract all previous payments
        $paid = StudentPayment::where('bill_id', $billId)->sum('paid_amount');

        return max($billAmount - $paid, 0);
    }

    public static function updateTotalAmount($billId, $discountId, $set)
    {
        $bill = Bill::find($billId);
        if (!$bill) {
            $set('total_amount', 0);
            return;
        }

        $billAmount = $bill->amount;

        // Apply discount
        if ($discountId) {
            $discount = StudentDiscount::find($discountId);
            if ($discount && $discount->percentage) {
                $billAmount -= ($billAmount * ($discount->percentage / 100));
            }
        }

        // Calculate remaining debt
        $paid = StudentPayment::where('bill_id', $billId)->sum('paid_amount');
        $remainingDebt = max($billAmount - $paid, 0);

        // ✅ This now sets total_amount to the remaining debt
        $set('total_amount', $remainingDebt);

        // ❌ Don’t overwrite paid_amount fully
        // Instead: only prefill if no payments exist yet
        if ($paid == 0) {
            $set('paid_amount', $remainingDebt);
        }
    }
}
