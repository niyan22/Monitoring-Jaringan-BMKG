<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>

<div class="auth-container">

    <div class="logo">
        <img src="{{ asset('assets/image/logo.jpeg') }}" alt="Logo">
    </div>

    <div class="auth-card">
        <h2>Login</h2>

        {{-- NOTIFIKASI ERROR LOGIN --}}
        @if ($errors->any())
            <div class="alert-error">
                ! Email atau Password salah
            </div>
        @endif
        @if (session('status'))
            <div class="alert-success">
                ✅ {{ session('status') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        {{-- ✅ FORM LOGIN BREEZE --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label>Username</label> 

            <input
                class="input"
                type="email"
                name="email"
                placeholder="Email"
                value="{{ old('email') }}"
                required
                autofocus
            >
            <label>Password</label>

            <input
                class="input"
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button class="btn" type="submit">
                Login
            </button>


        </form>

    </div>

</div>

</body>
</html>
