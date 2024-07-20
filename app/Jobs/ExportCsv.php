<?php

namespace App\Jobs;

use Carbon\CarbonInterface;
use Exception;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use League\Csv\Writer;
use SplTempFileObject;
use Throwable;

class ExportCsv implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public bool $deleteWhenMissingModels = true;

    protected Exporter $exporter;

    /**
     * @param  array<mixed>  $records
     * @param  array<string, string>  $columnMap
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        protected Export $export,
        protected array $records,
        protected array $columnMap,
        protected array $options = [],
        protected string $delimiter = ',',
    ) {
        $this->exporter = $this->export->getExporter(
            $this->columnMap,
            $this->options,
        );
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return $this->exporter->getJobMiddleware();
    }

    public function handle(): void
    {
        /** @var Authenticatable $user */
        $user = $this->export->user;

        if (method_exists(auth(null)->guard(), 'login')) {
            auth()->login($user);
        } else {
            auth()->setUser($user);
        }

        $exceptions = [];

        $processedRows = 0;
        $successfulRows = 0;

        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setDelimiter($this->delimiter);

        foreach ($this->records as $record) {
            try {
                $csv->insertOne($record);

                $successfulRows++;
            } catch (Throwable $exception) {
                $exceptions[$exception::class] = $exception;
            }

            $processedRows++;
        }

        $filePath = $this->export->getFileDirectory() . DIRECTORY_SEPARATOR . '.csv';
        $this->export->getFileDisk()->put($filePath, $csv->toString(), Filesystem::VISIBILITY_PRIVATE);

        $this->export->refresh();

        $exportProcessedRows = $this->export->processed_rows + $processedRows;
        $this->export->processed_rows = ($exportProcessedRows < $this->export->total_rows) ?
            $exportProcessedRows :
            $this->export->total_rows;

        $exportSuccessfulRows = $this->export->successful_rows + $successfulRows;
        $this->export->successful_rows = ($exportSuccessfulRows < $this->export->total_rows) ?
            $exportSuccessfulRows :
            $this->export->total_rows;

        $this->export->save();

        $this->handleExceptions($exceptions);
    }

    public function retryUntil(): ?CarbonInterface
    {
        return $this->exporter->getJobRetryUntil();
    }

    /**
     * @return array<int, string>
     */
    public function tags(): array
    {
        return $this->exporter->getJobTags();
    }

    /**
     * @param  array<Throwable>  $exceptions
     */
    protected function handleExceptions(array $exceptions): void
    {
        if (empty($exceptions)) {
            return;
        }

        if (count($exceptions) > 1) {
            throw new Exception('Multiple types of exceptions occurred: [' . implode('], [', array_keys($exceptions)) . ']');
        }

        throw Arr::first($exceptions);
    }
}
