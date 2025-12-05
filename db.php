<?php 
$host='localhost';
$db='item';
$user='root';
$pass='';
try{ 
    $conn=new PDO("mysql:host=$host;dbname=$db",$user,$pass);
} 
catch(PDOException $e) 
{ 
    die("Connection failed: " . $e->getMessage()); 
}
?>