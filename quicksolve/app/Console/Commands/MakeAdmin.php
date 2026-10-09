<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class MakeAdmin extends Command
{
    protected $signature = 'quicksolve:make-admin {email} {--name=} {--password=}';

    protected $description = 'Promote an existing user to administrator, or create one when a password is provided';

    public function handle(): int
    {
        $email = strtolower((string) $this->argument('email'));
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("{$email} is now an administrator.");

            return self::SUCCESS;
        }

        $password = (string) $this->option('password');
        $name = (string) ($this->option('name') ?: 'QuickSolve Admin');

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ], [
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('No user exists for that email. Pass --name and --password (at least 8 characters) to create the administrator.');

            return self::FAILURE;
        }

        $user = new User;
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
            'is_admin' => true,
        ])->save();

        $this->info("Created administrator {$email}.");

        return self::SUCCESS;
    }
}
