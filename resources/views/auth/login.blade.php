<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
            Welcome Back
        </h2>
        <p class="text-gray-600 mt-2">Sign in to continue your journey</p>
    </div>

    <x-validation-errors class="mb-4" />

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-label for="email" value="{{ __('Email') }}" class="text-gray-700 font-semibold" />
            <x-input id="email"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="email" name="email" :value="old('email')" required autofocus autocomplete="username"
                placeholder="you@example.com" />
        </div>

        <div class="mt-4">
            <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 font-semibold" />
            <x-input id="password"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
        </div>

        <div class="block mt-4 flex items-center justify-between">
            <label for="remember_me" class="flex items-center">
                <x-checkbox id="remember_me" name="remember"
                    class="text-purple-600 focus:ring-purple-500 rounded border-gray-300" />
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-purple-600 hover:text-purple-800 font-semibold hover:underline"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div class="flex items-center justify-end mt-8">
            <x-button
                class="w-full justify-center py-4 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold rounded-xl shadow-lg transform hover:scale-[1.02] transition-all">
                {{ __('Log in') }}
            </x-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-purple-600 font-bold hover:underline">
                Sign up
            </a>
        </div>
    </form>
</x-guest-layout>