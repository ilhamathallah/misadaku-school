<?php

namespace App\Filament\Treasurer\Resources;

use Filament\Forms;
use App\Models\Bill;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;

use App\Models\Classroom;
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
use Filament\Forms\Components\CheckboxList;
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
                                    $user->id => "{$name} - {$kelas}{$namaKelas}"
                                ];
                            })
                            ->toArray()
                    )
                    ->searchable()
                    ->required(),

                Select::make('bill_ids')
                    ->label('Tagihan Yang Dibayar')
                    ->options(function (Get $get) {
                        $studentId = $get('student_id');
                        $selectedBillIds = $get('bill_ids') ?? [];

                        $bills = collect();

                        // Ambil bill dari student_id (normal)
                        if ($studentId) {
                            $profile = \App\Models\StudentProfile::where('user_id', $studentId)->first();
                            if ($profile) {
                                $bills = $bills->merge(
                                    \App\Models\Bill::where('student_profile_id', $profile->id)->get()
                                );
                            }
                        }

                        // Tambahin bill yang udah dipilih juga (biar gak ilang di edit)
                        if (!empty($selectedBillIds)) {
                            $ids = is_array($selectedBillIds) ? $selectedBillIds : json_decode($selectedBillIds, true);
                            $bills = $bills->merge(
                                \App\Models\Bill::whereIn('id', $ids)->get()
                            );
                        }

                        // Hapus duplikat
                        $bills = $bills->unique('id');

                        // Generate options
                        $options = [];
                        foreach ($bills as $bill) {
                            $totalPaid = \App\Models\StudentPayment::whereJsonContains('bill_ids', (string) $bill->id)
                                ->orWhereJsonContains('bill_ids', (int) $bill->id)
                                ->sum('paid_amount');

                            if ($totalPaid < $bill->amount || in_array($bill->id, (array) $selectedBillIds)) {
                                $options[$bill->id] = \Illuminate\Support\Str::title($bill->nama_tagihan)
                                    . ' - Total: Rp ' . number_format($bill->amount, 0, ',', '.');
                            }
                        }

                        return $options;
                    })
                    ->multiple()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotalAmount($get('bill_ids'), $get('discount_id'), $set))
                    ->required(),

                Select::make('discount_id')
                    ->label('Diskon (jika ada)')
                    ->options(\App\Models\StudentDiscount::pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn(Set $set, Get $get) => self::updateTotalAmount($get('bill_ids'), $get('discount_id'), $set)),

                TextInput::make('total_amount')
                    ->label('Total Pembayaran')
                    ->numeric()
                    ->readOnly()
                    ->dehydrated()
                    ->prefix('Rp. ')
                    ->hint(function (Get $get) {
                        $billIds = $get('bill_ids');
                        if (empty($billIds)) return null;

                        $billIds = is_array($billIds) ? $billIds : [$billIds];
                        $details = \App\Models\Bill::whereIn('id', $billIds)->get()
                            ->map(fn($bill) => "{$bill->nama_tagihan}: Rp " . number_format($bill->amount, 0, ',', '.'))
                            ->implode(' | ');

                        return new HtmlString("<small>{$details}</small>");
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
                        if (!$classroom) return '-';
                        return Str::title("{$classroom->kelas}{$classroom->name}");
                    })
                    ->sortable(),

                TextColumn::make('bill_names')
                    ->label('Nama Tagihan')
                    ->getStateUsing(function ($record) {
                        if (empty($record->bill_ids)) {
                            return '-';
                        }

                        $billIds = is_array($record->bill_ids) ? $record->bill_ids : json_decode($record->bill_ids, true);
                        $bills = \App\Models\Bill::whereIn('id', $billIds)->get();

                        return $bills->map(function ($bill) {
                            $categories = \App\Models\FinanceCategory::whereIn('id', $bill->category_ids ?? [])
                                ->pluck('name')
                                ->map(fn($name) => \Illuminate\Support\Str::title($name))
                                ->implode(', ');

                            $classroom = optional($bill->studentProfile?->classroom);
                            $classroomName = $classroom
                                ? \Illuminate\Support\Str::title("{$classroom->kelas}{$classroom->name}")
                                : 'Tanpa Kelas';

                            return \Illuminate\Support\Str::title($bill->nama_tagihan) . ' - ' . $categories . ' - ' . $classroomName;
                        })->implode(' | ');
                    })
                    ->limit(40)
                    ->tooltip(fn($state) => $state)
                    ->searchable(),
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
                    // ->searchable()
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
                TextColumn::make('due_date')
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

                Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->searchable()
                    ->options(function () {
                        return \App\Models\Classroom::all()
                            ->mapWithKeys(function ($classroom) {
                                $kelas = Str::title($classroom->kelas ?? '-');
                                // $jenjang = Str::title($classroom->jenjang ?? '-');
                                $namaKelas = Str::title($classroom->name ?? '-');
                                return [$classroom->id => "{$kelas}{$namaKelas}"];
                            })->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('student.studentProfile', function ($q) use ($data) {
                                $q->where('classroom_id', $data['value']);
                            });
                        }
                    }),

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

                            $classroom = $record->student->studentProfile->classroom;
                            $kelasLengkap = "{$classroom->kelas} {$classroom->jenjang} {$classroom->name}";

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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
        ];
    }

    public static function calculateAmount(int $billId, ?int $discountId): float
    {
        $bill = Bill::find($billId);
        if (!$bill) return 0;

        $billAmount = $bill->amount;

        if ($discountId) {
            $discount = StudentDiscount::find($discountId);
            if ($discount && $discount->percentage) {
                $billAmount -= ($billAmount * ($discount->percentage / 100));
            }
        }

        $paid = StudentPayment::whereJsonContains('bill_ids', $billId)->sum('paid_amount');

        return max($billAmount - $paid, 0);
    }

    public static function updateTotalAmount($billIds, $discountId, $set)
    {
        if (empty($billIds)) {
            $set('total_amount', 0);
            $set('paid_amount', 0);
            return;
        }

        $billIds = is_array($billIds) ? $billIds : [$billIds];

        $total = 0;
        foreach ($billIds as $billId) {
            $bill = \App\Models\Bill::find($billId);
            if (!$bill) continue;

            $billAmount = $bill->amount;

            // kalau ada diskon, kurangi sesuai persen
            if ($discountId) {
                $discount = \App\Models\StudentDiscount::find($discountId);
                if ($discount && $discount->percentage) {
                    $billAmount -= ($billAmount * ($discount->percentage / 100));
                }
            }

            // cek kalau sudah ada pembayaran sebelumnya
            $paid = \App\Models\StudentPayment::whereJsonContains('bill_ids', $billId)->sum('paid_amount');
            $remainingDebt = max($billAmount - $paid, 0);

            $total += $remainingDebt;
        }

        // set total ke total_amount
        $set('total_amount', $total);

        // default isi paid_amount = total, tapi user tetap bisa ubah
        if ($total > 0) {
            $set('paid_amount', $total);
        }
    }
}
