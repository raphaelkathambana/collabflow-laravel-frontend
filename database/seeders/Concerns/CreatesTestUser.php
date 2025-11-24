<?php

namespace Database\Seeders\Concerns;

use App\Models\User;

trait CreatesTestUser
{
    /**
     * Get or create a test user for seeding
     *
     * @return User
     */
    protected function getOrCreateTestUser(): User
    {
        $user = User::where('email', 'john@collabflow.test')->first();

        if (!$user) {
            $user = User::create([
                'name' => 'John Doe',
                'email' => 'john@collabflow.test',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]);

            $this->command->info('✓ Test user created: john@collabflow.test');
        } else {
            $this->command->info('✓ Using existing test user: john@collabflow.test');
        }

        return $user;
    }
}
