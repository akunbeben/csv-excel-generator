<?php

namespace App\Livewire;

use App\Extends\ExportAction;
use App\Filament\Exports\DynamicExporter;
use App\Models\User;
use App\Supports\FakerFiller;
use App\Supports\Locale;
use App\Traits\Fingerprint;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Livewire\Component;

class Create extends Component implements HasActions, HasForms
{
    use Fingerprint;
    use InteractsWithActions;
    use InteractsWithForms;

    public ?array $mainData = [];

    public ?array $outputData = [];

    private ?Collection $columns = null;

    public ?array $records = [];

    public function mount(Request $request): void
    {
        if (! $request->filled('key')) {
            redirect()->route('create', ['key' => $this->fingerprint()]);

            return;
        }

        $user = User::query()->firstOrCreate([
            'key' => $request->query('key'),
        ]);

        auth()->login($user);

        $this->main->fill();
        $this->output->fill();
    }

    protected function getForms(): array
    {
        return [
            'main',
            'output',
        ];
    }

    public function output(Forms\Form $form): Forms\Form
    {
        return $form
            ->extraAttributes(['class' => 'gap-4'])
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label('Locale')
                    ->default('en_US')
                    ->options(Locale::options())
                    ->in(array_keys(Locale::options()))
                    ->required(),
                Forms\Components\TextInput::make('rows')
                    ->label('Rows')
                    ->hintIcon('heroicon-o-information-circle', 'How many rows do you want to generate?')
                    ->default(10)
                    ->minValue(1)
                    ->required(),
                Forms\Components\Select::make('output')
                    ->live()
                    ->label('Output')
                    ->default('csv')
                    ->options([
                        'csv' => 'CSV',
                        'xlsx' => 'Excel (XLSX)',
                    ])
                    ->in(['csv', 'xlsx'])
                    ->required(),
                Forms\Components\Select::make('delimiter')
                    ->hidden(fn (Forms\Get $get) => $get('output') !== 'csv')
                    ->label('Delimiter')
                    ->default(',')
                    ->options([
                        ',' => 'Comma (,)',
                        ';' => 'Semicolon (;)',
                    ])
                    ->required(),
            ])
            ->statePath('outputData');
    }

    public function main(Forms\Form $form): Forms\Form
    {
        return $form
            ->extraAttributes(['data-identifier' => 'overflow'])
            ->schema([
                Forms\Components\Section::make('Columns')
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Repeater::make('columns')
                            ->hiddenLabel()
                            ->addActionLabel('Add more columns')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('label')
                                        ->label('Label')
                                        ->required(),
                                    Forms\Components\Select::make('type')->label('Data type')
                                        ->options(FakerFiller::options())
                                        ->required(),
                                ]),
                            ]),
                    ]),
            ])
            ->statePath('mainData');
    }

    public function submit(): Action
    {
        return Action::make('submit')
            ->before(function () {
                $this->main->validate();
                $this->output->validate();
            })
            ->color('primary')
            ->icon('heroicon-o-sparkles')
            ->label('Generate')
            ->livewireTarget('export')
            ->action(function () {
                foreach (range(1, $this->outputData['rows'] ?? 10) as $index) {
                    foreach ($this->mainData['columns'] as $column) {
                        $record[$column['label']] = FakerFiller::from($column['type'])->fill($this->outputData['locale'] ?? 'en_US');
                    }

                    $this->records[] = $record;
                }

                $this->columns = collect();

                foreach ($this->records as $record) {
                    $columns = array_keys($record);

                    foreach ($columns as $column) {
                        $this->columns->push(ExportColumn::make($column));
                    }
                }

                $this->columns = $this->columns->unique();

                $this->export()->call();
                $this->records = [];
            });
    }

    public function export(): Action
    {
        return ExportAction::make('export')
            ->columns(fn () => $this->columns)
            ->records(fn () => collect($this->records))
            ->delimiter($this->outputData['delimiter'] ?? ',')
            ->livewire($this)
            ->exporter(DynamicExporter::class)
            ->formats([
                ExportFormat::tryFrom($this->outputData['output']),
            ]);
    }

    public function render()
    {
        return view('livewire.create');
    }
}
