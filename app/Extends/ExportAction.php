<?php

namespace App\Extends;

use App\Jobs\PrepareCsvExport;
use Closure;
use Filament\Actions;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Jobs\CreateXlsxFile;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Filament\Actions\Exports\Models\Export;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Number;

class ExportAction extends Actions\Action
{
    use Actions\Concerns\CanExportRecords;

    public Collection | Closure | null $records;

    public Collection | Closure | null $columns;

    public string $delimiter = ',';

    public function delimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function columns(Collection | Closure | null $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function getColumns(): Collection
    {
        return $this->columns = $this->evaluate($this->columns);
    }

    public function records(Collection | Closure | null $records): static
    {
        $this->records = $records;

        return $this;
    }

    public function getRecords(): Collection
    {
        return $this->records = $this->evaluate($this->records);
    }

    public function getJob(): string
    {
        return PrepareCsvExport::class;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // $this->label(fn (ExportAction $action): string => __('filament-actions::export.label', ['label' => $action->getPluralModelLabel()]));

        // $this->modalHeading(fn (ExportAction $action): string => __('filament-actions::export.modal.heading', ['label' => $action->getPluralModelLabel()]));

        // $this->modalSubmitActionLabel(__('filament-actions::export.modal.actions.export.label'));

        // $this->groupedIcon(FilamentIcon::resolve('actions::export-action.grouped') ?? 'heroicon-m-arrow-down-tray');

        $this->action(function (ExportAction $action, array $data, \Livewire\Component $livewire) {
            $exporter = $action->getExporter();

            $records = $action->getRecords();

            $totalRows = $records->count();
            $maxRows = $action->getMaxRows() ?? $totalRows;

            if ($maxRows < $totalRows) {
                Notification::make()
                    ->title(__('filament-actions::export.notifications.max_rows.title'))
                    ->body(trans_choice('filament-actions::export.notifications.max_rows.body', $maxRows, [
                        'count' => Number::format($maxRows),
                    ]))
                    ->danger()
                    ->send();

                return;
            }

            $user = auth()->user();

            $options = array_merge(
                $action->getOptions(),
                Arr::except($data, ['columnMap']),
            );

            $columnMap = collect($action->getColumns()->toArray())
                ->mapWithKeys(fn (ExportColumn $column): array => [$column->getName() => $column->getLabel()])
                ->all();

            $export = app(Export::class);
            $export->user()->associate($user);
            $export->exporter = $exporter;
            $export->total_rows = $totalRows;

            $exporter = $export->getExporter(
                columnMap: $columnMap,
                options: $options,
            );

            $export->file_disk = $action->getFileDisk() ?? $exporter->getFileDisk();
            $export->save();

            $export->file_name = $action->getFileName($export) ?? $exporter->getFileName($export);
            $export->save();

            $formats = $action->getFormats() ?? $exporter->getFormats();
            $hasCsv = in_array(ExportFormat::Csv, $formats);
            $hasXlsx = in_array(ExportFormat::Xlsx, $formats);

            $job = $action->getJob();
            $jobQueue = $exporter->getJobQueue();
            $jobConnection = $exporter->getJobConnection();
            $jobBatchName = $exporter->getJobBatchName();

            // We do not want to send the loaded user relationship to the queue in job payloads,
            // in case it contains attributes that are not serializable, such as binary columns.
            $export->unsetRelation('user');

            $makeCreateXlsxFileJob = fn (): CreateXlsxFile => app(CreateXlsxFile::class, [
                'export' => $export,
                'columnMap' => $columnMap,
                'options' => $options,
            ]);

            Bus::chain([
                Bus::batch([app($job, [
                    'export' => $export,
                    'columnMap' => $columnMap,
                    'options' => $options,
                    'chunkSize' => $action->getChunkSize(),
                    'records' => $action->getRecords()->toArray(),
                    'delimiter' => $action->getDelimiter() ?? ',',
                ])])
                    ->when(
                        filled($jobQueue),
                        fn (PendingBatch $batch) => $batch->onQueue($jobQueue),
                    )
                    ->when(
                        filled($jobConnection),
                        fn (PendingBatch $batch) => $batch->onConnection($jobConnection),
                    )
                    ->when(
                        filled($jobBatchName),
                        fn (PendingBatch $batch) => $batch->name($jobBatchName),
                    )
                    ->allowFailures(),
                ...(($hasXlsx && (! $hasCsv)) ? [$makeCreateXlsxFileJob()] : []),
                app(ExportCompletion::class, [
                    'export' => $export,
                    'columnMap' => $columnMap,
                    'formats' => $formats,
                    'options' => $options,
                ]),
                ...(($hasXlsx && $hasCsv) ? [$makeCreateXlsxFileJob()] : []),
            ])
                ->when(
                    filled($jobQueue),
                    fn (PendingChain $chain) => $chain->onQueue($jobQueue),
                )
                ->when(
                    filled($jobConnection),
                    fn (PendingChain $chain) => $chain->onConnection($jobConnection),
                )
                ->dispatch();

            Notification::make()
                ->title($action->getSuccessNotificationTitle())
                ->body(trans_choice('filament-actions::export.notifications.started.body', $export->total_rows, [
                    'count' => Number::format($export->total_rows),
                ]))
                ->success()
                ->send();
        });

        $this->color('gray');

        $this->modalWidth('xl');

        $this->successNotificationTitle(__('filament-actions::export.notifications.started.title'));
    }
}
