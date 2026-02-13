<?php

namespace App\Livewire\MoodCheckin;

use App\Models\MoodCheckin;
use App\Services\CoupleService;
use App\Services\XpService;
use Livewire\Component;

class Create extends Component
{
    public $existingCheckin;

    public $moodLevel = 3;

    public $reasonTags = [];

    public $needs = [];

    public $note = '';

    public $availableReasons = [
        'work' => 'Work',
        'health' => 'Health',
        'family' => 'Family',
        'relationship' => 'Relationship',
        'random' => 'Just Random',
    ];

    public $availableNeeds = [
        'space' => 'Some Space',
        'talk' => 'To Talk',
        'reassurance' => 'Reassurance',
        'help' => 'Help',
        'affection' => 'Affection',
    ];

    protected $rules = [
        'moodLevel' => 'required|integer|min:1|max:5',
        'reasonTags' => 'array',
        'needs' => 'array',
        'note' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $couple = app(CoupleService::class)->getUserCouple(auth()->user());

        if (! $couple) {
            return;
        }

        $this->existingCheckin = MoodCheckin::where('couple_id', $couple->id)
            ->where('user_id', auth()->id())
            ->where('date', today()->toDateString())
            ->first();

        if (! $this->existingCheckin) {
            return;
        }

        $this->moodLevel = $this->existingCheckin->mood_level;
        $this->reasonTags = $this->existingCheckin->reason_tags ?? [];
        $this->needs = $this->existingCheckin->needs ?? [];
        $this->note = $this->existingCheckin->note ?? '';
    }

    public function submit()
    {
        $this->validate();

        $coupleService = app(CoupleService::class);
        $xpService = app(XpService::class);

        $couple = $coupleService->getUserCouple(auth()->user());

        if (! $couple) {
            session()->flash('error', 'You need to be in a couple to check in.');

            return redirect()->route('dashboard');
        }

        // Update today's check-in if it exists, otherwise create a new one.
        $checkin = MoodCheckin::updateOrCreate([
            'couple_id' => $couple->id,
            'user_id' => auth()->id(),
            'date' => today(),
        ], [
            'mood_level' => $this->moodLevel,
            'reason_tags' => $this->reasonTags,
            'needs' => $this->needs,
            'note' => $this->note,
        ]);

        if ($checkin->wasRecentlyCreated) {
            // Award XP only on the first check-in of the day.
            $xpService->awardXp(
                $couple,
                'checkin',
                auth()->user(),
                null,
                ['mood_level' => $this->moodLevel]
            );
        }

        // Create mood alert notification if mood is low
        if ($this->moodLevel <= 2) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->createMoodAlert(
                $couple,
                auth()->user(),
                $this->moodLevel,
                $this->needs
            );
        }

        $message = $checkin->wasRecentlyCreated
            ? 'Check-in complete! +10 XP'
            : 'Check-in updated for today.';
        session()->flash('message', $message);

        return redirect()->route('dashboard');
    }

    public function toggleReason($reason)
    {
        if (in_array($reason, $this->reasonTags)) {
            $this->reasonTags = array_diff($this->reasonTags, [$reason]);
        } else {
            $this->reasonTags[] = $reason;
        }
    }

    public function toggleNeed($need)
    {
        if (in_array($need, $this->needs)) {
            $this->needs = array_diff($this->needs, [$need]);
        } else {
            $this->needs[] = $need;
        }
    }

    public function render()
    {
        return view('livewire.mood-checkin.create')->layout('layouts.app');
    }
}
