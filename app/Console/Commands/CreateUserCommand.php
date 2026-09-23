<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('rukre:user {email? : The email address used to sign in} {--name= : Display name} {--password= : Password (prompted for when omitted)}')]
#[Description('Create a RUKRE account, or reset the password of an existing one')]
class CreateUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: text('Email address', required: true);
        $existing = User::query()->where('email', $email)->first();
        $name = $this->option('name') ?: ($existing?->name ?? text('Name', default: strstr((string) $email, '@', true) ?: 'Viewer', required: true));
        $plain = $this->option('password') ?: password('Password', required: true);

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $plain],
            ['email' => ['required', 'email'], 'name' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'min:8']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->updateOrCreate(['email' => $email], ['name' => $name, 'password' => $plain]);

        $this->info($existing ? "Password updated for {$email}." : "Account created for {$email}.");

        return self::SUCCESS;
    }
}
