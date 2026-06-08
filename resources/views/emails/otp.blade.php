<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi MyMusic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fcf9f8;
            color: #1c1b1b;
            padding: 20px;
        }
        .container {
            max-w-md;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #1c1b1b;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #006687;
            background: #f0edec;
            padding: 15px 20px;
            border-radius: 8px;
            border: 2px dashed #1c1b1b;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #3e484e;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Selamat datang di MyMusic!</h2>
        <p>Gunakan kode verifikasi berikut untuk menyelesaikan pendaftaran akun Anda:</p>
        
        <div class="otp-code">{{ $otpCode }}</div>
        
        <p>Kode ini hanya berlaku selama 10 menit. Jangan berikan kode ini kepada siapa pun.</p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} MyMusic. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
