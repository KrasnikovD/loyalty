<?php
namespace App\Exports;

use App\Models\DataHelper;
use Maatwebsite\Excel\Concerns\FromArray;

class SalesMigrations implements FromArray
{
    private $dateBegin1;
    private $dateBegin2;
    private $dateEnd1;
    private $dateEnd2;
    private $outletIds;
    private $onlyLosses;

    public function __construct($dateBegin1, $dateBegin2, $dateEnd1, $dateEnd2, $outletIds, $onlyLosses = false)
    {
        $this->dateBegin1 = $dateBegin1;
        $this->dateBegin2 = $dateBegin2;
        $this->dateEnd1 = $dateEnd1;
        $this->dateEnd2 = $dateEnd2;
        $this->outletIds = $outletIds;
        $this->onlyLosses = $onlyLosses;
    }

    public function array(): array
    {
        $data = DataHelper::collectSalesMigrationsInfo($this->dateBegin1, $this->dateBegin2, $this->dateEnd1, $this->dateEnd2, $this->outletIds, $this->onlyLosses);
        $exportData = [
            ['name' => "Имя клиента",
            'outlet_name' => "Название магазина",
            'period_1_count' => "Период №1",
            'period_2_count' => "Период №2",
            'diff' => "Разница",],
            ['name' => "", 'outlet_name' => "", 'period_1_count' => "", 'period_2_count' => "", 'diff' => "",]
        ];
        foreach ($data as $datum) {
            $isFirst = true;
            foreach ($datum['outlets'] as $item) {
                $name = '';
                if ($isFirst) {
                    $name = $datum['name'] . ' (' . $datum['phone'] . ')';
                }
                $exportData[] = [
                    'name' => $name,
                    'outlet_name' => $item['name'],
                    'period_1_count' => strval($item['period_1_count']),
                    'period_2_count' => strval($item['period_2_count']),
                    'diff' => strval($item['diff']),
                ];
                $isFirst = false;
            }
            $exportData[] = ['name' => "", 'outlet_name' => "", 'period_1_count' => "", 'period_2_count' => "", 'diff' => "",];
        }
        return $exportData;
    }
}
