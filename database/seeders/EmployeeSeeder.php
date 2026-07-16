<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'PROD PN1', 'PROD PN2', 'QMS', 'FAT', 'IT', 'SCM', 'PPIC',
            'R&D', 'WHFG', 'WHRM', 'ENGINEERING', 'PROJECT', 'PURCHASIING',
            'HRGA', 'MR', 'PROD BMSD',
        ];

        foreach ($departments as $dept) {
            Employee::firstOrCreate(
                ['name' => $dept],
                ['department' => $dept, 'is_active' => true],
            );
        }

        $this->command->info('16 employee records seeded successfully.');
    }
}
