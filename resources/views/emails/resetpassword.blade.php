<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f5f5f5;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .header-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        .content {
            padding: 40px 30px;
        }
        h2 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
        }
        p {
            font-size: 16px;
            color: #555;
            margin: 0 0 20px 0;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            margin: 25px 0;
            color: #fff;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }
        .warning-box {
            background: #fff4e6;
            border-left: 4px solid #ffb366;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .warning-box p {
            margin: 0;
            color: #cc5200;
            font-size: 14px;
        }
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 20px;
            margin: 25px 0;
            border-radius: 8px;
            text-align: center;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #0369a1;
            font-size: 18px;
        }
        .info-box p {
            margin: 0;
            color: #0369a1;
            font-size: 16px;
        }
        .link-text {
            word-break: break-all;
            color: #f5576c;
            text-decoration: none;
            font-size: 14px;
        }
        .divider {
            height: 1px;
            background: #eee;
            margin: 30px 0;
        }
        .security-tips {
            background: #fafafa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .security-tips h4 {
            color: #333;
            margin: 0 0 15px 0;
            font-size: 16px;
        }
        .security-tips ul {
            margin: 0;
            padding-left: 20px;
        }
        .security-tips li {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
        }
        .footer p {
            font-size: 14px;
            color: #888;
            margin: 0;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            display: inline-block;
            width: 35px;
            height: 35px;
            background: #e9ecef;
            border-radius: 50%;
            margin: 0 5px;
            line-height: 35px;
            text-align: center;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background: #f5576c;
            color: white;
        }
        .company-info {
            margin-top: 20px;
        }
        .company-info img {
            height: 30px;
            margin-bottom: 10px;
        }
        @media screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .header {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header with gradient background -->
            <div class="header">
                <div class="header-icon">
                    <!-- Lock icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1>Reset Your Password</h1>
            </div>
            
            <!-- Main content -->
            <div class="content">
                <h2>Hello {{ $user->name }}! 🔐</h2>
                <p>We received a request to reset your password. If you made this request, please click the button below to set a new password:</p>
                
                <div style="text-align: center;">
                    <a href="{{ $reset_token }}" class="button">Reset Password</a>
                </div>
                
                <div class="warning-box">
                    <p><strong>⚠️ Important:</strong> This link will expire in 1 hour for security reasons.</p>
                </div>
                
                <div class="divider"></div>
                
                <p style="font-size: 14px; color: #888;">If the button above doesn't work, you can copy and paste the following link into your browser:</p>
                <p><a href="{{ $reset_token }}" class="link-text">{{ $reset_token }}</a></p>
                
                <div class="divider"></div>
                
                <div class="info-box">
                    <h3>Didn't request this?</h3>
                    <p>If you didn't request a password reset, please ignore this email. Your password won't be changed and your account remains secure.</p>
                </div>
                
                <div class="security-tips">
                    <h4>Security Tips:</h4>
                    <ul>
                        <li>Never share your password with anyone</li>
                        <li>Use a strong password with letters, numbers, and symbols</li>
                        <li>Change your password regularly</li>
                        <li>Be cautious of phishing emails asking for your password</li>
                    </ul>
                </div>
                
                <p style="font-size: 14px; color: #888; margin-bottom: 0;">
                    For your security, this password reset was requested from:
                    <br>
                    <strong>IP Address:</strong> {{ $ipAddress ?? 'Not available' }}
                    <br>
                    <strong>Time:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="company-info">
                    <!-- Company logo (optional) -->
                    <!-- <img src="your-logo-url.png" alt="Company Logo"> -->
                    <p><strong>PT RAMBU ADIYAKSA STUDIO</strong></p>
                </div>
                
                <div class="social-links">
                    <a href="#" title="Facebook">f</a>
                    <a href="#" title="Twitter">t</a>
                    <a href="#" title="LinkedIn">in</a>
                    <a href="#" title="Instagram">ig</a>
                </div>
                
                {{-- <p>123 Main Street, City, Country 12345</p> --}}
                <p>© 2025 PT RAMBU ADIYAKSA STUDIO. All rights reserved.</p>
                <p style="margin-top: 15px; font-size: 12px;">
                    This is an automated security email. Please do not reply.
                    If you need help, contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
