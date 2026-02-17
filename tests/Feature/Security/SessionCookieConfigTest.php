<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SessionCookieConfigTest extends TestCase
{
    public function test_session_cookie_defaults_are_hardened(): void
    {
        $this->assertSame('lax', config('session.same_site'));
        $this->assertTrue(config('session.http_only'));
    }
}
