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

if(isset($_GET['start'])):
$start=$_GET['start'];
else:
$start=10;
endif;
$sql=  "select categoryid , catparentid , magentocategoryid from isc_categories where catparentid!=0 order by magentocategoryid limit ". $start.",50" ;
$result=mysql_query($sql);

while($row=mysql_fetch_array($result)){

    
    

            try
            {
      	$catid=$row['categoryid'];
	    $catparentrid=$row['catparentid'];
	    $catmagentocategoryid=$row['magentocategoryid'];
	    $sql1=  "select magentocategoryid  from isc_categories where categoryid=".$catparentrid ;
	    $result1=mysql_query($sql1);
	    $row1=mysql_fetch_array($result1);
	   
	    
         $result2 = $client->call($session, 'catalog_category.move', array('categoryId' => $catmagentocategoryid, 'parentId' => $row1['magentocategoryid']));
        // var_dump($result2);            
            }
            catch(exception $e)
            {
                echo "Failed for category ".$catmagentocategoryid."<br />";
                echo $e->getMessage()."<br />";
                continue;
            }
            
           


}

if(mysql_num_rows($result)>0)
{
    // header('location: http://dev.doability.co.uk/importer/importcategories.php?start='.$_GET['start']+10);
}


?>