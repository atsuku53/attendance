@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_admin')
@endsection

@section('content')
<div class="index__container">
    <div class="index-title__block">
        <h1 class="index-title__content">スタッフ一覧</h1>
    </div>
    <div class="index__container-inner">
        <div class="staff-list__block">
            <table class="staff-list__table">
                <tr>
                    <th class="table__column">名前</th>
                    <th class="table__column">メールアドレス</th>
                    <th class="table__column">月次勤怠</th>
                </tr>
                @foreach ($staffs as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                    <td class="staff-detail__block">
                        <a href="/admin/attendance/staff/{{ $staff->id }}" class="staff-detail__link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection