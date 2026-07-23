<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Config\Config;
use App\Enums\RouteName;
use App\Models\User;
use App\Models\UserLoginToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Ramsey\Uuid\Uuid;

use function now;
use function sprintf;

/**
 * Mint a passwordless login link on the command line, bypassing the emailed
 * magic link. Handy for local demos where MAIL_MAILER=log and 2FA runs on the
 * fake OTP driver: run this, open the URL, enter any 6-digit code.
 *
 * Refuses to run in production: it hands out a ready-to-use login link with no
 * mailbox round-trip, which must never be possible on a real deployment.
 */
class DevLoginLink extends Command
{
    protected $signature = 'dev:login-link
        {--email=demo@example.com : Email of the user to log in as}';
    protected $description = 'Generate a passwordless login link for local development';

    public function handle(): int
    {
        if (App::isProduction()) {
            $this->output->error('dev:login-link is disabled in production.');

            return self::FAILURE;
        }

        $email = (string) $this->option('email');

        $user = User::where('email', $email)->first();
        if ($user === null) {
            $this->output->error(sprintf('No user found with email %s.', $email));

            return self::FAILURE;
        }

        $expiryMinutes = Config::integer('auth.passwordless.token_expiry_minutes');

        // One active token per user, matching the mailed-link flow.
        $user->userLoginTokens()->delete();

        /** @var UserLoginToken $token */
        $token = $user->userLoginTokens()->create([
            'token' => Uuid::uuid4()->toString(),
            'expires_at' => now()->addMinutes($expiryMinutes),
            'destination' => '/',
        ]);

        $link = URL::signedRoute(
            RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME,
            ['token' => $token->token],
            now()->addMinutes($expiryMinutes),
        );

        $this->output->success(sprintf('Login link for %s (valid %d min):', $user->email, $expiryMinutes));
        $this->output->writeln($link);
        $this->output->writeln('');
        $this->output->note('Open the link, then enter any 6-digit code (fake OTP driver in local).');

        return self::SUCCESS;
    }
}
