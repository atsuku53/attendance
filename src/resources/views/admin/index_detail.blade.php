@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index_detail.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_admin')
@endsection

@section('content')
<div class="index__container">
    <div class="index-title__block">
        <h1 class="index-title__content">勤怠詳細</h1>
    </div>
    <form class="index__form" action="/admin/attendance/detail/{{ $attendance->id }}" method="POST">
        @csrf
        <div class="index__container-inner">
            <div class="attendance-detail__block">
                <table class="attendance-detail__table">
                    <tr>
                        <th class="table__title">名前</th>
                        <td colspan="3">{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th class="table__title">日付</th>
                        <td class="table__content--first">{{ $attendance->attend_start->locale('ja')->isoFormat('YYYY年') }}</td>
                        <td class="table__content--second"></td>
                        <td class="table__content--third">{{ $attendance->attend_start->locale('ja')->isoFormat('M月D日') }}</td>
                    </tr>
                    <tr>
                        <th class="table__title">
                            出勤・退勤
                            @error('attend_start')
                                <div class="error__message">※{{ $message }}</div>
                            @enderror
                            @error('attend_end')
                                <div class="error__message">※{{ $message }}</div>
                            @enderror
                        </th>
                        <td class="table__content--first">
                            <input class="table__input" name="attend_start" value="{{ old('attend_start', $modifiedAttendance ? $modifiedAttendance->attend_start->format('H:i') : $attendance->attend_start->format('H:i')) }}">
                        </td>
                        <td class="table__content--second">～</td>
                        <td class="table__content--third">
                            <input class="table__input" name="attend_end" value="{{ old('attend_end', $modifiedAttendance ? $modifiedAttendance->attend_end->format('H:i') : $attendance->attend_end->format('H:i')) }}">
                        </td>
                    </tr>
                    @foreach ($modifiedAttendance && $modifiedAttendance->modifiedRests ? $modifiedAttendance->modifiedRests : $attendance->rests as $rest)
                        <tr>
                            <th class="table__title">
                                休憩{{ $loop->iteration }}
                                @error('rests.' . $loop->index . '.rest_start')
                                    <div class="error__message">※{{ $message }}</div>
                                @enderror
                                @error('rests.' . $loop->index . '.rest_end')
                                    <div class="error__message">※{{ $message }}</div>
                                @enderror
                            </th>
                            <td class="table__content--first">
                                <input class="table__input" name="rests[{{ $loop->index }}][rest_start]" value="{{ old('rests.' . $loop->index . '.rest_start', $rest->rest_start ? $rest->rest_start->format('H:i') : '') }}">
                            </td>
                            <td class="table__content--second">～</td>
                            <td class="table__content--third">
                                <input class="table__input" name="rests[{{ $loop->index }}][rest_end]" value="{{ old('rests.' . $loop->index . '.rest_end', $rest->rest_end ? $rest->rest_end->format('H:i') : '') }}">
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="table__title">
                            休憩{{ $modifiedAttendance ? $modifiedAttendance->modifiedRests->count() + 1 : $attendance->rests->count() + 1 }}
                            @error('rests.' . ($modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count()) . '.rest_start')
                                <div class="error__message">※{{ $message }}</div>
                            @enderror
                            @error('rests.' . ($modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count()) . '.rest_end')
                                <div class="error__message">※{{ $message }}</div>
                            @enderror
                        </th>
                        <td class="table__content--first">
                            <input class="table__input" name="rests[{{ $modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count() }}][rest_start]" value="{{ old('rests.' . ($modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count()) . '.rest_start') }}">
                        </td>
                        <td class="table__content--second">～</td>
                        <td class="table__content--third">
                            <input class="table__input" name="rests[{{ $modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count() }}][rest_end]" value="{{ old('rests.' . ($modifiedAttendance ? $modifiedAttendance->modifiedRests->count() : $attendance->rests->count()) . '.rest_end') }}">
                        </td>
                    </tr>
                    <tr>
                        <th class="table__title">
                            備考
                            @error('comment')
                                <div class="error__message">※{{ $message }}</div>
                            @enderror
                        </th>
                        <td colspan="3">
                            <textarea class="table-comment__input" name="comment">{{ old('comment', $modifiedAttendance ? $modifiedAttendance->comment : '') }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="attendance-detail-button__block">
            <button type="submit" class="attendance-detail-button__content">修正</button>
        </div>
    </form>
</div>
@endsection