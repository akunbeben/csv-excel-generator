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

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your dynamic export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
