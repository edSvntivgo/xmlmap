<?php
// Get parameters from URL
$center_lat = $_GET["latitud"];
$center_lng = $_GET["longitud"];
$radius = $_GET["radius"];

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
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
$query = sprintf("SELECT nombre,delegacion,latitud,longitud, ( 3959 * ACOS( COS( RADIANS( 37 ) ) * COS( RADIANS( latitud ) ) * COS( RADIANS( longitud ) - RADIANS( -122 ) ) + SIN( RADIANS( 37 ) ) * SIN( RADIANS( latitud ) ) ) ) AS distance FROM os_anuncios",
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($center_lng),
  mysql_real_escape_string($center_lat),
  mysql_real_escape_string($radius));
$result = mysql_query($query);

$result = mysql_query($query);
if (!$result) {
  die("Invalid query: " . mysql_error());
}

//header("Content-type: text/xml");
//echo $result;
// Iterate through the rows, adding XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
  /*$node = $dom->createElement("markers");
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("nombre", $row['nombre']);
  $newnode->setAttribute("delegacion", $row['delegacion']);
  $newnode->setAttribute("latitud", $row['latitud']);
  $newnode->setAttribute("longitud", $row['longitud']);
  $newnode->setAttribute("distance", $row['distance']);*/
  echo $row['distance']."<br>";
}

//echo $dom->saveXML();
?>