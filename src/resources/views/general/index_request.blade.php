@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index_request.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_user')
@endsection

@section('content')
<livewire:switch-approval :attendanceRequests="$attendanceRequests"/>
@endsection