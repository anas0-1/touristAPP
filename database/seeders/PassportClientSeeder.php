<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Client;

class PassportClientSeeder extends Seeder
{
    public function run()
    {
        // Create a personal access client if it doesn't already exist
        if (!Client::where('password_client', 1)->exists()) {
            $client = new Client();
            $client->name = 'Personal Access Client';
            $client->redirect = '';
            $client->personal_access_client = true;
            $client->password_client = false;
            $client->revoked = false;
            $client->save();
        }
    }
}
