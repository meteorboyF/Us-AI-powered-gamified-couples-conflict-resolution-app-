<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
            Create Account
        </h2>
        <p class="text-gray-600 mt-2">Start your journey together</p>
    </div>

    <x-validation-errors class="mb-4" />

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-label for="name" value="{{ __('Name') }}" class="text-gray-700 font-semibold" />
            <x-input id="name"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="text" name="name" :value="old('name')" required autofocus autocomplete="name"
                placeholder="Your name" />
        </div>

        <div class="mt-4">
            <x-label for="email" value="{{ __('Email') }}" class="text-gray-700 font-semibold" />
            <x-input id="email"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="email" name="email" :value="old('email')" required autocomplete="username"
                placeholder="you@example.com" />
        </div>

        <div class="mt-4">
            <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 font-semibold" />
            <x-input id="password"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
        </div>

        <div class="mt-4">
            <x-label for="password_confirmation" value="{{ __('Confirm Password') }}"
                class="text-gray-700 font-semibold" />
            <x-input id="password_confirmation"
                class="block mt-1 w-full border-gray-200 focus:border-purple-500 focus:ring focus:ring-purple-200 rounded-xl py-3"
                type="password" name="password_confirmation" required autocomplete="new-password"
                placeholder="••••••••" />
        </div>

        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required
                                class="text-purple-600 focus:ring-purple-500 rounded border-gray-300" />

                            <div class="ms-2 text-sm text-gray-600">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                'terms_of_service' => '<a target="_blank" href="' . route('terms.show') . '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' . __('Terms of Service') . '</a>',
                'privacy_policy' => '<a target="_blank" href="' . route('policy.show') . '" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">' . __('Privacy Policy') . '</a>',
            ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
        @endif

        <div class="flex items-center justify-end mt-8">
            <x-button
                class="w-full justify-center py-4 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-bold rounded-xl shadow-lg transform hover:scale-[1.02] transition-all">
                {{ __('Register') }}
            </x-button>
        </div>

        <div class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-purple-600 font-bold hover:underline">
                Log in
            </a>
        </div>
    </form>
</x-guest-layout>