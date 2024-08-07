<?php
require __DIR__ . '/inc/functions.inc.php'; 
$contents = file_get_contents(__DIR__ . '/../data/index.json');
$cities = json_decode($contents, true);
// echo "<pre>";
// var_dump($data);
// echo "</pre>";

?>
<?php require __DIR__ . '/views/header.inc.php'; ?>
<ul>
    <?php foreach($cities as $city):?>
        <li>
            <a href="city.php?<?php echo http_build_query(['city'=>$city['city']]);?>">
                <?php echo e($city['city']);?>
                <?php echo e($city['country']);?>
                (<?php echo e($city['flag']);?>)
            </a>
        </li>
    <?php endforeach;?>
    
</ul>


<?php require __DIR__ . '/views/footer.inc.php'; ?>