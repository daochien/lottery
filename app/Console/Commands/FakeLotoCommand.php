<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FakeLotoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fake-loto-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dàn đề 30 số trong vòng 3 ngày không ra thì vào đánh!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $number = "456789";
        $results = [];
        $this->combineNumbers("", $number, $results);
        dd(implode(',', $results));

    }

    protected function combineNumbers($prefix, $remaining, &$results)
    {
        if (intval($prefix) > 10 && intval($prefix) < 100) {
            $results[] = $prefix;
        }

        if (empty($remaining)) {
            return;
        }

        for ($i = 0; $i < strlen($remaining); $i++) {
            $newPrefix = $prefix . $remaining[$i];
            $newRemaining = substr($remaining, 0, $i) . substr($remaining, $i + 1);
            $this->combineNumbers($newPrefix, $newRemaining, $results);
        }
    }


}
