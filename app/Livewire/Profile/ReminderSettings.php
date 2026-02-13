<?php

namespace App\Livewire\Profile;

use Livewire\Component;

class ReminderSettings extends Component
{
    public bool $dailyCheckin = true;

    public bool $mission = true;

    public bool $anniversary = true;

    public function mount(): void
    {
        $user = auth()->user();

        $this->dailyCheckin = (bool) $user->reminder_daily_checkin_enabled;
        $this->mission = (bool) $user->reminder_mission_enabled;
        $this->anniversary = (bool) $user->reminder_anniversary_enabled;
    }

    public function save(): void
    {
        auth()->user()->update([
            'reminder_daily_checkin_enabled' => $this->dailyCheckin,
            'reminder_mission_enabled' => $this->mission,
            'reminder_anniversary_enabled' => $this->anniversary,
        ]);

        session()->flash('message', 'Reminder settings updated.');
    }

    public function render()
    {
        return view('livewire.profile.reminder-settings');
    }
}
