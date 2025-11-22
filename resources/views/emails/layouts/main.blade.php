<!DOCTYPE html>
<html dir="{{ context('email_direction') }}" lang="{{ context('email_language') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? settings('app_name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
            background-color: #e5a523;
            color: white;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .header img {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 20px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            color: #777;
            border-top: 1px solid #eee;
            background-color: #f9f9f9;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .btn {
            display: inline-block;
            background-color: #e5a523;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 4px;
            margin-top: 20px;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .highlight {
            color: #e5a523;
            font-weight: bold;
        }
        .social-icons {
            margin-top: 15px;
        }
        .social-icons a {
            display: inline-block;
            margin: 0 5px;
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(settings('logo'))
                <img src="{{ settings('logo') }}" alt="{{ settings('app_name') }}" class="logo">
            @endif
            <h2>{{ settings('app_name') }}</h2>
            @if(isset($subtitle))
                <p>{{ $subtitle }}</p>
            @endif
        </div>
        
        <div class="content">
            {{ $slot }}
        </div>
        
        <div class="footer">
            <p>
                {{ settings('app_name') }}<br>
                {{ settings('address') }}<br>
                @if(settings('working_time'))
                    {{ settings('working_time') }}<br>
                @endif
                {{ emailTranslation('Phone:') }} {{ settings('phone', '+1 234 567 890') }}<br>
                {{ emailTranslation('Email:') }} {{ settings('email', 'contact@example.com') }}
            </p>
            
            <div class="social-icons">
                @if(settings('facebook'))
                    <a href="{{ settings('facebook') }}" target="_blank">{{ __('Facebook') }}</a>
                @endif
                @if(settings('instagram'))
                    <a href="{{ settings('instagram') }}" target="_blank">{{ __('Instagram') }}</a>
                @endif
                @if(settings('twitter'))
                    <a href="{{ settings('twitter') }}" target="_blank">{{ __('Twitter') }}</a>
                @endif
                @if(settings('linkedin'))
                    <a href="{{ settings('linkedin') }}" target="_blank">{{ __('LinkedIn') }}</a>
                @endif
                @if(settings('youtube'))
                    <a href="{{ settings('youtube') }}" target="_blank">{{ __('YouTube') }}</a>
                @endif
                @if(settings('whatsapp'))
                    <a href="{{ settings('whatsapp') }}" target="_blank">{{ __('WhatsApp') }}</a>
                @endif
            </div>
            
            <p>{{ emailTranslation('This is an automated email, please do not reply to this message.') }}</p>
            <p>
                &copy; {{ date('Y') }} {{ settings('app_name') }}. <br> {{ emailTranslation('All rights reserved.') }}
            </p>
        </div>
    </div>
</body>
</html> 