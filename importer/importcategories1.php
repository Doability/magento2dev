<?php 
require_once 'connections.php';
$server='localhost';
$user= 'ctaylor_2011';
$paswrd= 'an1cca';
$db='doability_2011';
$link= mysql_connect($server,$user,$paswrd);
mysql_select_db($db,$link);

$client = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
$session = $client->login('vikas', 'w3sols!@#');


$sql=  "select categoryid ,catname , catdesc, catpagetitle, catmetakeywords, catmetadesc, catvisible from isc_categories where magentocategoryid= '0'" ;
$result=mysql_query($sql);

while($row=mysql_fetch_array($result)){

	$catid=$row['categoryid'];
	$catname= $row['catname'];
	$catname=utf8_encode($catname);
	$catdesc= $row['catdesc'];
	$catptitle= $row['catpagetitle'];
	$catkw= $row['catmetakeywords'];
	$catmde= $row['catmetadesc'];
	$catvis= $row['catvisible'];
   // $urlkey=strtolower($catname);
	$urlkey= str_replace(" ","-",$catname);
   // $urlkey=str_replace(array('&','/',' '),'-',$urlkey);
   // $urlkey=str_replace(array('—','—','——'),'-',$urlkey);
    $urlkey= str_replace(" ","-",$catname);
    //$urlkey=$urlkey.'.html';
	echo $catid;
	echo "</br>";
 
     $result1 = $client->call($session, 'catalog_category.create', array(2, array(
    'name' => $catname,
    'is_active' => $catvis,
    'position' => 1,
    //<!-- position parameter is deprecated, category anyway will be positioned in the end of list
    //and you can not set position directly, use catalog_category.move instead -->
    'available_sort_by' => 'position',
    'custom_design' => null,
    'custom_apply_to_products' => null,
    'custom_design_from' => null,
    'custom_design_to' => null,
    'custom_layout_update' => null,
    'default_sort_by' => 'position',
    'description' => $catdesc,
    'display_mode' => null,
    'is_anchor' => 0,
    'landing_page' => null,
    'meta_description' => $catmde,
    'meta_keywords' => $catkw,
    'meta_title' => $catptitle,
    'page_layout' => 'two_columns_left',
    'url_key' => $urlkey,
    'include_in_menu' => 1,
)));

//var_dump ($result1);

$sql1= "update isc_categories set magentocategoryid=$result1 where categoryid=$catid";
mysql_query($sql1); 

}


?>