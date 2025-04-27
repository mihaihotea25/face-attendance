<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;
use Faker\Factory as Faker;


class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $directories = Storage::disk('private')->directories('students');

        foreach ($directories as $folderName) {
            $id = basename($folderName);

            Student::create([
                'id' => $id,
                'name' => $faker->name,               // random full name
                'an' => $faker->numberBetween(1, 4),   //  random year between 1 and 4
                'specializare' => $faker->randomElement([
                    'Informatica', 'Matematica', 'Fizica', 'Chimie', 'Biologie', 'Electronica'
                ]) //  random specialization
            ]);
        }
    }
}
