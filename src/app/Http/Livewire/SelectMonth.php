<?php

namespace App\Http\Livewire;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;

class SelectMonth extends Component
{
    public $displayMonth;
    public $selectedMonth;
    public $attendances = [];
    public $totalRests = [];
    public $totalWorks = [];
    public $previousMonth;
    public $nextMonth;
    public $latestModified = [];
    public $monthlyRows = [];

    public function mount($displayMonth)
    {
        $this->displayMonth = $displayMonth;
        $this->selectedMonth = Carbon::createFromFormat('Y/m/d', $displayMonth . '/1')->format('Y-m');
        $this->loadMonthlyData(Carbon::createFromFormat('Y/m/d', $displayMonth . '/1'));
    }

    public function updatedSelectedMonth($value)
    {
        if (empty($value)) {
            return;
        }

        $selectedDate = Carbon::createFromFormat('Y-m-d', $value . '-1');
        $this->displayMonth = $selectedDate->format('Y/m');
        $this->loadMonthlyData($selectedDate);
    }

    public function goToPreviousMonth()
    {
        $this->updatedSelectedMonth($this->previousMonth);
        $this->selectedMonth = $this->previousMonth;
    }

    public function goToNextMonth()
    {
        $this->updatedSelectedMonth($this->nextMonth);
        $this->selectedMonth = $this->nextMonth;
    }

    private function loadMonthlyData(Carbon $targetMonth)
    {
        session()->put('attendance_list_month', $targetMonth->format('Y/m'));

        $this->previousMonth = $targetMonth->copy()->subMonth()->format('Y-m');
        $this->nextMonth = $targetMonth->copy()->addMonth()->format('Y-m');

        $this->attendances = Attendance::with('rests')->with('modifiedAttendances.modifiedRests')
            ->where('user_id', auth()->id())
            ->whereYear('attend_start', $targetMonth->year)
            ->whereMonth('attend_start', $targetMonth->month)
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

        $attendanceByDate = $this->attendances->keyBy(function ($attendance) {
            return Carbon::parse($attendance->attend_start)->format('Y-m-d');
        });

        $this->monthlyRows = [];
        $currentDate = $targetMonth->copy()->startOfMonth();
        $endDate = $targetMonth->copy()->endOfMonth();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');

            $this->monthlyRows[] = [
                'date' => $currentDate->copy(),
                'attendance' => $attendanceByDate->get($dateKey),
            ];

            $currentDate->addDay();
        }
    }

    public function render()
    {
        return view('livewire.select-month');
    }
}
