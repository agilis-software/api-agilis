<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    private $statuses = [
        ['name' => 'TO_DO'],
        ['name' => 'IN_PROGRESS'],
        ['name' => 'DONE'],
    ];

    public function run(): void
    {
        foreach ($this->statuses as $status) {
            Status::firstOrCreate($status);
        }
    }
}
