<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Post::where('slug', 'home')->exists()) {
            Post::create([
                'title' => 'Home',
                'slug' => 'home',
                'content' => '<h1>Welcome to Your CMS</h1><p>This is your new home page. You can edit this content in the admin panel.</p>',
                'type' => 'page',
                'status' => 'publish',
                'author_id' => 1,
            ]);
            $this->command->info('Home page created successfully.');
        } else {
            $this->command->info('Home page already exists.');
        }
    }
}
