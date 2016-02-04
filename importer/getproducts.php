<?php 
require_once 'connections.php';
$server='localhost';
$user= 'ctaylor_2011';
$paswrd= 'an1cca2016';
$db='doability_2011';
$link= mysql_connect($server,$user,$paswrd);
mysql_select_db($db,$link);

$sql=  "select productid from isc_products where magentoproductid=0" ;
$result=mysql_query($sql);
$i=0;
while($row=mysql_fetch_array($result)){
$i++;
echo $row['productid'];
echo '</br>';
}



?>