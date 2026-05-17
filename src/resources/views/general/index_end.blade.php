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
            <div class="attendance__status--display">退勤済</div>
        </div>
        <div class="attendance__date--block">
            <livewire:date :currentDate="$currentDate" />
        </div>
        <div class="attendance__time--block">
            <livewire:clock :currentTime="$currentTime" />
        </div>
        <div class="attendance__message--block">
            <div class="attendance__message--display">お疲れ様でした。</div>
        </div>
    </div>
</div>
@endsection