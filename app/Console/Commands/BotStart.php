<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BotStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:start';
    protected $description = 'Start WhatsApp Bot';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $process = proc_open(
            'node ' . base_path('index.js'),
            [],
            $pipes
        );

        $status = proc_get_status($process);
        file_put_contents(storage_path('bot.pid'), $status['pid']);
    }
}
