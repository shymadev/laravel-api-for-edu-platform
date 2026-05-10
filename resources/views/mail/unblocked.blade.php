<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аккаунт разблокирован</title>
    @include('emails.partials.styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="email-title">Аккаунт разблокирован</h1>
            </div>

            <div class="email-body">
                <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>.</p>

                <p>Ваш аккаунт был разблокирован. Теперь вы можете снова войти в систему.</p>

                <div class="email-cta">
                    <a href="{{ url('/') }}" class="btn-primary">Перейти на сайт</a>
                </div>

                <p class="muted">Рады видеть вас снова!</p>
            </div>

            <div class="footer">
                <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
            </div>
        </div>
    </div>
</body>
</html>
