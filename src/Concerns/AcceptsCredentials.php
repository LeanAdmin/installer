<?php

declare(strict_types=1);

namespace Lean\Installer\Concerns;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Throwable;

trait AcceptsCredentials
{
    /* Used for testing. */
    public static ?string $emailOverride = null;

    protected function getCredentials(): array
    {
        $email = null;
        $password = null;

        while (! $email) {
            $gitEmail = $this->getEmailFromGit();
            $input = $this->anticipate("What's your email?", [$gitEmail], $gitEmail);

            $validator = Validator::make(['email' => $input], [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                $this->error('Invalid email');

                continue;
            }

            $email = $input;
        }

        while (! $password) {
            $input = $this->secret("What's your password?");

            $validator = Validator::make(['password' => $input], [
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                $this->error('Invalid password');

                continue;
            }

            $password = $input;
        }

        $auth = $this->checkCredentials($email, $password);

        if ($auth->successful()) {
            $this->info('Successfully logged in.');

            return [$email, $password];
        } else {
            $errors = $auth->json('errors.*');

            if ($errors) {
                // Fields
                foreach ($arrayName = Arr::flatten($errors) as $error) {
                    $this->error($error);
                }
            } else {
                // General error message
                $this->error($auth->json('message'));
            }

            if ($this->confirm('Retry?', true)) {
                return $this->getCredentials();
            } else {
                throw new Exception('Interrupted by user');
            }
        }
    }

    protected function getEmailFromGit(): ?string
    {
        if (static::$emailOverride) {
            return static::$emailOverride;
        }

        try {
            $process = $this->getProcess(['git', 'config', 'user.email']);
            $process->mustRun();

            $output = trim($process->getOutput());

            return $output ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function checkCredentials(string $email, string $password): Response
    {
        return $this->api('auth', $email, $password);
    }
}
