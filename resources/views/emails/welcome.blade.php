<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добро пожаловать в Tallksy!</title>
    @include('emails.partials.styles')
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1 class="email-title">Добро пожаловать в Tallksy</h1>
            </div>

            <div class="email-body">
                <p>Здравствуйте, <strong>{{ $user->username ?? 'пользователь' }}</strong>!</p>

                <p>Спасибо за регистрацию в Tallksy. Мы рады видеть вас в нашей обучающей платформе.</p>

                @if(!empty($action_url))
                    <div class="email-cta">
                        <a href="{{ $action_url }}" class="btn-primary">Начать обучение</a>
                    </div>
                @endif

                <p class="muted">Если вы не регистрировались на нашем сайте, пожалуйста, проигнорируйте это письмо.</p>
            </div>

            <div class="footer">
                <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
            </div>
        </div>
    </div>
</body>
</html>
