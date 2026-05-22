<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk – E-BK Sistem Bimbingan Konseling</title>
<meta name="description" content="Login ke E-BK: Platform konsultasi rahasia berbasis web">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/ebk.css') }}" rel="stylesheet">
</head>
<body>

<div id="login-screen">
    {{-- Left Art Panel --}}
    <div class="login-art">
        <div class="login-art-circles"></div>
        <div class="login-logo" style="margin-bottom:20px;width:72px;height:72px;border-radius:20px">
            <i class="fas fa-hands-helping" style="font-size:32px"></i>
        </div>
        <h1>E-BK<br><span>Bimbingan &amp; Konseling</span><br>Online</h1>
        <p>Platform konsultasi rahasia berbasis web untuk mendukung keterbukaan siswa dalam mengatasi masalah</p>
        <div class="login-badges">
            <span class="login-badge"><i class="fas fa-lock"></i> Rahasia</span>
            <span class="login-badge"><i class="fas fa-comments"></i> Real-time Chat</span>
            <span class="login-badge"><i class="fas fa-shield-alt"></i> Aman</span>
        </div>
    </div>

    {{-- Right Form Panel --}}
    <div class="login-form-panel">
        <div class="login-logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h2>Selamat Datang</h2>
        <p class="sub">Masuk untuk mengakses layanan konseling</p>

        @if($errors->any())
        <div class="login-error show">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" style="width:100%">
            @csrf
            <div class="form-group">
                <label for="email">Email / Username</label>
                <input type="text" id="email" name="email" placeholder="Masukkan email..." value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login" id="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        <div class="login-hint">
            <b>Demo Akun:</b><br>
            superadmin@ebk.id / password<br>
            guru@ebk.id / password<br>
            siswa@ebk.id / password
        </div>
    </div>
</div>

</body>
</html>
