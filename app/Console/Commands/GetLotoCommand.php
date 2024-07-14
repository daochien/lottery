<?php

namespace App\Console\Commands;

use App\Models\PrizeDrawDay;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Phpml\Classification\Ensemble\RandomForest;
use Phpml\NeuralNetwork\ActivationFunction\Sigmoid;
use Phpml\NeuralNetwork\Layer;
use Phpml\NeuralNetwork\Training\Backpropagation;
use Phpml\Regression\LeastSquares;
use Phpml\NeuralNetwork\Network\MultilayerPerceptron;

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
        $days = PrizeDrawDay::query()->where([
            ['date', '<=', '2024-07-14']
        ])->get();

        $listResults = [];
        $totalTrue = 0;
        $totalFalse = 0;
        foreach ($days as $day) {

            $result = Result::query()->whereIn('level_id', [1, 2, 3])->where('day_id', $day->id)->get();
            $a = 0;
            $b = 0;
            $c = 0;
            $d = 0;
            foreach ($result as $item) {
                if ($item->level_id == 1) {
                    $a = $item->value;
                }
                if ($item->level_id == 2) {
                    $b = $item->value;
                }
                if ($item->level_id == 3) {
                    if ($c == 0) {
                        $c = $item->value;
                    } else {
                        $d = $item->value;
                    }
                }
            }

            if (empty($a) || empty($b)) {
                continue;
            }
//
//            $loto = $this->solveProblem($b, $a);
//
//            if ((int)$loto < 10) {
//                $loto = "0".$loto;
//            }

            $number = $a.$b.$c.$d;
            $array = str_split($number);
            $array = array_map('intval', $array);

            $loto = array_sum($array);
            if ($loto >= 100) {
                $loto = $this->solveProblem(0, 0, $loto);
            }
            $loto2 = strrev($loto);

            //kiem tra kết quả có ở ngày tiếp theo không?
            $nextDay = Carbon::parse($day->date)->addDay()->toDateString();

            $nextPrizeDrawDay = PrizeDrawDay::query()->where('date', $nextDay)->first();
            if (!$nextPrizeDrawDay) {
                continue;
            }

            $result1 = Result::query()->where('day_id', $nextPrizeDrawDay->id)->where('loto', $loto)->count();

            $result2 = Result::query()->where('day_id', $nextPrizeDrawDay->id)->where('loto', $loto2)->count();
            $isWin = !empty($result1) || !empty($result2);
            $listResults[] = [
                'old_date' => $day->date,
                'loto' => $loto,
                'result' => $isWin,
                'next_day' => $nextDay
            ];
            if (!empty($isWin)) {
                $totalTrue++;
            } else {
                $totalFalse++;
            }
        }
        dd($totalTrue, $totalFalse, $listResults);

    }

    public function solveProblem($specialPrize, $firstPrize, $total = 0) {
        if ($total == 0) {
            $combinedNumber = sprintf('%05d%05d', $specialPrize, $firstPrize); // Ghép thành một chuỗi số 10 chữ số

            // Bước 2: Tính theo công thức Pascal
            $currentNumber = str_split($combinedNumber); // Chuyển chuỗi thành mảng các ký tự
        } else {
            $currentNumber = str_split($total); // Chuyển chuỗi thành mảng các ký tự
        }

        while (true) {
            $nextNumber = [];
            for ($i = 0; $i < count($currentNumber) - 1; $i++) {
                $sum = (int)$currentNumber[$i] + (int)$currentNumber[$i + 1];
                if ($sum >= 10) {
                    $sum = $sum % 10;
                }
                $nextNumber[] = $sum;
            }
            $currentNumber = $nextNumber;

            $lastNumber = (int)implode('', $currentNumber);
            if ($lastNumber < 100) {
                break;
            }
        }

        // Kết quả là số cuối cùng còn lại
        return implode('', $currentNumber);
    }

    protected function get64Number()
    {
        return $this->_reportResult(1);
    }

    protected function _reportResult(int $subDay) : array
    {
        //$subDay = 2;
        $endObj = Carbon::now()->subDays($subDay);
        $endOb = Carbon::now()->subDays($subDay);
        $end = Carbon::now()->subDays($subDay)->toDateString();
        $start = $endOb->subDays(30)->toDateString();

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
            'value' => !empty($isExist) ? $isExist->loto: null,
            'date_start' => $start,
            'date_end' => $end,
            'numbers_random' => json_encode($numbers),
        ];
    }
}
