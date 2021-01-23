<?php

declare(strict_types=1);

namespace Lean\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Lean\Installer\Concerns;

class Setup extends Command
{
    use Concerns\AcceptsCredentials,
        Concerns\GeneratesToken,
        Concerns\InteractsWithProcesses,
        Concerns\RunsInstallCommand;

    public static string $endpoint = 'https://lean-admin.dev/install';
    public static string $repository = 'https://lean-admin.dev/releases';

    protected Filesystem $files;

    public $signature = 'lean:setup';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        $this->info("\nLean Admin Installer");
        $this->info("--------------------\n");
        $this->line("First, we'll need your login information for lean-admin.dev.");
        $this->line("This won't be stored anywhere and will only be used to generate an API token.");
        [$email, $password] = $this->getCredentials();

        $this->line("\nNow we need to generate an access token for composer.");
        $token = $this->getToken($email, $password);

        if ($token->successful()) {
            $this->writeTokenToAuthJson($token->json('token'));
            $this->info('Token successfully added to auth.json.');

            $this->composer(['config', 'repositories.lean', 'composer', static::$repository]);
            $this->info('Repository added to composer.json.');

            $this->line('Requiring leanadmin/lean...');
            $this->composer(['require', 'leanadmin/lean']);
            $this->info('leanadmin/lean required.');

            $this->line('Running the install command...');
            $this->phpArtisanLeanInstall();
            $this->info('Install command succeeded.');

            $this->line("\n----------------------\n");

            $this->info('✅ Everything succeeded ✅');
            $this->info("\n🚀 Lean is now installed and accessible on " . config('app.url') . '/admin.');
            $this->line("\nIf the /admin route is already occupied, you may need to make small changes.");
            $this->line('Please see your routes/web.php file to verify that the routes were added correctly.');

            $this->line("\n----------------------\n");
            $this->line('You can remove the installer dependency by running `composer remove leanadmin/installer`.');
            if ($this->confirm('Remove installer now?', true)) {
                $this->composer(['remove', 'leanadmin/installer']);

                $this->info('Installer dependency removed.');
            }
        }

        return 0;
    }

    protected function getComputerName(): ?string
    {
        return gethostname() ?: null;
    }

    protected function api(string $path, string $email, string $password, array $extra = []): Response
    {
        return Http::acceptJson()->withoutRedirecting()->put(static::$endpoint . '/' . $path, array_merge([
            'email' => $email,
            'password' => $password,
        ], $extra));
    }
}
