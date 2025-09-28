<x-guest-layout>
    <div class="flex justify-center mb-6 mt-4">
        <div
            class="w-12 h-12 bg-blue-600 text-white flex items-center justify-center rounded-xl shadow-md text-2xl font-bold">
            $
        </div>
    </div>

    <!-- Title -->
    <h2 class="text-center text-2xl font-bold text-gray-800">
        Welcome Back
        {{-- <span class="text-blue-600">
            School Finance Manager
        </span> --}}
    </h2>

    <p class="mt-2 text-center text-gray-500 text-sm"> Don't have an account?
        <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:underline">Register</a>
    </p>

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5 mb-4">
        @csrf

        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
            <x-text-input id="email" name="email" type="email" class="block w-full mt-1"
                placeholder="{{ __('Email') }}" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4 relative">
            <label for="password" class="block text-sm font-medium text-gray-600">Password</label>
            <div class="relative mt-1">
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-200 px-3 py-2 pr-10"
                    placeholder="••••••••">
                <!-- Tombol ikon mata -->
                <button type="button" onclick="togglePasswordVisibility()"
                    class="absolute top-1/2 right-0 p-3 mr-1 transform -translate-y-1/2 flex items-center text-gray-500">
                    <!-- Icon Eye -->
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- <!-- Remember + Forgot --> 
        <label class="inline-flex items-center"> 
            <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> 
            <span class="ml-2 text-gray-600">Remember me</span> </label> <a href="{{ route('password.request') }}" class="text-blue-600 hover:underline">Forgot password?</a> </div> --}}
        <!-- Button -->

        <div class="mt-4">
            <button type="submit" id="loginButton"
                class="w-full justify-center flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300 transition">
                <svg id="loginSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>

                <span id="loginText">Log in</span>
            </button>
        </div>
    </form>
</x-guest-layout>
