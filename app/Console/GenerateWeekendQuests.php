<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QuestService;

class GenerateWeekendQuests extends Command
{
    protected $signature = 'quests:generate-weekend';
    protected $description = 'Generate weekend quests for upcoming weekend';

    protected $questService;

    public function __construct(QuestService $questService)
    {
        parent::__construct();
        $this->questService = $questService;
    }

    public function handle()
    {
        $this->info('Generating weekend quests...');

        $quest = $this->questService->generateWeekendQuest();

        if ($quest) {
            $this->info("Weekend quest created: {$quest->title}");
            $this->info("Available from: {$quest->start_date}");
            $this->info("Until: {$quest->end_date}");
        } else {
            $this->error('Failed to generate weekend quest');
        }

        return 0;
    }
}