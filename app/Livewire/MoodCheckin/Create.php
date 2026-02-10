<?php

namespace App\Livewire\MoodCheckin;

use App\Models\MoodCheckin;
use App\Services\CoupleService;
use App\Services\XpService;
use Livewire\Component;

class Create extends Component
{
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
        // Check if user already checked in today
        $couple = app(CoupleService::class)->getUserCouple(auth()->user());

        if ($couple) {
            $existing = MoodCheckin::where('couple_id', $couple->id)
                ->where('user_id', auth()->id())
                ->whereDate('date', today())
                ->first();

            if ($existing) {
                session()->flash('message', 'You\'ve already checked in today!');
                return redirect()->route('dashboard');
            }
        }
    }

    public function submit()
    {
        $this->validate();

        $coupleService = app(CoupleService::class);
        $xpService = app(XpService::class);

        $couple = $coupleService->getUserCouple(auth()->user());

        if (!$couple) {
            session()->flash('error', 'You need to be in a couple to check in.');
            return redirect()->route('dashboard');
        }

        // Create mood check-in
        MoodCheckin::create([
            'couple_id' => $couple->id,
            'user_id' => auth()->id(),
            'date' => today(),
            'mood_level' => $this->moodLevel,
            'reason_tags' => $this->reasonTags,
            'needs' => $this->needs,
            'note' => $this->note,
        ]);

        // Award XP
        $xpService->awardXp(
            $couple,
            'checkin',
            auth()->user(),
            null,
            ['mood_level' => $this->moodLevel]
        );

        session()->flash('message', 'Check-in complete! +10 XP');
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
        return view('livewire.mood-checkin.create');
    }
}
