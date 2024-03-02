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
        \App\Models\User::factory(155)->create();

        \App\Models\User::factory()->create([
            'name' => 'Soporte DTW',
            'email' => 'soporte@dtw.com.mx',
            'phone' => '0000000000',
            'password' => bcrypt('321321321'),
            'employee_properties' => ["job_position" => "Soporte", "department" => "Desarrollo", "company" => "Papel diseño y color", "branch" => "Av. del Tigre"],
        ]);

        $this->call([
            CategorySeeder::class,
            TicketSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
