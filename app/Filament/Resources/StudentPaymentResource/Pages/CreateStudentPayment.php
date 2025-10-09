<?php

namespace App\Filament\Resources\StudentPaymentResource\Pages;

use App\Filament\Resources\StudentPaymentResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\StudentPayment;

class CreateStudentPayment extends CreateRecord
{
    protected static string $resource = StudentPaymentResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $lastPayment = null;

        // Kalau banyak bill dipilih (bill_ids)
        if (!empty($data['bill_ids']) && is_array($data['bill_ids'])) {
            foreach ($data['bill_ids'] as $billId) {
                $lastPayment = StudentPayment::create([
                    'student_id'   => $data['student_id'],
                    'bill_id'      => $billId,
                    'discount_id'  => $data['discount_id'] ?? null,
                    'total_amount' => StudentPaymentResource::calculateAmount($billId, $data['discount_id'] ?? null),
                    'paid_amount'  => $data['paid_amount'] ?? 0,
                    'note'         => $data['note'] ?? null,
                    'method'       => $data['method'] ?? 'cash',
                ]);
            }

            return $lastPayment;
        }

        // fallback kalau cuma satu bill
        if (!empty($data['bill_id'])) {
            return StudentPayment::create([
                'student_id'   => $data['student_id'],
                'bill_id'      => $data['bill_id'],
                'discount_id'  => $data['discount_id'] ?? null,
                'total_amount' => $data['total_amount'],
                'paid_amount'  => $data['paid_amount'],
                'note'         => $data['note'] ?? null,
                'method'       => $data['method'],
                'sum'          => $data['sum'],
            ]);
        }

        throw new \Exception('Tagihan belum dipilih.');
    }
}
