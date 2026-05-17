<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Date extends Component
{
    public $currentDate;

    public function mount()
    {
        $this->updateDate();
    }

    public function updateDate()
    {
        $this->currentDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
    }

    public function render()
    {
        return view('livewire.date');
    }
}
