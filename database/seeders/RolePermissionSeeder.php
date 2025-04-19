<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'Ver dashboard', 'category' => 'Dashboard']);

        Permission::create(['name' => 'Ver tickets', 'category' => 'Tickets']);
        Permission::create(['name' => 'Crear tickets', 'category' => 'Tickets']);
        Permission::create(['name' => 'Editar cualquier ticket', 'category' => 'Tickets']);
        Permission::create(['name' => 'Eliminar tickets', 'category' => 'Tickets']);

        Permission::create(['name' => 'Ver usuarios', 'category' => 'Usuarios']);
        Permission::create(['name' => 'Crear usuarios', 'category' => 'Usuarios']);
        Permission::create(['name' => 'Editar usuarios', 'category' => 'Usuarios']);
        Permission::create(['name' => 'Eliminar usuarios', 'category' => 'Usuarios']);

        Permission::create(['name' => 'Ver producciones', 'category' => 'Producciones']);
        Permission::create(['name' => 'Crear producciones', 'category' => 'Producciones']);
        Permission::create(['name' => 'Editar producciones', 'category' => 'Producciones']);
        Permission::create(['name' => 'Eliminar producciones', 'category' => 'Producciones']);
        
        Permission::create(['name' => 'Ver productos', 'category' => 'Productos']);
        Permission::create(['name' => 'Crear productos', 'category' => 'Productos']);
        Permission::create(['name' => 'Editar productos', 'category' => 'Productos']);
        Permission::create(['name' => 'Eliminar productos', 'category' => 'Productos']);
        
        Permission::create(['name' => 'Ver máquinas', 'category' => 'Máquinas']);
        Permission::create(['name' => 'Crear máquinas', 'category' => 'Máquinas']);
        Permission::create(['name' => 'Editar máquinas', 'category' => 'Máquinas']);
        Permission::create(['name' => 'Eliminar máquinas', 'category' => 'Máquinas']);
        
        Permission::create(['name' => 'Ver configuraciones', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Crear permisos', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Crear roles', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Crear categorias', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Editar permisos', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Editar roles', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Editar categorias', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Eliminar permisos', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Eliminar roles', 'category' => 'Configuraciones']);
        Permission::create(['name' => 'Eliminar categorias', 'category' => 'Configuraciones']);

        $super = Role::create(['name' => 'Super']);

        // Obtener todos los permisos
        $permissions = Permission::all();

        // Asignar todos los permisos al rol "Super"
        $super->syncPermissions($permissions);

        $users = User::all();
        $users->each(fn ($user) => $user->assignRole($super));
    }
}
