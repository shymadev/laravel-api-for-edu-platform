<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля — Tallksy</title>
    @include('mail.partials.styles')
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <div class="email-header">
            <h1 class="email-title">Сброс пароля</h1>
        </div>

        <div class="email-body">
            <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>!</p>

            <p>Вы запросили ссылку для сброса пароля. Нажмите кнопку ниже, чтобы задать новый пароль.</p>

            @if(!empty($resetUrl))
                <div class="email-cta" style="display:block;text-align:center;margin:20px 0;">
                    <a href="{{ $resetUrl }}"
                       style="display:inline-block;background-color:#4A148C;color:#ffffff !important;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-family:'Raleway',Arial,Helvetica,sans-serif;box-shadow:0 6px 18px rgba(74,20,140,0.12);">
                        Сбросить пароль
                    </a>
                </div>
            @endif

            <p class="muted">Если вы не запрашивали сброс пароля, проигнорируйте это письмо.</p>
        </div>

        <div class="footer">
            <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
        </div>
    </div>
</div>
</body>
</html>
