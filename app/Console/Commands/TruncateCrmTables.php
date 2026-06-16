<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TruncateCrmTables extends Command
{
    protected $signature = 'crm:truncate';
    protected $description = 'Truncate clients, sites, subscriptions for fresh import';

    public function handle(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('subscriptions')->truncate();
        DB::table('sites')->truncate();
        DB::table('clients')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('Limpio.');
    }
}
