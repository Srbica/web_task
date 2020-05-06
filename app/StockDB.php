<?php
 
namespace UbimetTask;

class StockDB {

	/*
     * PDO object
     * @var \PDO
     */
    private $pdo;
 
    /*
     * init the object with a \PDO object
     * @param type $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
	
  	public function select_Region() {
          $stmt = $this->pdo->query('SELECT gid, name, the_geom '
                  . 'FROM regions '
                  . 'ORDER BY name');
          $regions = [];
          while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
              $regions[] = [
                  'gid' => $row['gid'],
                  'name' => $row['name'],
                  'the_geom' => $row['the_geom']
              ];
          }
          return $regions;
    }

    public function headline_Region($gid) {
            // prepare SELECT statement
            $stmt = $this->pdo->prepare('SELECT distinct name land, ST_AsText(st_transform(regions.the_geom,3416)) as transform_geom
                                      from regions 
                                      where regions.gid=:gid');
            // bind value to the :id parameter
            $stmt->bindValue(':gid', $gid);
            // execute the statement
            $stmt->execute();
            // return the result set as an object
            $layer = [];
          while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
              $layer[] = [
                  'land' => $row['land'],
                  'transform_geom' => $row['transform_geom']
              ];
          }
            return $layer;
    }

    public function find_Region_points($gid) {
     $stmt = $this->pdo->prepare('SELECT distinct ST_AsText(st_transform(selection.the_geom,3416)) as coordinates, selection.value
                                from points,
                                lateral(
                                select points.the_geom, points.value from points, regions 
                                where (ST_Intersects(ST_PointOnSurface(points.the_geom), regions.the_geom) AND regions.gid=:gid)
                              ) as selection');
        $stmt->bindValue(':gid', $gid);
        $stmt->execute();
        $newregion = $stmt->fetchAll();
        return $newregion;
    }

    public function value_points($gid){
        $stmt = $this->pdo->prepare ('SELECT points.value as value
                                      FROM points, regions 
                                      WHERE (ST_Intersects(ST_PointOnSurface(points.the_geom), regions.the_geom) AND regions.gid=:gid)');
        $stmt->bindValue(':gid', $gid);
        $stmt->execute();
        $newpoints = $stmt->fetchAll();
        return $newpoints; 
      }               
 }             