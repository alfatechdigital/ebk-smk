<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-BK — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/ebk.css') }}" rel="stylesheet">
    
    <style>
        /* Mengamankan layout container utama agar fleksibel di mobile */
        #login-screen {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* Penyesuaian khusus Perangkat Mobile (Layar di bawah 768px) */
        @media (max-width: 768px) {
            #login-screen {
                flex-direction: column;
                background: var(--cream); /* Mengikuti warna dasar tema ebk.css */
                justify-content: center;
                align-items: center;
                padding: 20px;
            }

            .login-form-panel {
                width: 100% !important;
                max-width: 440px; /* Batasi lebar maksimal di HP agar tidak terlalu melar */
                padding: 32px 24px !important;
                border-radius: var(--radius); /* Membunderkan sudut panel di mobile */
                box-shadow: var(--shadow-md);
                background: #fff;
            }

            /* Modifikasi tampilan logo panel atas khusus di mobile agar lebih manis */
            .login-form-panel .login-logo {
                width: 60px !important;
                height: 60px !important;
                font-size: 24px !important;
                margin-bottom: 16px !important;
            }

            .login-form-panel h2 {
                font-size: 24px !important;
            }

            /* Mengoptimalkan tombol & input agar nyaman di-tap jari (Touch Friendly) */
            .form-group input {
                padding: 12px 16px !important;
                font-size: 15px !important;
            }

            .btn-login {
                padding: 14px !important;
                font-size: 16px !important;
                margin-top: 10px !important;
            }

            /* Menata info demo account agar pas dan tidak tumpah teksnya */
            .login-hint {
                margin-top: 24px !important;
                font-size: 12px !important;
                line-height: 1.6 !important;
                padding: 12px !important;
                border-radius: var(--radius-sm);
                background: var(--cream);
                word-break: break-word; /* Mencegah teks email panjang memotong layar */
            }
        }

        /* Untuk HP super kecil/layar sempit (seperti iPhone SE) */
        @media (max-width: 375px) {
            .login-form-panel {
                padding: 24px 16px !important;
            }
            .login-hint {
                font-size: 11px !important;
            }
        }
    </style>
</head>
<body>
<div id="login-screen">
    {{-- Bagian Seni Kiri (Akan otomatis disembunyikan di mobile oleh ebk.css) --}}
    <div class="login-art">
        <div class="login-art-circles"></div>
        <div class="login-logo" style="margin-bottom:20px; width:72px; height:72px; border-radius:20px; background: var(--teal-light); display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-hands-helping" style="font-size:32px; color:#fff"></i>
        </div>
        <h1>E-BK<br><span>Bimbingan & Konseling</span><br>Online</h1>
        <p>Platform konsultasi rahasia berbasis web untuk mendukung keterbukaan siswa dalam mengatasi masalah</p>
        <div class="login-badges">
            <span class="login-badge"><i class="fas fa-lock"></i> Rahasia</span>
            <span class="login-badge"><i class="fas fa-comments"></i> Real-time Chat</span>
            <span class="login-badge"><i class="fas fa-shield-alt"></i> Aman</span>
        </div>
    </div>

    {{-- Bagian Form Kanan (Akan melebar otomatis menjadi 100% berpusat di tengah di mobile) --}}
    <div class="login-form-panel" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
        <div class="login-logo" style="background: var(--cream); color: var(--teal); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 20px;">
            <i class="fas fa-user-shield"></i>
        </div>
        <h2 style="margin-bottom: 4px; font-weight: 600;">Selamat Datang</h2>
        <p class="sub" style="margin-bottom: 24px; color: var(--muted); text-align: center;">Masuk untuk mengakses layanan konseling</p>

        @if ($errors->any())
            <div class="login-error show" style="width: 100%; margin-bottom: 16px; box-sizing: border-box;">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" style="width:100%">
            @csrf
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 14px;">Email / Username</label>
                <input type="text" name="email" placeholder="Masukkan email..." value="{{ old('email') }}" style="width: 100%; box-sizing: border-box;" required autofocus>
            </div>
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 14px;">Password</label>
                <input type="password" name="password" placeholder="••••••••" style="width: 100%; box-sizing: border-box;" required>
            </div>
            <button type="submit" class="btn-login" style="width: 100%; justify-content: center; display: flex; align-items: center; gap: 8px;"><i class="fas fa-sign-in-alt"></i> Masuk</button>
        </form>

        <div class="login-hint" style="width: 100%; box-sizing: border-box; text-align: center;">
            <b style="color: var(--charcoal);">Demo Accounts:</b><br>
            <span style="color: var(--slate); font-family: monospace;">superadmin@ebk.id · admin@ebk.id · guru@ebk.id · siswa@ebk.id</span><br>
            Password: <b style="color: var(--teal);">password</b>
        </div>
    </div>
</div>
</body>
</html>