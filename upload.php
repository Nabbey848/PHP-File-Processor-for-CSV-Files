<?php
require 'processor/file.php';

use Processor\File;

if (isset($_POST["submit"])) {

  $uploadedFile = new File($_FILES['csvFileToUpload']['tmp_name']); 

}
$dataCheck = $uploadedFile->getConvertedData();

?>

<?php if(!empty($dataCheck)): ?>
<?php $convertedFileWithTotals = $uploadedFile->getFullyProcessedData();?> 

<style> 
table, th, td { 
  border: 1px solid black;
  text-align: center;
  }
  
</style>
<table>
  <tr>
    <th>SKU</th>
    <th>Price</th>
    <th>Cost</th>
    <th>QTY</th>
    <th>Profit Margin</th>
    <th>Total Cost (USD)</th>
    <th>Total Cost (CAD)</th>
  </tr>
  <?php for($i = 0; $i < sizeof($convertedFileWithTotals) - 1; $i++): ?>
  <tr>
    <td><?=$convertedFileWithTotals[$i]['SKU'] ?></td>
    <td>$<?=number_format($convertedFileWithTotals[$i]['Price'], 2)?></td>
    <td>$<?=number_format($convertedFileWithTotals[$i]['Cost'], 2)?></td>

    <?php
    $valQty = $convertedFileWithTotals[$i]['QTY'];
    $valProfMar = $convertedFileWithTotals[$i]['Profit Margin'];
    $valTotalUsd = $convertedFileWithTotals[$i]['Total Profit (USD)'];
    $valTotalCad = $convertedFileWithTotals[$i]['Total Profit (CAD)'];
  
    $valQty >= 0 ? $colorQty = "color: green;" : $colorQty = "color: red;";
    $valProfMar >= 0 ? $colorProfMar = "color: green;" : $colorProfMar = "color: red;";
    $valTotalUsd >= 0 ? $colorTotalUsd = "color: green;" :  $colorTotalUsd = "color: red;";
    $valTotalCad >= 0 ? $colorTotalCad = "color: green;" : $colorTotalCad = "color: red;";    
    ?>
    
    <td style='<?=$colorQty?>'><?=$convertedFileWithTotals[$i]['QTY'] ?></td>
    <td style='<?=$colorProfMar ?>'><?=$convertedFileWithTotals[$i]['Profit Margin'] ?></td>
    <td style='<?=$colorTotalUsd?>'>$<?=$convertedFileWithTotals[$i]['Total Profit (USD)'] ?></td>
    <td style='<?=$colorTotalCad?>'>$<?=$convertedFileWithTotals[$i]['Total Profit (CAD)'] ?></td>
  </tr>
  <?php endfor; ?>
  <tr style='height: 15px;'></tr>
  <tr>
    <th>Average Price</th>
    <th>Average Cost</th>
    <th>Total QTY</th>
    <th>Average Profit Margin</th>
    <th>Total Profit (USD)</th>
    <th>Total Profit (CAD)</th>
  </tr>
  
  <?php 
  $footerData = end($convertedFileWithTotals);

  $footValQty = $footerData['Total QTY'];
  $footValProfMar = $footerData['Average Profit Margin'];
  $footValTotalUsd = $footerData['Total Profit (USD)'];
  $footValTotalCad = $footerData['Total Profit (CAD)'];

  $footValQty >= 0 ? $footColorQty = "color: green;" : $footColorQty = "color: red;";
  $footValProfMar >= 0 ? $footColorProfMar = "color: green;" : $footColorProfMar = "color: red;";
  $footValTotalUsd >= 0 ? $footColorTotalUsd = "color: green;" : $footColorTotalUsd = "color: red;";
  $footValTotalCad >= 0 ? $footColorTotalCad = "color: green;" : $footColorTotalCad = "color: red;";
  ?>
  <tr>
    <td>$<?=$footerData['Average Price']?></td>
    <td>$<?=$footerData['Average Cost']?></td>
    <td style='<?=$footColorQty?>'><?=$footerData['Total QTY']?></td>
    <td style='<?=$footColorProfMar?>'><?=$footerData['Average Profit Margin']?></td>
    <td style='<?=$footColorTotalUsd?>'>$<?=$footerData['Total Profit (USD)']?></td>
    <td style='<?=$footColorTotalCad?>'>$<?=$footerData['Total Profit (CAD)']?></td>
  </tr>
</table>
<?php else :?>
  <h1> UH OH...</h1>
  <h2>You have submitted a CSV file with only headers, but no data. Please reupload a the file with data.</h2>
  <a href='/app.html'><button>Go Back To Submission Page</button></a>
<?php endif; ?>













