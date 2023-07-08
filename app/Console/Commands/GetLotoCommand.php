<?php

namespace App\Console\Commands;

use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetLotoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-loto-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $end = Carbon::now()->subDay();
        $endStr = $end->toDateString();
        $start = $end->subDays(100)->toDateString();

        $dataFirstNumber = Result::query()
            ->select(DB::raw('first_number, count(*) as total'))
            ->join('prize_draw_days', 'results.day_id', '=', 'prize_draw_days.id')
            ->where('prize_draw_days.date', '<=', $endStr)
            ->where('prize_draw_days.date', '>=', $start)
            ->groupBy('first_number')
            ->orderBy('total', 'DESC')
            ->limit(8)
            ->pluck(8)
            ->get();
        dd($dataFirstNumber->toArray());

    }
}
