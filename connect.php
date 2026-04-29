<?php    
$HOSTNAME='localhost';
$USERNAME='root';
$PASSWORD='';
$DATABASE='trackseed';

$con=mysqli_connect($HOSTNAME,$USERNAME,$PASSWORD,$DATABASE);

if(!$con){
   die(mysqli_error($con));
}
?>