<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Attendance;
use Carbon\Carbon;

class SelectDay extends Component
{
    public $displayDay;
    public $selectedDay;
    public $attendances = [];
    public $totalRests = [];
    public $totalWorks = [];
    public $previousDay;
    public $nextDay;
    public $latestModified = [];

    public function mount($displayDay = null)
    {
        $this->displayDay = $displayDay;
        $this->selectedDay = Carbon::createFromFormat('Y/m/d', $displayDay)->format('Y-m-d');
        $this->loadDailyData(Carbon::createFromFormat('Y/m/d', $displayDay));
    }

    public function updatedSelectedDay($value)
    {
        if (empty($value)) {
            return;
        }

        $selectedDate = Carbon::createFromFormat('Y-m-d', $value);
        $this->displayDay = $selectedDate->format('Y/m/d');
        $this->loadDailyData($selectedDate);
    }

    public function goToPreviousDay()
    {
        $this->updatedSelectedDay($this->previousDay);
        $this->selectedDay = $this->previousDay;
    }

    public function goToNextDay()
    {
        $this->updatedSelectedDay($this->nextDay);
        $this->selectedDay = $this->nextDay;
    }

    private function loadDailyData(Carbon $targetDay)
    {
        session()->put('attendance_list_day', $targetDay->format('Y/m/d'));

        $this->previousDay = $targetDay->copy()->subDay()->format('Y-m-d');
        $this->nextDay = $targetDay->copy()->addDay()->format('Y-m-d');

        $this->attendances = Attendance::with('user')->with('rests')->with('modifiedAttendances.modifiedRests')
            ->whereYear('attend_start', $targetDay->year)
            ->whereMonth('attend_start', $targetDay->month)
            ->whereDay('attend_start', $targetDay->day)
            ->orderBy('attend_start', 'asc')
            ->get();

        $this->totalRests = [];
        foreach ($this->attendances as $attendance) {
            $totalRestMinutes = 0;
            $latestModified = $attendance->modifiedAttendances->last();
            $this->latestModified[$attendance->id] = $latestModified;
            foreach ($latestModified && $latestModified->modifiedRests->isNotEmpty() ? $latestModified->modifiedRests : $attendance->rests as $rest) {
                if ($rest->rest_end) {
                    $totalRestMinutes += Carbon::parse($rest->rest_end)->diffInMinutes(Carbon::parse($rest->rest_start));
                }
            }
            $this->totalRests[$attendance->id] = sprintf('%d:%02d', intdiv($totalRestMinutes, 60), $totalRestMinutes % 60);
        }

        $this->totalWorks = [];
        foreach ($this->attendances as $attendance) {
            $totalWorkMinutes = 0;
            $latestModified = $attendance->modifiedAttendances->last();
            $this->latestModified[$attendance->id] = $latestModified;
            $attendStart = $latestModified ? Carbon::parse($latestModified->attend_start) : Carbon::parse($attendance->attend_start);
            $attendEnd = $latestModified ? Carbon::parse($latestModified->attend_end) : ($attendance->attend_end ? Carbon::parse($attendance->attend_end) : '');
            if ($attendEnd) {
                $totalWorkMinutes = $attendEnd->diffInMinutes($attendStart);
                $restMinutes = 0;
                foreach ($latestModified && $latestModified->modifiedRests->isNotEmpty() ? $latestModified->modifiedRests : $attendance->rests as $rest) {
                    if ($rest->rest_end) {
                        $restMinutes += Carbon::parse($rest->rest_end)->diffInMinutes(Carbon::parse($rest->rest_start));
                    }
                }
                $totalWorkMinutes -= $restMinutes;
            }
            $this->totalWorks[$attendance->id] = sprintf('%d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);
        }
    }

    public function render()
    {
        return view('livewire.select-day');
    }
}
