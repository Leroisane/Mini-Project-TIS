<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $path = database_path('seeders/concert_tickets.sql');

        if (file_exists($path)) {
            \Illuminate\Support\Facades\DB::unprepared(file_get_contents($path));
            $this->command->info('Concert tickets table seeded successfully from SQL file!');
        } else {
            $this->command->warn('SQL file not found. Please place concert_tickets.sql inside database/seeders/');
        }
    }
}
