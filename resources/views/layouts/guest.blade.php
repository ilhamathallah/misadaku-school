<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Misadaku School</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images/misadaku.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen px-4 flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100">

        <div class="w-full items-center sm:max-w-md px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 
                    0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.219 
                    6.219A9.955 9.955 0 0112 5c4.477 0 8.268 
                    2.943 9.542 7a9.958 9.958 0 01-4.243 
                    5.132M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 
                    3l18 18" />`;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 
                    5c4.477 0 8.268 2.943 9.542 7-1.274 
                    4.057-5.065 7-9.542 7-4.477 
                    0-8.268-2.943-9.542-7z" />`;
        }
    }

    function toggleConfirmPasswordVisibility() {
        const passwordInput = document.getElementById('password_confirmation');
        const eyeIcon = document.getElementById('confirmEyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 
                    0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592M6.219 
                    6.219A9.955 9.955 0 0112 5c4.477 0 8.268 
                    2.943 9.542 7a9.958 9.958 0 01-4.243 
                    5.132M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 
                    3l18 18" />`;
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 
                    5c4.477 0 8.268 2.943 9.542 7-1.274 
                    4.057-5.065 7-9.542 7-4.477 
                    0-8.268-2.943-9.542-7z" />`;
        }
    }

    // loading button
    document.querySelector('form').addEventListener('submit', function() {
        const button = document.getElementById('loginButton');
        const spinner = document.getElementById('loginSpinner');
        const text = document.getElementById('loginText');

        button.disabled = true;
        spinner.classList.remove('hidden');
        text.textContent = 'Logging in...';

    });
</script>

</html>
