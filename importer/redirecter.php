<?php
if($_GET['start']>3681)
{
    $_GET['start']=0;
}
$url="http://dev.doability.co.uk/importer/orders.php?start=".$_GET['start'];
?>
<meta http-equiv="refresh" content="5;url=<?php echo $url ?>" />
