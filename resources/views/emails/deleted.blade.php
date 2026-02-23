<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Аккаунт удалён</title>
    @include('emails.partials.styles')
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <div class="email-header">
            <h1 class="email-title">Аккаунт удалён</h1>
        </div>

        <div class="email-body">
            <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>.</p>

            <p>Ваш аккаунт был удалён администратором.</p>

            @if(!empty($deleted_by))
                <p><strong>Удалил(а):</strong> {{ $deleted_by }}</p>
            @endif

            <p>Если вы считаете, что это произошло по ошибке, пожалуйста, обратитесь в службу поддержки.</p>

            <p class="muted">Это автоматическое сообщение.</p>
        </div>

        <div class="footer">
            <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
        </div>
    </div>
</div>
</body>
</html>
