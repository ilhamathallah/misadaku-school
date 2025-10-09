<x-guest-layout>

    <div class="flex justify-center mb-6 mt-4">
        <div
            class="w-12 h-12 bg-blue-600 text-white flex items-center justify-center rounded-xl shadow-md text-2xl font-bold">
            $
        </div>
    </div>

    <!-- Title -->
    <h2 class="text-center text-2xl font-bold text-gray-800">
        Create Account
        {{-- <span class="text-blue-600">
            School Finance Manager
        </span> --}}
    </h2>

    <p class="mt-2 text-center text-gray-500 text-sm"> Already have an account?
        <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Login</a>
    </p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
            <x-text-input id="name" class="block mt-1 w-full px-3 py-2 pr-10" type="text" name="name"
                placeholder="{{ __('Name') }}" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-600">Email</label>
            <x-text-input id="email" class="block mt-1 w-full px-3 py-2 pr-10" type="email" name="email"
                placeholder="{{ __('Email') }}" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
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
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- confirm password --}}
        <div class="mt-4 relative">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-600">Konfirmasi
                Password</label>
            <div class="relative mt-1">
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-600 focus:ring-blue-200 px-3 py-2 pr-10"
                    placeholder="••••••••">

                <!-- Tombol ikon mata -->
                <button type="button" onclick="toggleConfirmPasswordVisibility()"
                    class="absolute top-1/2 right-0 p-3 mr-1 transform -translate-y-1/2 flex items-center text-gray-500">
                    <!-- Icon Eye -->
                    <svg id="confirmEyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12
                    5c4.477 0 8.268 2.943 9.542 7-1.274
                    4.057-5.065 7-9.542 7-4.477
                    0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Submit Button -->
        <div class="mt-4">
            <x-primary-button class="w-full justify-center">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

</x-guest-layout>
