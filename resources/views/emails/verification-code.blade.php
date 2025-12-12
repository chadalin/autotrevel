<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код подтверждения для AutoRuta</title>
    <style>
        body {
            font-family: 'Open Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #0F2A44 0%, #1e3a5f 100%);
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF7A45 0%, #e56a35 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-text {
            color: white;
            font-size: 24px;
            font-weight: bold;
            font-family: 'Montserrat', sans-serif;
        }
        .content {
            background: white;
            padding: 40px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .code-container {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px dashed #f59e0b;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .code {
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #0F2A44;
            font-family: 'Montserrat', sans-serif;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #FF7A45 0%, #e56a35 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
            .code {
                font-size: 36px;
                letter-spacing: 8px;
            }
        }
    </style>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <div style="background: linear-gradient(to right, #f97316, #dc2626); padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0;">AutoRuta</h1>
        </div>
        
        <div style="padding: 30px; background-color: #f9fafb;">
            <h2 style="color: #1f2937;">Ваш код верификации</h2>
            <p style="color: #4b5563; font-size: 16px;">
                Для завершения входа в аккаунт AutoRuta используйте следующий код:
            </p>
            
            <div style="background-color: white; border-radius: 8px; padding: 20px; text-align: center; margin: 30px 0; border: 2px dashed #e5e7eb;">
                <div style="font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #1f2937;">
                    {{ $code }}
                </div>
            </div>
            
            <p style="color: #4b5563; font-size: 14px;">
                Этот код действителен в течение 15 минут. Если вы не запрашивали вход, просто проигнорируйте это письмо.
            </p>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 12px;">
                <p>С уважением,<br>Команда AutoRuta</p>
                <p>Это письмо отправлено автоматически, пожалуйста, не отвечайте на него.</p>
            </div>
        </div>
    </div>
</body>
            