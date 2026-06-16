<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostsImportSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('posts')->truncate();
        DB::unprepared(file_get_contents(database_path('seeders/posts_data.sql')));
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->info('Posts (40) imported.');
    }
}
