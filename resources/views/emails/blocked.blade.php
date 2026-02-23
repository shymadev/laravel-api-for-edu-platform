<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аккаунт заблокирован</title>
    @include('emails.partials.styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="email-title">Аккаунт заблокирован</h1>
            </div>

            <div class="email-body">
                <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>.</p>

                <p>Ваш аккаунт был заблокирован администрацией.</p>

                @if(!empty($reason))
                    <p><strong>Причина:</strong> {{ $reason }}</p>
                @endif

                <p>Если вы считаете, что произошла ошибка, пожалуйста, свяжитесь со службой поддержки.</p>

                <p class="muted">Это автоматическое сообщение.</p>
            </div>

            <div class="footer">
                <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
            </div>
        </div>
    </div>
</body>
</html>
