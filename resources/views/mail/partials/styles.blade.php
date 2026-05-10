<style>
    @import url('https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap');

    :root{
        --primary: #4A148C;
        --primary-dark: #3b0f6e;
        --secondary: #2b2b2b;
        --bg: #FFFFFF;
        --paper: #F4F4F4;
        --text-primary: #000000;
        --text-secondary: #4A148C;
        --button-radius: 8px;
        --font-family: 'Raleway', Arial, Helvetica, sans-serif;
    }

    body, html {
        margin: 0;
        padding: 0;
        background-color: var(--bg);
        font-family: var(--font-family);
        color: var(--text-primary);
    }

    .email-wrapper {
        width: 100%;
        background-color: #f6f5fb;
        padding: 28px 0;
    }

    .email-container {
        width: 100%;
        max-width: 680px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(74,20,140,0.08);
        overflow: hidden;
        border: 1px solid rgba(74,20,140,0.06);
    }

    .email-header {
        display: flex;
        align-items: center;
        gap: 12px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        color: #ffffff;
        padding: 18px 22px;
    }

    .email-logo {
        width: 48px;
        height: 48px;
        display: inline-block;
        border-radius: 8px;
        background: rgba(255,255,255,0.06);
        padding: 6px;
        box-sizing: border-box;
    }

    .email-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.2px;
        color: var(--primary-dark);
    }

    .email-header .email-title {
        color: #ffffff;
    }

    .email-body {
        padding: 28px;
        color: var(--text-primary);
        font-size: 16px;
        line-height: 1.6;
    }

    .email-cta {
        display: block;
        text-align: center;
        margin: 20px 0;
    }

    .btn-primary {
        display: inline-block;
        background-color: var(--primary);
        color: #fff !important;
        padding: 12px 22px;
        border-radius: var(--button-radius);
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 6px 18px rgba(74,20,140,0.12);
    }

    .muted {
        color: #6b6b6b;
        font-size: 13px;
    }

    .footer {
        background-color: var(--paper);
        padding: 14px 22px;
        font-size: 13px;
        color: #6b6b6b;
        text-align: center;
    }

    .divider { height:1px; background: rgba(0,0,0,0.06); margin: 18px 0; }

    /* Responsive tweaks */
    @media screen and (max-width: 480px) {
        .email-container { padding: 0; border-radius: 0; }
        .email-body { padding: 20px; }
        .email-header { padding: 14px; }
        .email-title { font-size: 16px }
    }
</style>
