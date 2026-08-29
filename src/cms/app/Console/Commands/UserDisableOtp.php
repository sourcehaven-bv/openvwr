<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

use function defined;
use function is_string;
use function Laravel\Prompts\text;
use function stream_isatty;

use const STDIN;

class UserDisableOtp extends Command
{
    protected $signature = 'user:disable-otp {email? : Email of the user to disable OTP for (skips the prompt when set)}';
    protected $description = 'Disable one-time-password for an existing user';

    public function handle(OtpService $otpService): int
    {
        try {
            $inputData = $this->getInputData();
        } catch (Throwable $throwable) {
            $this->output->error($throwable->getMessage());

            return self::INVALID;
        }

        $user = User::where('email', $inputData['email'])->first();
        if ($user === null) {
            $this->output->error('User does not exist');

            return self::FAILURE;
        }

        $otpService->disable($user);

        $this->output->success('Otp disabled');

        return self::SUCCESS;
    }

    /**
     * Prefer the argument so unattended callers (Ansible / just recipes running
     * with stdin closed) never reach a prompt. Without it we only prompt when a
     * terminal is actually there; Laravel Prompts reads the terminal rather
     * than stdin, so it cannot be answered by a pipe. Symfony only clears the
     * interactive flag for an explicit --no-interaction, so we check for a tty
     * as well to keep `artisan user:disable-otp </dev/null` reporting the
     * missing argument instead of failing with a bare "Required.".
     *
     * @return array{'email': string}
     */
    private function getInputData(): array
    {
        $email = $this->argument('email');
        if (is_string($email)) {
            return ['email' => $email];
        }

        if (!$this->input->isInteractive() || !$this->canPrompt()) {
            throw new RuntimeException('The email argument is required in non-interactive mode.');
        }

        return [
            'email' => text(label: 'Email address', required: true),
        ];
    }

    /**
     * Whether a prompt could actually be answered. Laravel Prompts reads the
     * terminal rather than stdin, so a closed or redirected stdin cannot
     * answer one. This mirrors the condition the framework itself uses to
     * decide whether prompts are interactive (see ConfiguresPrompts).
     *
     * The tty check cannot be exercised by the test suite: the runner never
     * has a terminal, and runningUnitTests() short-circuits this anyway, so
     * the real branch only ever runs outside the tests.
     *
     * @codeCoverageIgnore
     */
    private function canPrompt(): bool
    {
        if ($this->laravel->runningUnitTests()) {
            return true;
        }

        return defined('STDIN') && stream_isatty(STDIN);
    }
}
