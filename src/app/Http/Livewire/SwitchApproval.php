<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SwitchApproval extends Component
{
    public $activeTab = 'tab1';
    public $attendanceRequests;

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.switch-approval');
    }
}
