<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    public function run(): void
    {
        SystemConfig::create([
            'content_mode' => 'cms',
            'git_repository_url' => null,
            'git_branch' => 'main',
            'git_access_token' => null,
            'git_webhook_secret' => null,
            'git_sync_frequency' => 15,
            'last_synced_at' => null,
            'setup_completed' => true,
        ]);
    }
}
