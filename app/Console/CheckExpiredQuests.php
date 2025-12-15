<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quest;

class CheckExpiredQuests extends Command
{
    protected $signature = 'quests:check-expired';
    protected $description = 'Check and deactivate expired quests';

    public function handle()
    {
        $this->info('Checking for expired quests...');

        $expiredQuests = Quest::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->get();

        foreach ($expiredQuests as $quest) {
            $quest->update(['is_active' => false]);
            $this->line("Deactivated quest: {$quest->title}");
        }

        $this->info("Deactivated {$expiredQuests->count()} quests");

        return 0;
    }
}