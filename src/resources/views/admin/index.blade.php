@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/index_list.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_admin')
@endsection

@section('content')
<livewire:select-day
    :displayDay="$displayDay"
    :attendances="$attendances"
    :totalRests="$totalRests"
    :totalWorks="$totalWorks"
    :previousDay="$previousDay"
    :nextDay="$nextDay"
    :latestModified="$latestModified" />
@endsection