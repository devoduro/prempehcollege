<?php

namespace App\Console\Commands;

use App\Models\OsaAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateOsaAdmin extends Command
{
    protected $signature = 'osa:create-admin {email} {--name=} {--password= : If omitted you are asked for it}';

    protected $description = 'Create (or update) an Old Students Association portal admin.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $password = (string) ($this->option('password') ?: $this->secret('Password (min 10 chars, upper and lower case, a number)'));

        $v = Validator::make(['email' => $email, 'password' => $password], [
            'email' => ['required', 'email:rfc'],
            'password' => ['required', Password::min(10)->mixedCase()->numbers()],
        ]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $admin = OsaAdmin::firstOrNew(['email' => $email]);
        $admin->name = (string) ($this->option('name') ?: $admin->name ?: strstr($email, '@', true));
        $admin->password = $password;
        $admin->is_active = true;
        $admin->save();

        $this->info("OSA admin {$email} ready. Sign in at ".url('/osa'));

        return self::SUCCESS;
    }
}
