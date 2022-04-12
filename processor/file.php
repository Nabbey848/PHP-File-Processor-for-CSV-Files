<?php

namespace Processor;

class File
{
    protected $uploadedFile;
    protected $convertedData;
    protected $convertedDataWithTotals;
    protected $dataWithFooterAvgs;
    protected $dataWithCadTotals;

    public function __construct($uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    public function convertToArray()
    {
        $rows = array_map('str_getcsv', file($this->uploadedFile));

        $header = array_shift($rows);

        $header = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header);

        $csv = array();

        foreach($rows as $row)
        {
            $csv[]= array_combine($header, $row);
        }

        
        //var_dump($csv);
        $this->convertedData = $csv;
    }

    public function getConvertedData(){
        $this->convertToArray();
        return $this->convertedData;
    }

    public function calculateTotals()
    {
        $this->convertToArray();

        $arr = $this->convertedData;

        
        $profitMarginTitle = 'Profit Margin';
        $totalProfitTitle = 'Total Profit (USD)';
        
        for($i = 0; $i < sizeof($arr); $i++)
        {
            $price = $arr[$i]['Price'];
            $cost = $arr[$i]['Cost'];
            $qty = $arr[$i]['QTY'];

            $revenue = $price * $qty;
            $operatingCost = $cost * $qty;
            $totalProfit = $revenue - $operatingCost;
        

            $profitMargin = $totalProfit / $revenue;
            $profitMargin = number_format($profitMargin, 2);
            

            $arr[$i] += [$profitMarginTitle => $profitMargin];
            $arr[$i] += [$totalProfitTitle => $totalProfit];
        }

        $this->convertedDataWithTotals = $arr;
    }

    public function getCalculatedTotals()
    {
        $this->calculateTotals();

        return $this->convertedDataWithTotals;
    }

    public function generateFooterData()
    {
        $arr = $this->getCalculatedTotals();

        $footerAvgCostTitle = 'Average Cost';
        $footerAvgPriceTitle =  'Average Price';
        $footerTotalQtyTitle = 'Total QTY';
        $footerAvgProfMarTitle = 'Average Profit Margin';
        $footerTotalProfitTitle = 'Total Profit (USD)';

        $avgCost = [];
        $avgPrice = [];
        $totalQty = [];
        $avgProfitMargin = [];
        $totalProfit = [];
        $productCount = sizeof($arr);
        $dataWithFooterAvgs = [];

        for ($i = 0; $i < $productCount; $i++) 
        {
            $avgCost[] = $arr[$i]['Cost'];
            $avgPrice[] = $arr[$i]['Price'];
            $totalQty[] = $arr[$i]['QTY'];
            $avgProfitMargin[] = $arr[$i]['Profit Margin'];
            $totalProfit[] = $arr[$i]['Total Profit (USD)'];
        }

        if ($productCount !== 0) {

            $finalAvgCost = array_sum($avgCost);
            $finalAvgCost = $finalAvgCost / $productCount;
            $finalAvgCost = number_format($finalAvgCost, 2);

            $finalAvgPrice = array_sum($avgPrice);
            $finalAvgPrice = $finalAvgPrice / $productCount;
            $finalAvgPrice = number_format($finalAvgPrice, 2);

            $finalAvgProfitMargin = array_sum($avgProfitMargin);
            $finalAvgProfitMargin = $finalAvgProfitMargin / $productCount;
            $finalAvgProfitMargin = number_format($finalAvgProfitMargin, 2);

            $finalTotalQty = array_sum($totalQty);
            $finalTotalProfit = array_sum($totalProfit);
            $finalTotalProfit = intval($finalTotalProfit);
            
            $dataWithFooterAvgs += [$footerAvgCostTitle => $finalAvgCost];
            $dataWithFooterAvgs += [$footerAvgPriceTitle => $finalAvgPrice];
            $dataWithFooterAvgs += [$footerTotalQtyTitle => $finalTotalQty];
            $dataWithFooterAvgs += [$footerAvgProfMarTitle => $finalAvgProfitMargin];
            $dataWithFooterAvgs += [$footerTotalProfitTitle => $finalTotalProfit];

            array_push($arr, $dataWithFooterAvgs);

            $this->dataWithFooterAvgs = $arr;
        }
    }

    public function getProcessedDataWithFooter()
    {
        $this->generateFooterData();
        return $this->dataWithFooterAvgs;
    }
    
    private function convertCurrency()
    {
        $arr = $this->getProcessedDataWithFooter();
        $totalProfitCadTitle = 'Total Profit (CAD)';

        $apikey = 'c073bb4ca5c60505ccc0';
        $from_currency = 'USD';
        $to_currency = 'CAD';

        $from_Currency = urlencode($from_currency);
        $to_Currency = urlencode($to_currency);
        $query =  "{$from_Currency}_{$to_Currency}";

        $json = file_get_contents("https://free.currconv.com/api/v7/convert?q={$query}&compact=ultra&apiKey={$apikey}");
        $obj = json_decode($json, true);

        $val = floatval($obj["$query"]);

        for($i = 0; $i < sizeof($arr); $i++)
        {
            $amountAsUsd = $arr[$i]['Total Profit (USD)'];
            $amountAsUsd = floatval($amountAsUsd);
            $totalAsCad = $val * $amountAsUsd;

            $amountAsUsd = number_format($amountAsUsd, 2);
            $totalAsCad = number_format($totalAsCad, 2);

            $arr[$i]['Total Profit (USD)'] = $amountAsUsd;
            $arr[$i] += [$totalProfitCadTitle => $totalAsCad];
            
        }
        

        $this->dataWithCadTotals = $arr;
    }

    public function getFullyProcessedData()
    {
        $this->convertCurrency();
        return $this->dataWithCadTotals;
    }
}



