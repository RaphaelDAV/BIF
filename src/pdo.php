<?php
/*$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'bif';

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}*/


$servername = 'sqletud.u-pem.fr';
$username = 'raphael.daviot';
$password = 'R@ph@131106';
$dbname = 'raphael.daviot_db';
$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>