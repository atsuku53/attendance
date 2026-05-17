@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index_request.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_admin')
@endsection

@section('content')
<livewire:switch-approval-admin :attendanceRequests="$attendanceRequests"/>
@endsection