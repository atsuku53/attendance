<div class="index__container">
    <div class="index-title__block">
        <h1 class="index-title__content">勤怠一覧</h1>
    </div>
    <div class="index__container-inner">
        <div class="attendance-month__block">
            <div class="attendance-month-previous__block">
                <span class="attendance-month-previous__content" wire:click="goToPreviousMonth">&#8592;&nbsp;前月</span>
                <input type="hidden" wire:model="previousMonth" />
            </div>
            <div class="attendance-month-current__block">
                <div class="attendance-month-current__content">
                    <span class="attendance-month-current__icon">&#128197;</span>
                    <input type="month" class="attendance-month-current__input" wire:model="selectedMonth" />
                    <span class="attendance-month-current__text">{{ $displayMonth }}</span>
                </div>
            </div>
            <div class="attendance-month-next__block">
                <span class="attendance-month-next__content" wire:click="goToNextMonth">翌月&nbsp;&#8594;</span>
                <input type="hidden" wire:model="nextMonth" />
            </div>
        </div>
        <div class="attendance-list__block">
            <table class="attendance-list__table">
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach (($monthlyRows ?? []) as $row)
                    @php
                        $attendance = $row['attendance'];
                        $currentModified = $attendance ? ($latestModified[$attendance->id] ?? null) : null;
                    @endphp
                    <tr>
                        <td>{{ $row['date']->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                        <td>{{ $attendance ? ($currentModified ? ($currentModified->attend_start ? $currentModified->attend_start->format('H:i') : '') : ($attendance->attend_start ? $attendance->attend_start->format('H:i') : '')) : '' }}</td>
                        <td>{{ $attendance ? ($currentModified ? ($currentModified->attend_end ? $currentModified->attend_end->format('H:i') : '') : ($attendance->attend_end ? $attendance->attend_end->format('H:i') : '')) : '' }}</td>
                        <td>{{ $attendance ? ($totalRests[$attendance->id] ?? '') : '' }}</td>
                        <td>{{ $attendance ? ($totalWorks[$attendance->id] ?? '') : '' }}</td>
                        <td class="attendance-detail__block">
                            <a href="/attendance/detail/{{ $attendance ? $attendance->id : 'add/' . $row['date']->locale('ja')->format('Y-m-d') }}" class="attendance-detail__link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
