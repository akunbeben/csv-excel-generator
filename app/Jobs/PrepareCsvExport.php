<?php

namespace App\Jobs;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use SplTempFileObject;

class PrepareCsvExport implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    protected Exporter $exporter;

    /**
     * @param  array<string, string>  $columnMap
     * @param  array<string, mixed>  $options
     * @param  array<mixed> | null  $records
     */
    public function __construct(
        protected Export $export,
        protected array $columnMap,
        protected array $options = [],
        protected int $chunkSize = 100,
        protected ?array $records = null,
        protected string $delimiter = ',',
    ) {
        $this->exporter = $this->export->getExporter(
            $this->columnMap,
            $this->options,
        );
    }

    public function handle(): void
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setOutputBOM(ByteSequence::BOM_UTF8);
        $csv->setDelimiter($this->delimiter);
        $csv->insertOne($this->columnMap);

        $filePath = $this->export->getFileDirectory() . DIRECTORY_SEPARATOR . 'headers.csv';
        $this->export->getFileDisk()->put($filePath, $csv->toString(), Filesystem::VISIBILITY_PRIVATE);

        $exportCsvJob = $this->getExportCsvJob();

        $totalRows = 0;

        $this->export->unsetRelation('user');

        $dispatchRecords = function (array $records) use ($exportCsvJob, &$totalRows) {
            $recordsCount = count($records);

            if (($totalRows + $recordsCount) > $this->export->total_rows) {
                $records = array_slice($records, 0, $this->export->total_rows - $totalRows);
                $recordsCount = count($records);
            }

            if (! $recordsCount) {
                return;
            }

            $jobs = [];

            foreach (array_chunk($records, length: $this->chunkSize) as $recordsChunk) {
                $jobs[] = app($exportCsvJob, [
                    'export' => $this->export,
                    'records' => $recordsChunk,
                    'columnMap' => $this->columnMap,
                    'options' => $this->options,
                    'delimiter' => $this->delimiter,
                ]);
            }

            $this->batch()->add($jobs);

            $totalRows += $recordsCount;
        };

        $dispatchRecords($this->records);
    }

    public function getExportCsvJob(): string
    {
        return ExportCsv::class;
    }
}
