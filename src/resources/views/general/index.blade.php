@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_user')
@endsection

@section('content')
<div class="index__container">
    <div class="attendance__block">
        <div class="attendance__status--block">
            <div class="attendance__status--display">勤務外</div>
        </div>
        <div class="attendance__date--block">
            <livewire:date :currentDate="$currentDate" />
        </div>
        <div class="attendance__time--block">
            <livewire:clock :currentTime="$currentTime" />
        </div>
        <div class="attendance__button--block">
            <a href="{{ route('attend_start') }}" class="attendance__button--display">出勤</a>
        </div>
    </div>
</div>
@endsection