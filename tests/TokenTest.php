<?php

namespace Lean\Installer\Tests;

use Illuminate\Support\Facades\Http;

class TokenTest extends TestCase
{
    /** @test */
    public function token_name_is_required()
    {
        Http::fake([
            'lean-admin.dev/install/auth' => Http::response(['message' => 'OK'], 200, ['Content-Type' => 'application/json']),
        ]);

        $this->artisan('lean:setup')
            ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsOutput('Successfully logged in.')
            ->expectsQuestion('Token name', '')
            ->expectsOutput('Invalid token') // <--
            ->expectsQuestion('Token name', 'foo')
            ->expectsOutput('Generating token...')
            ->assertExitCode(0);
    }

    /** @test */
    public function token_name_defaults_to_the_computer_hostname()
    {
        Http::fake([
            'lean-admin.dev/install/auth' => Http::response(['message' => 'OK'], 200, ['Content-Type' => 'application/json']),
        ]);

        $this->artisan('lean:setup')
            ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsChoice('Token name', gethostname(), [gethostname()]) // <--
            ->assertExitCode(0);
    }

    /** @test */
    public function other_content_in_auth_json_is_not_overridden()
    {
        file_put_contents(base_path('auth.json'), json_encode(['my' => 'preexisting content']));

        Http::fake([
            'lean-admin.dev/install/auth' => Http::response(['message' => 'OK'], 200, ['Content-Type' => 'application/json']),
            'lean-admin.dev/install/token' => Http::response(['token' => 'bar'], 200, ['Content-Type' => 'application/json']),
        ]);

        $this->artisan('lean:setup')
            ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsQuestion('Token name', 'foo')
            ->expectsOutput('Generating token...')
            ->expectsOutput('✅ Everything succeeded ✅')
            ->expectsQuestion('Remove installer now?', false)
            ->assertExitCode(0);

        $content = json_decode(file_get_contents(base_path('auth.json')), true);

        $this->assertSame([
            'my' => 'preexisting content',
            'bearer' => [
                'lean-admin.dev' => 'bar',
            ],
        ], $content);
    }
}
