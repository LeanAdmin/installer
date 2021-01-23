<?php

namespace Lean\Installer\Tests;

use Lean\Installer\Concerns\AcceptsCredentials;

class CredentialsTest extends TestCase
{
    /** @test */
    public function credentials_are_validated_locally()
    {
        $this->expectExceptionMessage('Interrupted by user');

        $this->artisan('lean:setup')
            ->expectsQuestion("What's your email?", 'samuel.stanclgmail.com')
            ->expectsOutput('Invalid email') // <--
            ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsOutput('Invalid credentials')
            ->expectsConfirmation('Retry?', 'no')
            ->assertExitCode(1);
    }

    /** @test */
    public function if_the_server_returns_an_error_it_will_be_shown()
    {
        $this->expectExceptionMessage('Interrupted by user');

        $this->artisan('lean:setup')
            ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsOutput('Invalid credentials') // <--
            ->expectsConfirmation('Retry?', 'no')
            ->assertExitCode(1);
    }

    // Right now there's not a scenario when this can happen, because all validation happens on both
    // the frontend and the backend. Keeping this test here since the behavior itself is in place
    // and I may want to test this sometime, when the backend returns other validation errors.
    // /** @test */
    // public function if_the_server_returns_errors_for_specific_fields_they_will_be_shown()
    // {
    //     $this->expectExceptionMessage('Interrupted by user');

    //     $this->artisan('lean:setup')
    //         ->expectsQuestion("What's your email?", 'samuel.stancl@gmail.com')
    //         ->expectsQuestion("What's your password?", 'foo')
    //         ->expectsOutput('Invalid credentials')
    //         ->expectsConfirmation('Retry?', 'no')
    //         ->assertExitCode(1);
    // }

    /** @test */
    public function email_defaults_to_git_email()
    {
        AcceptsCredentials::$emailOverride = 'samuel.stancl+test@gmail.com';

        $this->expectExceptionMessage('Interrupted by user');

        $this->artisan('lean:setup')
            ->expectsChoice("What's your email?", 'samuel.stancl+test@gmail.com', ['samuel.stancl+test@gmail.com']) // <--
            ->expectsQuestion("What's your password?", 'foo')
            ->expectsOutput('Invalid credentials')
            ->expectsConfirmation('Retry?', 'no')
            ->assertExitCode(1);
    }
}
