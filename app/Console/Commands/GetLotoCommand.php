<?php

namespace App\Console\Commands;

use App\Models\PrizeDrawDay;
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
        $results = [];
        for ($i = 1; $i <= 100; $i ++) {
            $result = $this->_reportResult($i);
            $results[] = $result;
        }
        dd($results);
    }

    protected function get64Number()
    {
        return $this->_reportResult(1);
    }

    protected function _reportResult(int $subDay) : array
    {
        //$subDay = 2;
        $endObj = Carbon::now()->subDays($subDay);
        $end = Carbon::now()->subDays($subDay)->toDateString();
        $start = Carbon::now()->subDays(100)->toDateString();

        $dataFirstNumber = Result::query()
            ->select(DB::raw('first_number, count(*) as total'))
            ->join('prize_draw_days', 'results.day_id', '=', 'prize_draw_days.id')
            ->where('prize_draw_days.date', '<=', $end)
            ->where('prize_draw_days.date', '>=', $start)
            ->groupBy('first_number')
            ->orderBy('total', 'DESC')
            //->get()
            ->limit(8)
            ->pluck('first_number')
            ->toArray();

        $dataLastNumber = Result::query()
            ->select(DB::raw('last_number, count(*) as total'))
            ->join('prize_draw_days', 'results.day_id', '=', 'prize_draw_days.id')
            ->where('prize_draw_days.date', '<=', $end)
            ->where('prize_draw_days.date', '>=', $start)
            ->groupBy('last_number')
            ->orderBy('total', 'DESC')
            //->get()
            ->limit(8)
            ->pluck('last_number')
            ->toArray();

        $numbers = [];
        
        foreach ($dataFirstNumber as $first) {
            foreach ($dataLastNumber as $last) {
                $numbers[] = $first.$last;
            }
        }
        dd(implode(',', $numbers), $end, $start, $dataFirstNumber , $dataLastNumber);
        $currentDay = $endObj->addDay();
        $isExist = PrizeDrawDay::query()
                    ->join('results', 'prize_draw_days.id', '=', 'results.day_id')
                    ->whereDate('prize_draw_days.date', $currentDay)
                    ->whereIn('results.loto', $numbers)
                    ->where('results.level_id', 1)
                    ->first();
        return [
            'day' => $currentDay->toDateString(),
            'status' => !empty($isExist),
            'value' => !empty($isExist) ? $isExist->loto: null
        ];
    }
}
