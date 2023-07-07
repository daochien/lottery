<?php

namespace App\Console\Commands;



use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Goutte\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;


class CrawlerCommand extends Command
{
    public array $items;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crawler-command';

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
        $endStr = $end->toDateTimeString();
        $start = $end->subDays(100);
        $period = CarbonPeriod::create($start->toDateTimeString(), $endStr);
        $urls  = [];
        foreach ($period as $date) {
            $day = $date->day;
            $month = $date->month;
            $year = $date->year;
            $key = "{$day}-{$month}-{$year}";
            $urls[$key] = "https://xsmn.mobi/xsmb-{$day}-{$month}-{$year}.html";
        }

        foreach ($urls as $key => $url) {
            $response = $this->_doCrawl($key, $url);
        }

//        $url = 'https://xsmn.mobi/xsmb-6-7-2023.html';
//        $client = new Client();
//        $crawler = $client->request('GET', $url);
//
//        $crawler->filter('table.kqmb tbody tr')->each(function (Crawler $node) {
//            $name = $node->filter('td.txt-giai')->text();
//            $value = $node->filter('td.v-giai ')->text();
//            $wholeStar = $node->filter('td span.number')->count();
//            if ($wholeStar > 1) {
//                $values = $node->filter('td span.number')->each(function (Crawler $node) {
//                    return $node->text();
//                });
//                $this->items[$name] = $values;
//            } else {
//                $this->items[$name] = [$value];
//            }
//        });
//
//        $data = array_values($this->items);
//        unset($data[0]);
//        dd($data);
    }

    protected function _doCrawl($key, $url)
    {
//        $prizeDay = DB::table('prize_draw_days')->where('key', $key)->first();
//        if ($prizeDay) {
//            return false;
//        }
        //$prizeDay = DB::table('prize_draw_days')->store
        $client = new Client();
        $crawler = $client->request('GET', $url);

        $crawler->filter('table.kqmb tbody tr')->each(function (Crawler $node) {
            $name = $node->filter('td.txt-giai')->text();
            $value = $node->filter('td.v-giai ')->text();
            $wholeStar = $node->filter('td span.number')->count();
            if ($wholeStar > 1) {
                $values = $node->filter('td span.number')->each(function (Crawler $node) {
                    return $node->text();
                });
                $this->items[$name] = $values;
            } else {
                $this->items[$name] = [$value];
            }
        });

        $data = array_values($this->items);
        dd($key,$data);
        unset( $data[0]);
    }

    protected function _getFirstNumber($num) : int
    {
        return (int)substr((string)$num, 0, 1);
    }

    protected function _getLastNumber($num) : int
    {
        return $num % 10;
    }
}
