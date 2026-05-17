@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/general/index_list.css') }}" />
@endsection

@section('header')
@include('layouts.header_auth_user')
@endsection

@section('content')
<livewire:select-month
    :displayMonth="$displayMonth"
    :attendances="$attendances"
    :totalRests="$totalRests"
    :totalWorks="$totalWorks"
    :previousMonth="$previousMonth"
    :nextMonth="$nextMonth"
    :latestModified="$latestModified" />
@endsection