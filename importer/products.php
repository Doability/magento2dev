<?php
require_once 'connections.php';
ini_set('display_errors',1);
class Products extends Connections{
    
    // Tables involved
    // isc_products
    // isc_product_images
    // isc_product_variations
    //isc_product_variation_combinations
    // isc_product_variation_options
    
    protected $products = array();
   
    public function getProducts()
    {
        
        
        $products = array();
        
    
        $prepare = $this->conInterspire->prepare("
            select p.*,pv.magentovariationid from `isc_products` p inner join `isc_product_variations` pv where prodvariationid=variationid and `magentoproductid` = 0
            and prodvariationid!=0 ORDER BY p.`productid`  ASC
            limit 1
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
      
        
      /*  $inc = 1;
        foreach ($results as $result) {
            $this->customers['customer'][$inc] = $result;
           // $this->customers['customer'][$inc]['orders'] = $this->getOrders($result['customerid']);
            $this->customers['customer'][$inc]['addresses']=$this->getCustomerAddresses($result['customerid']);
            $inc++;
        } */
       // print_r($this->customers);
        return $results;
        
    }
    
    public function getProductImages($pid)
    {
        if($pid=='')
        {
            return array();
        }
       $prepare = $this->conInterspire->prepare("
            select pi.* from `isc_product_images` pi where pi.`imageprodid`='".$pid."'
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
    
    function getConfigurableOptions($pid)
    {
        if($pid=='')
        {
            return array();
        }
        
     
       
       $prepare = $this->conInterspire->prepare("
            select pvc.*, pv.vname from `isc_product_variation_combinations` pvc,
            `isc_product_variations` pv
            where
            pvc.vcvariationid=pv.variationid 
            and
            pvc.`vcproductid`='".$pid."'
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
        return $results;
        
    }
    
    function getConfigurableOptionsValues($ids,$variationid)
    {
        
        if(count($ids)==0) return array();
        
        
        $prepare = $this->conInterspire->prepare("
            SELECT * FROM `isc_product_variation_options` WHERE `vovariationid`='".$variationid."'
            and voptionid in ($ids)
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
       
        return $results;
    }
    
    public function addSimpleProduct($productArray, $images)
    {
    
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $product = Mage::getModel('catalog/product');
       
             try{
            if(!is_array($productArray) || sizeof($productArray)<=0)
                throw new exception('product data is required!');
                $prodvisible=1;
                if($productArray['prodvisible']==0)
                {
                    $prodvisible=2;
                }
                $price=$productArray['prodretailprice'];
                 if(empty($productArray['prodretailprice']) || $productArray['prodretailprice']==0)
                 {
                     $price=$productArray['prodprice'];
                 }
                 
                 
                 if(empty($productArray['prodcode']) || $productArray['prodcode']=='N/A')
                 {
                     $sku=rand(0,9999).'NA';
                 }
                 else
                 {
                     $sku=$productArray['prodcode'];
                 }
                     
                 
                $product->setStoreId($store) //you can set data in store scope
                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                ->setAttributeSetId(4) //ID of a attribute set named 'default'
                ->setTypeId('simple') //product type
                ->setCreatedAt(strtotime($productArray['proddateadded'])) //product creation time
                ->setUpdatedAt(strtotime('now')) //product update time
             
                ->setSku($sku) //SKU
                ->setName($productArray['prodname']) //product name
                ->setWeight($productArray['prodweight'])
                ->setStatus($prodvisible) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                //->setManufacturer(28) //manufacturer id
                //->setColor(24)
                //->setNewsFromDate('06/26/2014') //product set as new from
                //->setNewsToDate('06/30/2014') //product set as new to
                //->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)
             
                ->setPrice($price) //price in form 11.22
                
                //->setSpecialFromDate('06/1/2014') //special price from (MM-DD-YYYY)
                //->setSpecialToDate('06/30/2014') //special price to (MM-DD-YYYY)
                //->setMsrpEnabled(1) //enable MAP
                ->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                //->setMsrp(99.99) //Manufacturer's Suggested Retail Price
                ->setUpc($productArray['upc'])
                ->setInterspireId($productArray['productid'])
                ->setMetaTitle($productArray['prodpagetitle'])
                ->setMetaKeyword($productArray['prodmetakeywords'])
                ->setMetaDescription($productArray['prodmetadesc'])
             
                ->setDescription($productArray['proddesc'])
                ->setShortDescription($productArray['proddesc'])
                ->setProdwidth($productArray['prodwidth'])
                ->setProdheight($productArray['prodheight'])
                ->setProddepth($productArray['proddepth'])               
             
                //->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
               
                ->setStockData(array(
                                   'use_config_manage_stock' => 0, //'Use config settings' checkbox
                                   'manage_stock'=>1, //manage stock
                                   'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                                   'max_sale_qty'=>99, //Maximum Qty Allowed in Shopping Cart
                                   'is_in_stock' => 1, //Stock Availability
                                   'qty' =>  $productArray['prodcurrentinv']//qty
                               )
                );
                 
                //  ->addImageToMediaGallery('1/0/10243-1.png', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
              //    ->addImageToMediaGallery('/var/www/vhosts/doability.co.uk/', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
             if(!empty($productArray['prodcostprice']) && $productArray['prodcostprice']>0)
             {
                $product->setCost($productArray['prodcostprice']); //price in form 11.22
             }
             if(!empty($productArray['prodsaleprice']) && $productArray['prodsaleprice']>0)
             {     
                 $product->setSpecialPrice($productArray['prodsaleprice']); //special price in form 11.22     
             }
                 
             foreach($images as $image)
             {
                 if(file_exists('/var/www/vhosts/doability.co.uk/httpdocs/product_images/'.$image['imagefile']))
                 {
                 $product->addImageToMediaGallery('/var/www/vhosts/doability.co.uk/httpdocs/product_images/'.$image['imagefile'], array('image','thumbnail','small_image','hover_img'), false, false);
                 }
             }
                      
             
            //->setCategoryIds($categories); //assign product to categories
            $product->save();
                              
            return $product->getId();
            //endif;
            }catch(Exception $e){
                
               Mage::log('product import error'.$e->getMessage());
        }
        
  
        
    }
    
    
    
    
    
    

/**************** Create Configurable Product ********************************/
    
    public function addConfigurableProduct($productArray, $images)
    {
       
     
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $product = Mage::getModel('catalog/product');
      
             try{
            if(!is_array($productArray) || sizeof($productArray)<=0)
                throw new exception('product data is required!');
                $prodvisible=1;
                if($productArray['prodvisible']==0)
                {
                    $prodvisible=2;
                }
                $price=$productArray['prodretailprice'];
                 if(empty($productArray['prodretailprice']) || $productArray['prodretailprice']==0)
                 {
                     $price=$productArray['prodprice'];
                 }
                 
                 
                 if(empty($productArray['prodcode']) || $productArray['prodcode']=='N/A')
                 {
                     $sku=rand(0,9999).'NA';
                 }
                 else
                 {
                     $sku=$productArray['prodcode'];
                 }
                     
                 
                $product->setStoreId($store) //you can set data in store scope
                ->setWebsiteIds(array(1)) //website ID the product is assigned to, as an array
                ->setAttributeSetId($productArray['prodvariationid']) //ID of a attribute set named 'default'
                    
                ->setTypeId('configurable') //product type
                ->setCreatedAt(strtotime($productArray['proddateadded'])) //product creation time
                ->setUpdatedAt(strtotime('now')) //product update time
             
                ->setSku($sku) //SKU
                ->setName($productArray['prodname']) //product name
                ->setWeight($productArray['prodweight'])
                ->setStatus($prodvisible) //product status (1 - enabled, 2 - disabled)
                ->setTaxClassId(2) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                //->setManufacturer(28) //manufacturer id
                //->setColor(24)
                //->setNewsFromDate('06/26/2014') //product set as new from
                //->setNewsToDate('06/30/2014') //product set as new to
                //->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)
             
                ->setPrice($price) //price in form 11.22
                
                //->setSpecialFromDate('06/1/2014') //special price from (MM-DD-YYYY)
                //->setSpecialToDate('06/30/2014') //special price to (MM-DD-YYYY)
                //->setMsrpEnabled(1) //enable MAP
                ->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                //->setMsrp(99.99) //Manufacturer's Suggested Retail Price
                ->setUpc($productArray['upc'])
                ->setInterspireId($productArray['productid'])
                ->setMetaTitle($productArray['prodpagetitle'])
                ->setMetaKeyword($productArray['prodmetakeywords'])
                ->setMetaDescription($productArray['prodmetadesc'])
             
                ->setDescription($productArray['proddesc'])
                ->setShortDescription($productArray['proddesc'])
                ->setProdwidth($productArray['prodwidth'])
                ->setProdheight($productArray['prodheight'])
                ->setProddepth($productArray['proddepth'])               
             
                //->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
               
                ->setStockData(array(
                                   'use_config_manage_stock' => 0, //'Use config settings' checkbox
                                   'manage_stock'=>1, //manage stock
                                   'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                                   'max_sale_qty'=>99, //Maximum Qty Allowed in Shopping Cart
                                   'is_in_stock' => 1, //Stock Availability
                                   'qty' =>  $productArray['prodcurrentinv']//qty
                               )
                );
                 
                //  ->addImageToMediaGallery('1/0/10243-1.png', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
              //    ->addImageToMediaGallery('/var/www/vhosts/doability.co.uk/', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
             if(!empty($productArray['prodcostprice']) && $productArray['prodcostprice']>0)
             {
                $product->setCost($productArray['prodcostprice']); //price in form 11.22
             }
             if(!empty($productArray['prodsaleprice']) && $productArray['prodsaleprice']>0)
             {     
                 $product->setSpecialPrice($productArray['prodsaleprice']); //special price in form 11.22     
             }
                 
             foreach($images as $image)
             {
                 if(file_exists('/var/www/vhosts/doability.co.uk/httpdocs/product_images/'.$image['imagefile']))
                 {
                 $product->addImageToMediaGallery('/var/www/vhosts/doability.co.uk/httpdocs/product_images/'.$image['imagefile'], array('image','thumbnail','small_image','hover_img'), false, false);
                 }
             }
                      
             
             
            //->setCategoryIds($categories); //assign product to categories
            $product->save();
                              
            return $product->getId();
            //endif;
            }catch(Exception $e){
              
                    echo $e->getMessage();
                 
               Mage::log('product import error'.$e->getMessage());
        }
        
  
        
    }
    
    
/**************** Create Configurable Product ********************************/    
    
    
             
   function updateProductIdinInterspire($intespireid,$magenotproductid)
   {
      
       $prepare = $this->conInterspire->prepare("
            update `isc_products` set `magentoproductid`='".$magenotproductid."' where 
           `productid`='".$intespireid."'
        ");
       
        $prepare->execute();
   }
    
}



$obj = new Products();
$products=$obj->getProducts();

foreach($products as $product)
{
    
    
      $configurableoptions=$obj->getConfigurableOptions($product['productid']);
    
    
      if(count($configurableoptions)): 
    
       
        $productimages=$obj->getProductImages($product['productid']);
        try
        {
            $simpleProduct = $this->_createProduct(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE); // create simple product
            $confProduct   = $this->_createProduct(Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE, false); // create conf product but do not save
               
            
          //   $productid=$obj->addConfigurableProduct($product,$productimages);
        }
       catch(exception $e)
       {
           echo $e->getMessage();
       }
   
            foreach($configurableoptions as $option):
             echo '<pre>';
                print_r($option);
             echo '</pre>';
              $attributeId=$option['magentoattributeid'];     
              $optionvalues=  $obj->getConfigurableOptionsValues($option['vcoptionids'],$option['vcvariationid']);
                
            echo '<pre>';
                print_r($optionvalues);
            echo '</pre>';

        exit; 
        endforeach;
    endif;
 //   $productimages=$obj->getProductImages($product['productid']);
   
//    $productid=$obj->addConfigurableProduct($product,$productimages);
    
   // $productid=$obj->addSimpleProduct($product,$productimages);
    
      
  //  $obj->updateProductIdinInterspire($product['productid'],$productid);
    
    
    
}

if(count($products)>0 && false ){
    ?>
<script>
document.location="http://dev.doability.co.uk/importer/products.php";
</script>

<?php
}
?>