<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_failed_logins_are_rate_limited(): void
    {
        $email = 'throttle@us.test';

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->from('/login')->post('/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->from('/login')->post('/login', [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

        $throttleKey = Str::transliterate(Str::lower($email).'|127.0.0.1');

        // Middleware-based throttling yields 429; Breeze request limiter yields throttled login errors.
        $this->assertTrue(in_array($response->getStatusCode(), [302, 429], true));
        $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, 5));
    }
}
