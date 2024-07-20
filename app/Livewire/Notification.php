<?php

namespace App\Livewire;

use Filament\Notifications\Livewire\DatabaseNotifications as Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Notification extends Component
{
    public static ?string $pollingInterval = '10s';

    public function render(): View
    {
        return view('livewire.notification');
    }

    public function getTrigger(): ?View
    {
        return view('components.notification-trigger');
    }

    public function getNotificationsQuery(): Builder | Relation
    {
        /** @phpstan-ignore-next-line */
        return $this->getUser()->notifications()->where('data->format', 'filament');
    }
}
