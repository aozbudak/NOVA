<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Support\AdminStore;
use App\Support\DatabaseRecords;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            AdminCatalogSeeder::class,
        ]);

        $records = new DatabaseRecords;

        if (! Customer::query()->where('email', 'ada@nova.example')->exists()) {
            $records->registerCustomer([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@nova.example',
                'password' => 'password123',
            ]);
        }

        foreach ((new AdminStore)->users() as $staff) {
            $records->saveStaff($staff, 'secret123');
        }
    }
}
