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
    <div class="index__container-inner">
        <div class="attendance-detail__block">
            <table class="attendance-detail__table">
                <tr>
                    <th class="table__title">名前</th>
                    <td colspan="3">{{ $modifiedAttendance->attendance->user->name }}</td>
                </tr>
                <tr>
                    <th class="table__title">日付</th>
                    <td class="table__content--first">{{ $modifiedAttendance->attend_start->locale('ja')->isoFormat('YYYY年') }}</td>
                    <td class="table__content--second"></td>
                    <td class="table__content--third">{{ $modifiedAttendance->attend_start->locale('ja')->isoFormat('M月D日') }}</td>
                </tr>
                <tr>
                    <th class="table__title">出勤・退勤</th>
                    <td class="table__content--first">
                        {{ $modifiedAttendance->attend_start->format('H:i') }}
                    </td>
                    <td class="table__content--second">～</td>
                    <td class="table__content--third">
                        {{ $modifiedAttendance->attend_end->format('H:i') }}
                    </td>
                </tr>
                @foreach ($modifiedAttendance->modifiedRests ? $modifiedAttendance->modifiedRests : $attendance->rests as $rest)
                    <tr>
                        <th class="table__title">休憩{{ $loop->iteration }}</th>
                        <td class="table__content--first">
                            {{ $rest->rest_start ? $rest->rest_start->format('H:i') : '' }}
                        </td>
                        <td class="table__content--second">～</td>
                        <td class="table__content--third">
                            {{ $rest->rest_end ? $rest->rest_end->format('H:i') : '' }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th class="table__title">備考</th>
                    <td colspan="3">
                        {{ $modifiedAttendance->comment }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="attendance-detail-link__block">
        @if ($modifiedAttendance->administrator_id === null)
            <a href="/admin/stamp_correction_request/approve/{{ $modifiedAttendance->id }}" class="attendance-detail-link__content">承認</a>
        @else
            <div class="attendance-detail-button__content--done">承認済み</div>
        @endif
    </div>
</div>
@endsection