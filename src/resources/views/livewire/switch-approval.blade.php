<div class="index__container">
    <div class="index-title__block">
        <h1 class="index-title__content">申請一覧</h1>
    </div>
    <div class="index__tab">
        <button class="index__tab--button {{ $activeTab === 'tab1' ? 'active' : '' }}" wire:click="switchTab('tab1')">承認待ち</button>
        <button class="index__tab--button {{ $activeTab === 'tab2' ? 'active' : '' }}" wire:click="switchTab('tab2')">承認済み</button>
    </div>
    <div class="index__container-inner">
        @if($activeTab === 'tab1')
            <table class="approval__table">
                <tr>
                    <th class="table__column">状態</th>
                    <th class="table__column">名前</th>
                    <th class="table__column">対象日時</th>
                    <th class="table__column">申請理由</th>
                    <th class="table__column">申請日時</th>
                    <th class="table__column">詳細</th>
                </tr>
                @foreach ($attendanceRequests->where('administrator_id', null) as $request)
                    <tr>
                        <td>承認待ち</td>
                        <td>{{ $request->attendance->user->name }}</td>
                        <td>{{ $request->attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD') }}</td>
                        <td>{{ $request->comment }}</td>
                        <td>{{ $request->created_at->locale('ja')->isoFormat('YYYY/MM/DD') }}</td>
                        <td class="approval-detail__block">
                            <a href="/attendance/detail/{{ $request->attendance_id }}" class="approval-detail__link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        @elseif($activeTab === 'tab2')
            <table class="approval__table">
                <tr>
                    <th class="table__column">状態</th>
                    <th class="table__column">名前</th>
                    <th class="table__column">対象日時</th>
                    <th class="table__column">申請理由</th>
                    <th class="table__column">申請日時</th>
                    <th class="table__column">詳細</th>
                </tr>
                @foreach ($attendanceRequests->where('administrator_id', '!=', null) as $request)
                    <tr>
                        <td>承認済み</td>
                        <td>{{ $request->attendance->user->name }}</td>
                        <td>{{ $request->attendance->attend_start->locale('ja')->isoFormat('YYYY/MM/DD') }}</td>
                        <td>{{ $request->comment }}</td>
                        <td>{{ $request->created_at->locale('ja')->isoFormat('YYYY/MM/DD') }}</td>
                        <td class="approval-detail__block">
                            <a href="/attendance/detail/{{ $request->attendance_id }}" class="approval-detail__link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>
</div>
