<?php

namespace App\Filament\Widgets;

use App\Models\Classroom;
use Filament\Widgets\ChartWidget;

class StudentsPerClassChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Siswa Perkelas';

    protected function getData(): array
    {
        $classrooms = Classroom::with('student')->get();

        $labels = $classrooms->map(fn ($c) => "{$c->kelas} {$c->name}");

        $maleCounts = $classrooms->map(fn ($c) => $c->student->where('gender', 'L')->count());
        $femaleCounts = $classrooms->map(fn ($c) => $c->student->where('gender', 'P')->count());

        return [
            'datasets' => [
                [
                    'label' => 'Laki-laki',
                    'data' => $maleCounts,
                    'backgroundColor' => '#3b82f6',
                    'borderRadius' => 4,
                    'borderWidth' => 0,
                ],
                [
                    'label' => 'Perempuan',
                    'data' => $femaleCounts,
                    'backgroundColor' => '#ec4899',
                    'borderRadius' => 4,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected static ?string $maxHeight = '400px';

    protected int | string | array $columnSpan = '10px';
}
