<?php
ini_set('max_execution_time', 0);
ini_set('display_errors',1);
set_time_limit(0);
require_once 'connections.php';


class Variations extends Connections
{ 


    


public function AddAttributeSets()
{
    $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
    $sessionId = $proxy->login('vikas', 'w3sols!@#');
    $prepare = $this->conInterspire->prepare("
            SELECT * FROM `isc_product_variations` where magentovariationid=0 limit 1
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
      


foreach($results as $set)
    {

    $setName = $set['vname'];
    $skeletonId = 4;

    $newSetId = $proxy->call(
        $sessionId,
        "product_attribute_set.create",
        array(
             $setName,
             $skeletonId
        )
    );
    $prepare = $this->conInterspire->prepare("
           update `isc_product_variations` set magentovariationid='".$newSetId."' where variationid='".$set['variationid']."'
        ");
       
        $prepare->execute();
    
   }
    
    if(count($results)>0 ){

        $iteration=$_GET['iteration']+1;
        header("location: http://dev.doability.co.uk/importer/variations.php?iteration=$iteration");
        exit;
    }
    
 }
    
public function AddAttributes()
{
         $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
         $sessionId = $proxy->login('vikas', 'w3sols!@#');
        
        /* $prepare = $this->conInterspire->prepare("
        SELECT * FROM `isc_product_variations` inner join `isc_product_variation_options` po  where magentovariationid!=0 and   magentoattributeid=0 and variationid=vovariationid group by vovariationid,voname order by variationid,vooptionsort
         
         
        "); */
       
          $prepare = $this->conInterspire->prepare("SELECT *  FROM  `isc_product_variation_options` where   magentoattributeid=0  group by voname 
      ");
    
        $prepare->execute();
        $variations = $prepare->fetchAll(PDO::FETCH_ASSOC);
        
        
        foreach($variations as $variation)
        {
            if(strtolower($variation['voname'])=='options')
            {
                $attributecode='coptions';
            }
            else
            {
                $attributecode=strtolower(substr(str_replace(array("-"," ","&",'/'),"_",$variation['voname']),0,25));
            }
            $attributeToUpdate = array(
            "attribute_code" => $attributecode,    
            "scope" => "global",
            "default_value" => "",
            "frontend_input" => "select",
            "is_unique" => 0,
            "is_required" => 0,
            "is_configurable" => 1,
            "is_searchable" => 0,
            "is_visible_in_advanced_search" => 0,
            "used_in_product_listing" => 0,
            "additional_fields" => array(
                "is_filterable" => 0,
                "is_filterable_in_search" => 0,
                "position" => 1,
                "used_for_sort_by" => 0
            ),
            "frontend_label" => array(
                array(
                    "store_id" => 0,
                    "label" => $variation['voname']
                )
            )
        );
            
        $attributeid = $proxy->call($sessionId, 'product_attribute.create', array($attributeToUpdate));    
        
         
        
        /*    
        $result = $proxy->call($sessionId, "product_attribute_set.attributeAdd",
        array(
             $attributeid,
             $variation['magentovariationid']
            )
        );    */
        
            
        $prepare = $this->conInterspire->prepare("
           update `isc_product_variation_options` set magentoattributeid='".$attributeid."' where  voname='".$variation['voname']."'
        ");
       
        $prepare->execute();
            

        }
    
 
    
        if(count($variations)>0)
        {
            $iteration=$_GET['iteration']+1;
            header("location: http://dev.doability.co.uk/importer/variations.php?iteration=$iteration");
            exit;
            
        }
       
}
    
    
public function AddAttributesToSets()
{
        $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
         $sessionId = $proxy->login('vikas', 'w3sols!@#');
          $prepare = $this->conInterspire->prepare("SELECT *  FROM  `isc_product_variation_options`   group by voname 
      ");
    
        $prepare->execute();
        $variations = $prepare->fetchAll(PDO::FETCH_ASSOC);
        
        
        foreach($variations as $variation){
        
            
            
            $prepare2 = $this->conInterspire->prepare("SELECT po.*,pv.magentovariationid  FROM  `isc_product_variation_options` po , `isc_product_variations` pv   where po.vovariationid=pv.variationid and voname='".$variation['voname']."' group by vovariationid 
          ");
            
            try
            {

                $prepare2->execute();
                $sets = $prepare2->fetchAll(PDO::FETCH_ASSOC);

                echo '<pre>';
                    print_r($sets);
                echo '</pre>';
            
            foreach($sets as $set):
                $result = $proxy->call($sessionId, "product_attribute_set.attributeAdd",
                array(
                     $set['magentoattributeid'],
                     $set['magentovariationid']
                    )
                );  
            endforeach;
            
            }
            catch(exception $e)
            {
                echo $e->getMessage();
                continue;
            }
            
           
            
            
        }
    
    
    
}
    
    
    
    
    
    
    
public function AddVariationOptions()
{
    $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
    $sessionId = $proxy->login('vikas', 'w3sols!@#');
    try
    {
            $prepare = $this->conInterspire->prepare("
                    select * from isc_product_variation_options   where magentoattributeoptionid=0 group by voname, vovalue order by voname, vovaluesort
                    limit 200
                ");

                 
                $prepare->execute();
                $options = $prepare->fetchAll(PDO::FETCH_ASSOC);
                foreach($options as $option)
                {
                    echo '<pre>';
                        print_r($option);
                    echo '</pre>';
                    
                    $attributeCode = $option['magentoattributeid'];
                        $optionToAdd = array(
                            "label" => array(
                                array(
                                    "store_id" => 0,
                                    "value" => utf8_encode($option['vovalue'])
                                )
                            ),
                            "order" => $option['vovaluesort'],
                            "is_default" => 0
                        );

                        $result = $proxy->call(
                            $sessionId,
                            "product_attribute.addOption",
                            array(
                                 $attributeCode,
                                 $optionToAdd
                            )
                        );
                    
                   
                    
            $prepare = $this->conInterspire->prepare("
           update `isc_product_variation_options` set magentoattributeoptionid=1 where voname='".$option['voname']."' and vovalue='".addslashes($option['vovalue'])."'
        ");
       
        $prepare->execute();
                    
                }
        
        if(count($options)>0)
        {
            $iteration=$_GET['iteration']+1;
            header("location: http://dev.doability.co.uk/importer/variations.php?iteration=$iteration");
            exit;
        }

        
    }
    catch(exception $e)
    {
        echo $e->getMessage();
        continue;
    }
}

    
    
function UpdateAttributes()
{
        
         $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
         $sessionId = $proxy->login('vikas', 'w3sols!@#');
        
         $prepare = $this->conInterspire->prepare("
            SELECT * FROM `isc_product_variations` inner join `isc_product_variation_options` po  where magentovariationid!=0 and  variationid=vovariationid group by vovariationid limit 1
        ");
       
        $prepare->execute();
        $variations = $prepare->fetchAll(PDO::FETCH_ASSOC);
        
        
        foreach($variations as $variation)
        {
            echo '<pre>';
                print_r($variation);
            echo '</pre>';
            $attributeToUpdate = array(

            "additional_fields" => array(
                "is_filterable" => 0,
                "is_filterable_in_search" => 0,
                 )
            );
            
            $result = $proxy->call(
                $sessionId,
                $variation['vovariationid'],
                "product_attribute.update",
                array(
                    $attributeToUpdate
                )
            );
        }
        
}
    
public function deleteAttribute()
{
      $proxy = new SoapClient('http://dev.doability.co.uk/api/soap/?wsdl');
      $sessionId = $proxy->login('vikas', 'w3sols!@#');
            
    
        $prepare = $this->conInterspire->prepare("
        SELECT distinct magentoattributeid FROM `isc_product_variation_options`   where magentoattributeid!=0 
        limit 100
        ");
       
        $prepare->execute();
        $variations = $prepare->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($variations as $variation):
            $attributeCode = $variation['magentoattributeid'];
            try
            {
                $result = $proxy->call(
                    $sessionId,
                    "product_attribute.remove",
                    array(
                         $attributeCode
                    )
                );
                $prepare = $this->conInterspire->prepare("
                update `isc_product_variation_options` set magentoattributeid=0 where magentoattributeid='".$attributeCode."'
                ");
       
                $prepare->execute();
      
            }
            catch(exception $e)
            {
              echo $e->getMessage().' '.$attributeCode;
              $prepare = $this->conInterspire->prepare("
                update `isc_product_variation_options` set magentoattributeid=0 where magentoattributeid='".$attributeCode."'
                ");    
                 $prepare->execute();
            }
               
       
            endforeach;
    
}
    
    
    
}

$obj=new Variations();
//$obj->AddAttributeSets();


// $obj->AddAttributes();
//$obj->AddAttributesToSets();

$obj->AddVariationOptions();
//$obj->deleteAttribute();

//$obj->UpdateAttributes();

