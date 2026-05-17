<div class="header__inner">
    <a class="header__logo" href="{{ route('index_admin') }}">
        <img class="header__logo-img" src="{{ asset('images/header_logo.png') }}" alt="Attendance" />
    </a>
</div>
<nav class="header__nav">
    <div class="header__block">
        <a class="header__link" href="{{ route('index_admin') }}">勤怠一覧</a>
    </div>
    <div class="header__block">
        <a class="header__link" href="/admin/staff/list">スタッフ一覧</a>
    </div>
    <div class="header__block">
        <a class="header__link" href="/admin/stamp_correction_request/list">申請一覧</a>
    </div>
    <form class="header__block" method="POST" action="{{ route('logout_admin') }}">
    @csrf
        <button class="header__logout--button">ログアウト</button>
    </form>
</nav>