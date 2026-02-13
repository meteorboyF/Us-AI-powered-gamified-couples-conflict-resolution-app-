<?php

namespace App\Livewire\Gifts;

use Livewire\Component;

class Index extends Component
{
    public string $tab = 'wishlist';

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['wishlist', 'suggestions'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function render()
    {
        return view('livewire.gifts.index')->layout('layouts.app');
    }
}
