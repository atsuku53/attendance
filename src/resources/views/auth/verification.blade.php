@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verification.css') }}" />
@endsection

@section('header')
@include('layouts.header_logo_verification')
@endsection

@section('content')
    <div class="verification__container">
        <div class="verification__block">
            <div class="verification__message">
                登録していただいたメールアドレスに認証メールを送付しました。
            </div>
            <div class="verification__message">
                メール認証を完了してください。
            </div>
            <div class="verification__message">
                <a class="verification__message--link" href="http://localhost:8025" target="_blank" rel="noopener noreferrer">
                    <button class="verification__message--button" type="button">認証はこちらから</button>
                </a>
            </div>
            <div class="verification__message">
                <form class="verification__resend--form" method="POST" action="/email/verification-notification">
                    @csrf
                    <button class="verification__resend--button" type="submit">認証メールを再送する</button>
                </form>
            </div>
        </div>
    </div>
@endsection