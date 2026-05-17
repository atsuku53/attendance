@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index_list.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_admin')
@endsection

@section('content')
<livewire:select-month-admin
    :displayMonth="$displayMonth"
    :attendances="$attendances"
    :totalRests="$totalRests"
    :totalWorks="$totalWorks"
    :previousMonth="$previousMonth"
    :nextMonth="$nextMonth"
    :latestModified="$latestModified"
    :staff="$staff" />
@endsection