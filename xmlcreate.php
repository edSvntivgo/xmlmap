<?php
// Get parameters from URL
$center_lat = $_GET["latitud"];
$center_lng = $_GET["longitud"];
$radius = $_GET["radius"];

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("os_anuncios");
$parnode = $dom->appendChild($node);

// Opens a connection to a mySQL server
$connection=mysql_connect ('localhost', 'root','');
if (!$connection) {
  die("Not connected : " . mysql_error());
}

// Set the active mySQL database
$db_selected = mysql_select_db('osmexico', $connection);
if (!$db_selected) {
  die ("Can\'t use db : " . mysql_error());
}
// Search the rows in the markers table
$query = sprintf("SELECT nombre,delegacion,latitud,longitud,(3959 * ACOS( COS( RADIANS('%s') ) * COS( RADIANS( latitud ) ) * COS( RADIANS( longitud ) - RADIANS('%s') ) + SIN( RADIANS( '%s' ) ) * SIN( RADIANS( latitud ) ) ) ) AS distance FROM os_anuncios   where latitud<>'' and latitud<>0 and longitud <> '' and longitud <>0 and nombre <> '' and delegacion is not null HAVING distance < '%s'",
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($center_lng),
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($radius));
$result = mysql_query($query);

$result = mysql_query($query);
if (!$result) {
  die("Invalid query: " . mysql_error());
}
//echo json_encode($result);
header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each

  while ($row = @mysql_fetch_assoc($result)){
      if(strlen($row['nombre'])!=0 || strlen($row['latitud'])!=0 || strlen($row['longitud'])!=0 || strlen($row['delegacion'])!=0 || strlen($row['distance'])!=0){
      $node = $dom->createElement("os_anuncios");
      $newnode = $parnode->appendChild($node);
      //$newnode->setAttribute("name", $row['nombre']);
      //$newnode->setAttribute("delegacion", $row['delegacion']);
      $newnode->setAttribute("latitud", $row['latitud']);
      $newnode->setAttribute("longitud", $row['longitud']);
      $newnode->setAttribute("distance", $row['distance']);
      //echo $row['nombre']." / <br>";
      //echo json_encode($row)."<br>";
      }else{
        //echo "campos vacios";
      }
  }  

echo $dom->saveXML();
?>