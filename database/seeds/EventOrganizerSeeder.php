<?php

use Illuminate\Database\Seeder;
use App\EventOrganizer;

class EventOrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EventOrganizer::create([
            'first_name' => 'John',
            'last_name'  => 'Organizer',
            'email'      => 'info@fikaplaces.com',
            'password'   => bcrypt('1nfoFIKAplac3s')
        ]);
    }
}
