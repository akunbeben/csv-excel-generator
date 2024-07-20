<div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="col-span-full sm:col-span-2">
            {{ $this->main }}
        </div>

        <div>
            <x-filament::section>
                <x-slot name="heading">
                    Output Settings
                </x-slot>
            
                <div class="grid gap-4">
                    {{ $this->output }}

                    {{ $this->submit }}
            
                    @livewire('notification')
                </div>
            </x-filament::section>
        </div>
    </div>
    <x-filament-actions::modals />
</div>
