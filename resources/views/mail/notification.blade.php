<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Уведомление' }}</title>
    @include('emails.partials.styles')
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">
        <div class="email-header">
            <h1 class="email-title">{{ $title ?? 'Уведомление' }}</h1>
        </div>

        <div class="email-body">
            {!! $body ?? '' !!}

            @if(!empty($action_url))
                <div class="email-cta">
                    <a href="{{ $action_url }}" class="btn-primary">{{ $action_text ?? 'Перейти' }}</a>
                </div>
            @endif

            <p class="muted">Это письмо было отправлено автоматически. Пожалуйста, не отвечайте на него.</p>
        </div>

        <div class="footer">
            <div>© {{ date('Y') }} Tallksy. Все права защищены.</div>
        </div>
    </div>
</div>
</body>
</html>
