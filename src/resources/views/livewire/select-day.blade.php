<div class="index__container">
    <div class="index-title__block">
        <h1 class="index-title__content">勤怠</h1>
    </div>
    <div class="index__container-inner">
        <div class="attendance-day__block">
            <div class="attendance-day-previous__block">
                <span class="attendance-day-previous__content" wire:click="goToPreviousDay">&#8592;&nbsp;前日</span>
                <input type="hidden" wire:model="previousDay" />
            </div>
            <div class="attendance-day-current__block">
                <div class="attendance-day-current__content">
                    <span class="attendance-day-current__icon">&#128197;</span>
                    <input type="date" class="attendance-day-current__input" wire:model="selectedDay" />
                    <span class="attendance-day-current__text">{{ $displayDay }}</span>
                </div>
            </div>
            <div class="attendance-day-next__block">
                <span class="attendance-day-next__content" wire:click="goToNextDay">翌日&nbsp;&#8594;</span>
                <input type="hidden" wire:model="nextDay" />
            </div>
        </div>
        <div class="attendance-list__block">
            <table class="attendance-list__table">
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach (($attendances ?? []) as $attendance)
                    @php $currentModified = $latestModified[$attendance->id] ?? null; @endphp
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $currentModified ? $currentModified->attend_start->format('H:i') : ($attendance->attend_start ? $attendance->attend_start->format('H:i') : '') }}</td>
                        <td>{{ $currentModified ? $currentModified->attend_end->format('H:i') : ($attendance->attend_end ? $attendance->attend_end->format('H:i') : '') }}</td>
                        <td>{{ $totalRests[$attendance->id] ?? '' }}</td>
                        <td>{{ $totalWorks[$attendance->id] ?? '' }}</td>
                        <td class="attendance-detail__block">
                            <a href="/admin/attendance/detail/{{ $attendance->id }}" class="attendance-detail__link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
