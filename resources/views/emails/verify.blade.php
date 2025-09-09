<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .link-text {
            word-break: break-all;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .divider {
            height: 1px;
            background: #eee;
            margin: 30px 0;
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
            background: #667eea;
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
                    <!-- Email icon SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                </div>
                <h1>Email Verification</h1>
            </div>
            
            <!-- Main content -->
            <div class="content">
                <h2>Hello {{ $user->name }}! 👋</h2>
                <p>Thank you for registering with us. We're excited to have you on board!</p>
                <p>To complete your registration and ensure your account security, please verify your email address by clicking the button below:</p>
                
                <div style="text-align: center;">
                    <a href="{{ $verification_url }}" class="button">Verify Email Address</a>
                </div>
                
                <div class="divider"></div>
                
                <p style="font-size: 14px; color: #888;">If the button above doesn't work, you can copy and paste the following link into your browser:</p>
                <p><a href="{{ $verification_url }}" class="link-text">{{ $verification_url }}</a></p>
                
                <div class="divider"></div>
                
                <p style="font-size: 14px; color: #888; margin-bottom: 0;">
                    If you didn't create an account with us, please ignore this email or contact our support team if you have concerns.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div class="company-info">
                    <!-- Company logo (optional) -->
                    <!-- <img src="your-logo-url.png" alt="Company Logo"> -->
                    <p><strong>PT Rileks Happy Abadi</strong></p>
                </div>
                
                <div class="social-links">
                    <a href="#" title="Facebook">f</a>
                    <a href="#" title="Twitter">t</a>
                    <a href="#" title="LinkedIn">in</a>
                    <a href="#" title="Instagram">ig</a>
                </div>
                
                {{-- <p>Plaza Simatupang Lt. 6 Unit 3 Jl. TB. Simatupang Kav. IS No.01, RT.2/RW.17, Pd. Pinang, Kec. Kby. Lama, Daerah Khusus Ibukota Jakarta 12310</p> --}}
                <p>© 2025 PT Rileks Happy Abadi. All rights reserved.</p>
                <p style="margin-top: 15px; font-size: 12px;">
                    You received this email because you signed up for an account. 
                    If you have any questions, please contact our support team.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
