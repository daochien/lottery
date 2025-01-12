<?php

namespace App\Console\Commands;

use App\Models\PrizeDrawDay;
use Carbon\Carbon;
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
//        $number = "456789";
//        $results = [];
//        $this->combineNumbers("", $number, $results);
//        dd(implode(',', $results));
        $this->soi_cau_lo_tam_giac('2025-01-01', '2025-01-31');

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

    public function soi_cau_lo_tam_giac($start, $end)
    {
        $dates = PrizeDrawDay::query()
            ->with('results')
            ->where('date', '>=', $start)
            ->where('date', '<=', $end)
            ->orderBy('date')
            ->get();
        $list = [];

        foreach ($dates as $item) {
            foreach ($item->results as $k => $ite) {
                if ($k == 0) {
                    $a = $this->getMiddleNumber($ite->value);
//                    $a = $this->sumOfDigits($ite->value);
//                    $a = $this->getLastNumber($a);
                }
                if ($k == 23) {
                    $b = $this->getFirstNumber($ite->value);
//                    $b = $this->sumOfDigits($ite->value);
//                    $b = $this->getLastNumber($b);
                }
                if ($k == 26) {
                    $c = $this->getLastNumber($ite->value);
//                    $c = $this->sumOfDigits($ite->value);
//                    $c = $this->getLastNumber($c);
                }

                if (isset($a) && isset($b) && isset($c)) {
                    $kq1 = $a.$b;
                    $kq2 = $c.$a;
//                    $kq3 = $a1.$b;
//                    $kq4 = $c.$a1;

                    //kiem tra xem ket qua co o ngay hom sau hay khong
                    $total = PrizeDrawDay::query()->join('results', 'prize_draw_days.id', '=', 'results.day_id')
                    ->whereIn('loto', [$kq1, $kq2])
                    ->whereIn('date', [
                        Carbon::parse($item->date)->addDay()->toDateString(),
//                        Carbon::parse($item->date)->addDays(2)->toDateString(),
//                        Carbon::parse($item->date)->addDays(3)->toDateString(),
                    ])
                    ->count();

                    $list[$item->date] = [
                        'next_date' => Carbon::parse($item->date)->addDay()->toDateString(),
                        'kq1' => $kq1,
                        'kq2' => $kq2,
                        'total' => $total
                    ];
                }
            }
        }
        dd($list);
    }

    public function getMiddleNumber($number) {
        $numberStr = (string)$number;
        // Lấy ký tự đầu tiên
        return $numberStr[2];
    }

    public function getFirstNumber($number) {
        // Chuyển đổi số thành chuỗi để dễ dàng xử lý
        $numberStr = (string)$number;

        // Lấy ký tự đầu tiên
        return $numberStr[0];
    }

    public function getLastNumber($number) {
        // Chuyển đổi số thành chuỗi để dễ dàng xử lý
        $numberStr = (string)$number;

        // Lấy ký tự cuối cùng
        return $numberStr[strlen($numberStr) - 1];
    }

    public function sumOfDigits($number) {
        $numberStr = (string)$number;
        $sum = 0;
        for ($i = 0; $i < strlen($numberStr); $i++) {
            $sum += (int)$numberStr[$i];
        }

        return $sum;
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
}
