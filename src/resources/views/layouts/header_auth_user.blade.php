<div class="header__inner">
    <a class="header__logo" href="{{ route('index') }}">
        <img class="header__logo-img" src="{{ asset('images/header_logo.png') }}" alt="Attendance" />
    </a>
</div>
<nav class="header__nav">
    <div class="header__block">
        <a class="header__link" href="{{ route('index') }}">勤怠</a>
    </div>
    <div class="header__block">
        <a class="header__link" href="/attendance/list">勤怠一覧</a>
    </div>
    <div class="header__block">
        <a class="header__link" href="/stamp_correction_request/list">申請</a>
    </div>
    <form class="header__block" method="POST" action="/logout">
    @csrf
        <button class="header__logout--button">ログアウト</button>
    </form>
</nav>