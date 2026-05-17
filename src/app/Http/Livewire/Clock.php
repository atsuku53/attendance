<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Clock extends Component
{
    public $currentTime;

    public function mount()
    {
        $this->updateTime();
    }

    public function updateTime()
    {
        $this->currentTime = Carbon::now()->locale('ja')->isoFormat('HH:mm');
    }

public function render()
    {
        return view('livewire.clock');
    }
}
