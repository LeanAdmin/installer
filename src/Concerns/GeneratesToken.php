<?php

declare(strict_types=1);

namespace Lean\Installer\Concerns;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Validator;

trait GeneratesToken
{
    public static string $domain = 'lean-admin.dev';

    protected function getToken(string $email, string $password): Response
    {
        $tokenName = null;
        while (! $tokenName) {
            $input = $this->anticipate('Token name', [$this->getDefaultTokenName()], $this->getDefaultTokenName());

            $validator = Validator::make(['token' => $input], [
                'token' => 'required',
            ]);

            if ($validator->fails()) {
                $this->error('Invalid token');

                continue;
            }

            $tokenName = $input;
        }

        $this->line('Generating token...');

        return $this->generateToken($email, $password, $tokenName);
    }

    protected function generateToken(string $email, string $password, string $tokenName): Response
    {
        return $this->api('token', $email, $password, ['token_name' => $tokenName]);
    }

    protected function getDefaultTokenName(): ?string
    {
        if (gethostname()) {
            return config('app.name') . ' @ ' . gethostname();
        }

        return config('app.name');
    }

    protected function writeTokenToAuthJson(string $token): void
    {
        $authJson = base_path('auth.json');

        if (file_exists($authJson)) {
            $content = (string) file_get_contents($authJson);
        } else {
            $content = '{}';
        }

        $content = json_decode($content, true);
        $content['bearer'] ??= [];
        $content['bearer'][static::$domain] = $token;

        $content = json_encode($content);

        file_put_contents($authJson, $content);
    }
}
