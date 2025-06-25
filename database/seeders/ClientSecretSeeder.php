<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

class ClientSecretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Client::query()->exists()) {
            return;
        }

        Artisan::call('passport:keys', ['--force' => false, '--length' => 4096]);

        $personal_client = Passport::client()->forceFill([
            'id' => 1,
            'user_id' => null,
            'name' => 'Laravel Personal Access Client',
            'secret' => 'HbtYZvIgFLmRp6QdSuqUd4ZdkeOAbpGTRJzzVlLv',
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
        ]);
        $personal_client->save();

        $passport_client = Passport::client()->forceFill([
            'id' => 2,
            'user_id' => null,
            'name' => 'Laravel Password Grant Client',
            'secret' => 'V8LB4CY9YqmkDJbl3iKshTHNj0xob34dx1XIKLoh',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
        ]);
        $passport_client->save();

        $personal_client = Passport::client()->forceFill([
            'id' => 3,
            'user_id' => null,
            'name' => 'Laravel Personal Access Client',
            'secret' => 'iOCsAxXTDbYgAaq5Bo17ijjNPmEuVv65dYn0OFFD',
            'provider' => null,
            'redirect' => 'http://localhost',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
        ]);
        $personal_client->save();

        $passport_client = Passport::client()->forceFill([
            'id' => 4,
            'user_id' => null,
            'name' => 'Laravel Password Grant Client',
            'secret' => 'nL0XI7bKltNeI4dXAbhu68p7RBiW9HEfhOhW4aol',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
        ]);
        $passport_client->save();
    }
}
