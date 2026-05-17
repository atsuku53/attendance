@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
@endsection

@section('header')
@include('layouts.header_logo_user')
@endsection

@section('content')
<div class = "register__container">
    <h1 class="register__title">会員登録</h1>
    <div class="register__content">
        <form class="register__form" action="/register" method="POST" novalidate>
            @csrf
                <div class="register__form--block">
                    <label class="register__label">ユーザー名</label>
                    <input class="register__input" type="text" name="name" value="{{ old('name') }}">
                    <div class="register__error">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="register__form--block">
                    <label class="register__label">メールアドレス</label>
                    <input class="register__input" type="email" name="email" value="{{ old('email') }}">
                    <div class="register__error">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="register__form--block">
                    <label class="register__label">パスワード</label>
                    <input class="register__input" type="password" name="password" value="{{ old('password') }}">
                </div>
                <div class="register__form--block">
                    <label class="register__label">確認用パスワード</label>
                    <input class="register__input" type="password" name="password_confirmation" value="{{ old('password_confirmation') }}">
                    <div class="register__error">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="register__button">
                    <button type="submit" class="register__button--submit">登録する</button>
                </div>
                <div class="register__login">
                    <a href="/login" class="register__login--link">ログインはこちら</a>
                </div>
        </form>
    </div>
</div>
@endsection