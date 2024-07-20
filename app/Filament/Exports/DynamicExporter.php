<?php

namespace App\Filament\Exports;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;

class DynamicExporter extends Exporter
{
    public function __invoke(Model $record): array
    {
        $this->record = $record;

        $columns = $this->getCachedColumns();

        $data = [];

        foreach ($columns as $column) {
            $data[] = $columns[$column]->getFormattedState();
        }

        return $data;
    }

    public static function getColumns(): array
    {
        return [];
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Seed Completed';
    }

    public function getFileName(Export $export): string
    {
        return now()->format('Y-m-d-H-i-s');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your seed has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' seeded.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to seed.';
        }

        return $body;
    }
}
