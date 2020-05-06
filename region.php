<?php
require 'vendor/autoload.php';
 
use UbimetTask\Connection as Connection;
use UbimetTask\StockDB as StockDB;
use UbimetTask\Calculation as Calculation;

     try {
       if (isset( $_POST['submit']) && is_int((int)$_POST["region"]) ) { 
            // connect to the PostgreSQL database
            $gid=(int)$_POST["region"];
            $pdo = Connection::get()->connect();
            // 
            $stockDB = new StockDB($pdo);
             // get all data
            $stocks = $stockDB->value_points($_POST["region"]);
            $bundesland = $stockDB->headline_Region($gid);
            $points = $stockDB->find_Region_points($gid);
         }
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
        
        <title>SELECTED REGION</title>
    </head>
    <body>
        <div class="container my-3">
                <?php //Selected region as headline
                    foreach ($bundesland as $layer) {
                        ?>
                        <h1><?php echo htmlspecialchars($layer["land"]); ?></h1>
                    <?php } 
                ?>
          
                <?php //Creating array with points values
                     $array1 = [];
                     foreach ($stocks as $value) {
                        array_push($array1, $value["value"]);
                    }
                    $calculations = new Calculation();
                    $calculations->setArray($array1);
                ?>
            
        </div>
        
        <div class="container">
            <p>Based on the selected region, their points and values:</p>
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>MAX VALUE</th>
                        <th>MIN VALUE</th>
                        <th>MEDIAN</th> 
                        <th>AVERAGE</th> 
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo number_format(max($array1), 2); ?></td>
                        <td><?php echo number_format(min($array1), 2); ?></td>
                        <td><?php echo number_format($calculations->calculate_median($array1), 3); ?></td>
                        <td><?php echo number_format($calculations->calculate_average($array1), 3); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div id="map" class="map container">
            <p>Values of points <span class="badge badge-pill badge-danger">below</span> median</p>
            <p>Values of points <span class="badge badge-pill badge-success">equal or above</span> median <a href="index.php" class="link text-muted" data-toggle="tooltip" data-placement="top" title="New selection">Select the region again</a></p>
        </div>

    <script>
    // EPSG:3416 for Austria
    proj4.defs("EPSG:3416","+proj=lcc +lat_1=49 +lat_2=46 +lat_0=47.5 +lon_0=13.33333333333333 +x_0=400000 +y_0=400000 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs");
    ol.proj.proj4.register(proj4);

    // Basic Map
    var map = new ol.Map({
        target: 'map',
        layers: [
        new ol.layer.Tile({
            source: new ol.source.OSM()
        })
        ],
        view: new ol.View({
        center: [494361.537557676, 415179.02708263986],
        zoom: 7.6,
        maxZoom: 11,
        minZoom: 4,
        projection: 'EPSG:3416' 
        })
    })

    //First group of points - below the median
    var multipoint_feature = new ol.Feature({
        geometry: new ol.geom.MultiPoint(
            [<?php 
                $numOfPoints = count($points);
                $numCount = 0;

                foreach ($points as $dot) {
                   if($dot["value"]<$calculations->calculate_median($array1)){
                    $text = str_replace("(", "[", $dot["coordinates"]);
                    $text1 = str_replace(")", "]", $text);
                    $text2 = str_replace(" ", ",", $text1);
                    echo str_replace("POINT", "", $text2);
                    $numCount = $numCount +1;
                        if($numCount<$numOfPoints){
                            echo ", ";
                        }
                        
                    }
                }
            ?>]
        )
    });

    //Second group of points - above the median
    var multipoint_feature1 = new ol.Feature({
        geometry: new ol.geom.MultiPoint(
            [<?php 
                $numOfPoints = count($points);
                $numCount = 0;

                foreach ($points as $dot) {
                   if($dot["value"]>=$calculations->calculate_median($array1)){
                    $text = str_replace("(", "[", $dot["coordinates"]);
                    $text1 = str_replace(")", "]", $text);
                    $text2 = str_replace(" ", ",", $text1);
                    echo str_replace("POINT", "", $text2);
                    $numCount = $numCount +1;
                        if($numCount<$numOfPoints){
                            echo ", ";
                        }        
                    }
                }
            ?>]
        )
    });

    //Borders of region
    var linestring_feature = new ol.Feature({
        geometry: new ol.geom.LineString(
            [<?php 
                $border = $bundesland[0]["transform_geom"];
                $border1 = str_replace(")))", "]", $border);
                $border2 = str_replace("MULTIPOLYGON(((", "[", $border1);
                $border3 = str_replace(",", "],[", $border2);
                $border4 = str_replace(" ", ",", $border3);
                $border5 = str_replace(')],[(', '],[', $border4);
                $border6 = str_replace(')', '', $border5);
                 echo str_replace('(', '', $border6);                               
            ?>]
        )
    })

    const fillStyle = new ol.style.Fill({
        color: [40, 119, 247, 1]
    })

    const strokeStyle = new ol.style.Stroke({
        color: [30, 30, 31, 0.9],
        width: 1.2
    })

    //style for the first group of points
    const pointStyle1 =  new ol.style.RegularShape({
        fill: new ol.style.Fill({
            color: [255, 0, 0]
        }),
        stroke: strokeStyle, 
        points: 4,
        radius: 5
    })

    //style for the second group of points
    const pointStyle2 =  new ol.style.RegularShape({
        fill: new ol.style.Fill({
            color: [0, 255, 0]
        }),
        stroke: strokeStyle, 
        points: 4,
        radius: 5
    })
                    
    //Layers
    var vector_layer = new ol.layer.Vector({
              source: new ol.source.Vector({
              features: [multipoint_feature, linestring_feature]
              }),
              style: new ol.style.Style({
                fill: fillStyle,
                stroke: strokeStyle,
                image: pointStyle1  
              })
                
    }) 

    var vector_layer1 = new ol.layer.Vector({
              source: new ol.source.Vector({
              features: [multipoint_feature1]
              }),
              style: new ol.style.Style({
                fill: fillStyle,
                stroke: strokeStyle,
                image: pointStyle2  
              })
                
    })
        
    map.addLayer(vector_layer);
    map.addLayer(vector_layer1);

    </script>
    </body>
</html>