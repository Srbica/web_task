<?php
require 'vendor/autoload.php';
 
use UbimetTask\Connection as Connection;
use UbimetTask\StockDB as StockDB;
use UbimetTask\Calculation as Calculation;
 
try {
    // connect to the PostgreSQL database
    $pdo = Connection::get()->connect();
    // 
    $stockDB = new StockDB($pdo);
    // get all stocks data
    $stocks = $stockDB->select_Region();
} catch (\PDOException $e) {
    echo $e->getMessage();
}

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.3.0/css/ol.css" type="text/css">
        <link rel="stylesheet" type="text/css" href="styles.css"> 
        <link rel="stylesheet" href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
        
        <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.3.0/build/ol.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.6.1/proj4-src.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.6.1/proj4.js"></script>

        <title>WEB TASK</title>
    </head>
    <body>
        <div class="container p-4">
            <img src="./img/logo1.jpg" class="mx-auto d-block">
        </div>
        <div class="jumbotron text-center p-4 my-3 bg-dark text-white">
            <h1>Welcome to the new application</h1>
            <p>THE BEST INFORMATION FOR THE RIGHT BUSINESS DECISIONS</p>
        </div>
        <div class="container text-center p-2 my-3 bg-warning text-white">
            <h3>Please select one of the Austrian regions below</h3>
        </div>
        
        <div class="container">
            <form method="POST" action="region.php">
                <select id="region" name="region" class="custom-select custom-select-lg mb-3">
                <?php 
                foreach ($stocks as $regions) : ?>
                    <option value="<?php echo $regions['gid']; ?>"><?php echo htmlspecialchars($regions['name']); ?></option>
                <?php endforeach; ?>
                </select>
            <input type="submit" class="btn btn-primary mb-2" name="submit" value="Select">
            </form>
        </div> 
    </body>
</html>