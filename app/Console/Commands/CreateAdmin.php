<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=} {--role=super-admin} {--password= : Password to set; if omitted you are asked for it}';

    protected $description = 'Create (or update) an admin account. The password is asked for and never shown.';

    public function handle(): int
    {
        $role = (string) $this->option('role');
        if (! array_key_exists($role, User::ROLES)) {
            $this->error('Role must be one of: '.implode(', ', array_keys(User::ROLES)));

            return self::FAILURE;
        }

        $email = strtolower(trim((string) $this->argument('email')));
        // --password is handy locally, but it lands in shell history; omit it on shared or live machines.
        $password = (string) ($this->option('password') ?: $this->secret('Password (min 12 chars, upper and lower case, a number)'));

        $v = Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email:rfc'],
            'password' => ['required', Password::defaults()],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        (new RolesSeeder)->run();

        $user = User::firstOrNew(['email' => $email]);
        $user->name = (string) ($this->option('name') ?: $user->name ?: strstr($email, '@', true));
        $user->password = $password;
        $user->is_active = true;
        $user->save();
        $user->syncRoles([$role]);

        $this->info("Admin {$email} saved with role {$role}. Two-factor setup is required at first sign-in.");

        return self::SUCCESS;
    }
}
