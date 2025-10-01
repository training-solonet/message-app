<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BotStop extends Command
{
    protected $signature = 'bot:stop';
    protected $description = 'Stop WhatsApp Bot';

    public function handle()
    {
        $pid = file_get_contents(storage_path('bot.pid'));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /PID $pid /F");
        } else {
            exec("kill -9 $pid");
        }

        $this->info("Bot with PID $pid stopped!");

    }
}
