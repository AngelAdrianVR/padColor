<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(100)->create();

        \App\Models\User::factory()->create([
            'name' => 'Angel',
            'email' => 'angel@gmail.com',
            'phone' => '3333034738',
            'password' => bcrypt('321321321'),
            'employee_properties' => ["job_position" => "Super admin", "department" => "Desarrollador"],
        ]);

        $this->call([
            CategorySeeder::class,
            TicketSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
