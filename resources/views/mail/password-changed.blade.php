<!doctype html>
<html lang="ru">
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
            <h1 class="email-title">Уведомление: пароль изменён</h1>
        </div>

        <div class="email-body">
            <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>!</p>

            <p>Это письмо подтверждает, что пароль от вашего аккаунта Tallksy был <strong>успешно изменён</strong>.</p>

            @isset($changedAt)
                <p class="muted" style="margin-top:8px;">Время изменения: {{ $changedAt }}</p>
            @endisset

            <div class="divider"></div>

            <p>Если это были вы, можете спокойно удалить это сообщение — дополнительных действий не требуется.</p>

            @if(!empty($homePageUrl))
                <div class="email-cta" style="display:block;text-align:center;margin:20px 0;">
                    <a href="{{ $homePageUrl }}"
                       style="display:inline-block;background-color:#4A148C;color:#ffffff !important;padding:12px 22px;border-radius:8px;text-decoration:none;font-weight:600;font-family:'Raleway',Arial,Helvetica,sans-serif;box-shadow:0 6px 18px rgba(74,20,140,0.12);">
                        Перейти на сайт
                    </a>
                </div>
            @endif

            <p><strong>Вы не меняли пароль?</strong> Срочно смените его через форму восстановления доступа и обратитесь в службу поддержки — возможно, кто-то получил доступ к аккаунту.</p>

            <p class="muted">Это автоматическое уведомление о безопасности. Пожалуйста, не отвечайте на это письмо.</p>
        </div>

        <div class="footer">
            <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
        </div>
    </div>
</div>
</body>
</html>
