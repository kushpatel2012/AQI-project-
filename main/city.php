<?php
require __DIR__ . '/inc/functions.inc.php'; 
$city=NULL;
    if(!empty($_GET['city'])){
    $city=$_GET['city'];
}
$filename=NULL;
$cityInformation=[];
if(!empty($city)){
    $contents = file_get_contents(__DIR__ . '/../data/index.json');
    $cities = json_decode($contents, true);
    foreach($cities as $value){
        if($value['city']==$city){
            $filename=$value['filename'];
            $cityInformation=$value;
            break;
        }
    }
}
if(!empty($filename)){
    $results=json_decode(file_get_contents('compress.bzip2://'.__DIR__.'/../data/'.$filename),true)['results'];
    $units=[
        "pm25"=>null,
        "pm10"=>null,
    ];
}
$pm25=false;
$pm10=false;
foreach ($results as $result) {
        if ($result["parameter"] === "pm10") {
            $pm10=true;
        } 
}
foreach ($results as $result) {
    if ($result["parameter"] === "pm25") {
        $pm25=true;
    } 
}


    if ($pm25===true && $pm10===true){
        foreach($results as $result){
            if(!empty($units['pm25'])  && !empty($units['pm10'])) break;
            if($result["parameter"]==="pm25"){
                $units['pm25']=$result["unit"];
            }
            if($result["parameter"]==="pm10"){
                $units['pm10']=$result["unit"];
            }
        }
    }
    if($pm25===true){
        foreach($results as $result){
            if(!empty($units['pm25'])) break;
            if($result["parameter"]==="pm25"){
                $units['pm25']=$result["unit"];
            }
        }
    }
    if( $pm10=== true){
        foreach($results as $result){
            if(!empty($units['pm10'])) break;
            if($result["parameter"]==="pm10"){
                $units['pm10']=$result["unit"];
            }
        }
    }
    $stats=[];
    foreach($results as $result){
        if($result["parameter"]!=="pm25" && $result["parameter"]!=="pm10") continue;
        if($result["value"]<0) continue;
        $month=substr($result["date"]['local'],0,7);
        if(!isset($stats[$month])){
            $stats[$month]=[
                "pm25"=>[],
                "pm10"=>[],

            ];
        }
        $stats[$month][$result["parameter"]][]=$result["value"];
    }
    // echo "<pre>";
    // var_dump($stats);
    // echo "</pre>";


?>
<?php require __DIR__ . '/views/header.inc.php'; ?>
<?php if(empty($city)): ?>
    <h1>Please select a city</h1>
<?php else:?>
    <?php echo $cityInformation['city'];?>
    <?php if(!empty($stats)): ?>
        <canvas id="aqi-chart" style="width: 300px; height: 200px;"></canvas>
        <script src="scripts/chart.umd.js"></script>
        <?php
            $labels=array_keys($stats);
            sort($labels);
            $pm25label=[];
            $pm10label=[];
            foreach($labels as $label) {
                $mesurment=$stats[$label];
                if(count($mesurment["pm25"])>0){
                    $pm25label[]=array_sum($mesurment["pm25"])/count($mesurment["pm25"]);
                   }
                   else{
                       $pm25label[]=0;
                   }
               
            }
            foreach($labels as $label) {
                $mesurment=$stats[$label];
                if(count($mesurment["pm10"])>0){
                 $pm10label[]=array_sum($mesurment["pm10"])/count($mesurment["pm10"]);
                }
                else{
                    $pm10label[]=0;
                }
            }
            $datsets=[];
            if(array_sum($pm25label)>0){
                $datsets[]=[
                    'label'=>"AQI PM 2.5 in {$units["pm25"]}",
                    'data'=>$pm25label,
                    'fill'=>false,
                    'borderColor'=>'rgb(75, 192, 192)',
                    'tension'=>0.1
                    ];
            }
            if(array_sum($pm10label)>0){
                $datsets[]=[
                    'label'=>"AQI PM 10 in {$units["pm10"]}",
                    'data'=>$pm10label,
                    'fill'=>false,
                    'borderColor'=>'rgb(192, 75, 192)',
                    'tension'=>0.1
                ];
            }
           ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('aqi-chart');
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($labels) ;?> ,
                        datasets: <?php echo json_encode($datsets) ;?> ,
                    }
                });
            });
        </script>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>PM2.5</th>
                    <th>PM10</th>
                </tr>
            </thead>
            <thead>
            <tbody>
            <?php foreach ($stats as $month => $value):?>
                <tr>
                    <th><?php echo e($month);?></th>
                    <td>
                        <?php if(count($value["pm25"])>0):?>
                            <?php echo e(round(array_sum($value["pm25"])/count($value["pm25"]),2));?>
                            <?php echo e($units['pm25']);?>
                        <?php else:?>
                            <?php echo "data not found";?>
                        <?php endif;?>
                    </td>
                    <td>
                        <?php if(count($value["pm10"])>0):?>
                            <?php echo e(round(array_sum($value["pm10"])/count($value["pm10"]),2));?>
                            <?php echo e($units['pm10']);?>
                        <?php else:?>
                            <?php echo "data not found";?>
                        <?php endif;?>
                    </td>
                     
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
<?php endif; ?>

<?php require __DIR__ . '/views/footer.inc.php'; ?>