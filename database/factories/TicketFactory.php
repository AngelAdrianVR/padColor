<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $branches = ['Alfajayucan', 'Morelia', 'San Luis Potosí', 'Acapulco', 'Av. del Tigre', 'Calle C', 'Calle 2', 'Veracruz', 'León', 'Juárez', 'Puebla', 'Monterrey', 'Federalismo'];
        return [
            'title' => $this->faker->text(100),
            'description' => $this->faker->text(100),
            'status' => $this->faker->randomElement(['Abierto', 'Completado', 'En proceso', 'Re-abierto']),
            'priority' => $this->faker->randomElement(['Baja', 'Media', 'Alta']),
            'ticket_type' => $this->faker->randomElement(['Solicitud o servicio', 'Soporte o incidencia']),
            'expired_date' => '2024-02-15',
            'branch' => $this->faker->randomElement($branches),
            'user_id' => 1,
            'responsible_id' => 1,
            'category_id' => 1,
        ];
    }
}
