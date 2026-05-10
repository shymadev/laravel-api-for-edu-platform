<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пароль изменён — Tallksy</title>
    @include('mail.partials.styles')
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <div class="email-header">
            <h1 class="email-title">Пароль изменён</h1>
        </div>

        <div class="email-body">
            <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>!</p>

            <p>Пароль от вашего аккаунта Tallksy был успешно изменён.</p>

            <p>Если это были вы, дополнительных действий не требуется.</p>

            @if(!empty($homePageUrl))
                <div class="email-cta" style="display:block;text-align:center;margin:20px 0;">
                    <a href="{{ $homePageUrl }}"
                       style="display:inline-block;background-color:#4A148C;color:#ffffff !important;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-family:'Raleway',Arial,Helvetica,sans-serif;box-shadow:0 6px 18px rgba(74,20,140,0.12);">
                        На главную страницу
                    </a>
                </div>
            @endif

            <p><strong>Не вы меняли пароль?</strong> Срочно свяжитесь со службой поддержки — возможно, кто-то получил доступ к вашему аккаунту.</p>

            <p class="muted">Это автоматическое сообщение. Пожалуйста, не отвечайте на него.</p>
        </div>

        <div class="footer">
            <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
        </div>
    </div>
</div>
</body>
</html>
