@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}" />
@endsection

@section('header')
@include('layouts.header_logo_user')
@endsection

@section('content')
<div class = "login__container">
    <h1 class="login__title">管理者ログイン</h1>
    <div class="login__content">
        <form class="login__form" action="/admin/login" method="POST" novalidate>
            @csrf
                <div class="login__form--block">
                    <label class="login__label">メールアドレス</label>
                    <input class="login__input" type="email" name="email" value="{{ old('email') }}">
                    <div class="login__error">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="login__form--block">
                    <label class="login__label">パスワード</label>
                    <input class="login__input" type="password" name="password" value="{{ old('password') }}">
                    <div class="login__error">
                            @error('password')
                                {{ $message }}
                            @enderror
                    </div>
                </div>
                <div class="login__button">
                    <button type="submit" class="login__button--submit">管理者ログインする</button>
                </div>
        </form>
    </div>
</div>
@endsection