<?php

ini_set('max_execution_time', 0);
ini_set('display_errors',1);
set_time_limit(0);

require_once 'connections.php';

class Customers extends Connections {

    protected $customers = array();

    /**
     * Customers::customersData()
     * 
     * @return void
     */
    public function customersData() {
        $customers = array();
        $prepare = $this->conInterspire->prepare("
            select c.* from `isc_customers` c where `magentocustomerid`=''
            ORDER BY c.`customerid` ASC
            limit 0,50
        ");
       
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

        $inc = 1;
        foreach ($results as $result) {
            $this->customers['customer'][$inc] = $result;
           // $this->customers['customer'][$inc]['orders'] = $this->getOrders($result['customerid']);
            $this->customers['customer'][$inc]['addresses']=$this->getCustomerAddresses($result['customerid']);
            $inc++;
        }
       // print_r($this->customers);
        return $this->customers;
    }
    
    
    public function AddMagentoCustomerIdtoInterspire($interspireid, $magentoid)
    {
        $prepare = $this->conInterspire->prepare("
            update `isc_customers` set `magentocustomerid`='".$magentoid."' where 
           `customerid`='".$interspireid."'
        ");
       
        $prepare->execute();
    }
    /**
     * Customers::getCustomerAddresses()
     * 
     * @param int $customer_id
     * @return customer addresses Array
     */
    
    public function getCustomerAddresses($customer_id)
    {
        if(empty($customer_id)) return array();
        $addresses=array();
        $prepare = $this->conInterspire->prepare("
            select sa.* , ic.countryiso2 from `isc_shipping_addresses` sa inner join isc_countries ic 
            WHERE sa.shipcountryid=ic.countryid and `shipcustomerid` = {$customer_id}
        ");
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
         foreach ($results as $result) {
             $addresses[]=$result;
         }
        return $addresses;
    }
    

    /**
     * Customers::getCustomerDataById()
     * 
     * @param int $customer_id
     * @return void
     */
    
    
    
    public function getCustomerDataById($customer_id) {
        $customers = array();
        $prepare = $this->conInterspire->prepare("
            select c.* from `isc_customers` c
            WHERE c.`customerid` = {$customer_id}
        ");
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

        $inc = 0;
        foreach ($results as $result) {
            $this->customers['customer'][$inc] = $result;
            $this->customers['customer'][$inc]['addresses']=$this->getCustomerAddresses($customer_id);
            
            
            //$this->customers['customer'][$inc]['orders'] = $this->getOrders($result['customerid']);

            //$inc++;
        }

        return $this->customers;
    }

    /**
     * Customers::getOrderProducts()
     * 
     * @param int $order_id
     * @return
     */
    public function getOrderProducts($order_id) {
        try {
            $products = array();
            if (empty($order_id))
                throw new exception('order id is required');

            $prepare = $this->conInterspire->prepare("
                select op.* from `isc_order_products` op
                WHERE op.`orderorderid` = {$order_id}
            ");
            $prepare->execute();
            $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

            $inc = 0;
            foreach ($results as $result) {
                $products[$inc] = $result;
                $mageProductId = function ($sku) {
                    $product_id = Mage::getModel('catalog/product')->getIdBySku($sku);
                    return $product_id;
                };
                $mage_product_id = $products[$inc]['magento']['product_id'] = $mageProductId($result['ordprodsku']);
                /**
                 * in case when product is not present in the magento site use current order product.
                 */ 
                if(empty($mage_product_id)){
                    $products[$inc]['magento']['product'] = $result;
                } 
                $inc++;
            }


            return $products;

        }
        catch (exception $e) {

        }
    }

    /**
     * Customers::getOrders()
     * 
     * @param int $customer_id
     * @return
     */
    public function getOrders($customer_id) {
        try {
            if (empty($customer_id))
                throw new exception('customer id is required');

            $orders = array();

            $prepare = $this->conInterspire->prepare("
                select o.* from `isc_orders` o
                WHERE o.`ordcustid` = {$customer_id}
            ");
            $prepare->execute();
            $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

            $inc = 0;
            foreach ($results as $result) {
                $orders[$inc] = $result;
                $orders[$inc]['address'] = $this->getOrderAddress($result['orderid']);
                $orders[$inc]['products'] = $this->getOrderProducts($result['orderid']);
                $orders[$inc]['shipping'] = $this->getOrderShipping($result['orderid']);
                $orders[$inc]['tax'] = $this->getOrderTax($result['orderid']);

                $inc++;
            }

            return $orders;

        }
        catch (exception $e) {

        }
    }

    /**
     * Customers::getOrderTax()
     * 
     * @param int $order_id
     * @return
     */
    public function getOrderTax($order_id) {
        try {
            if (empty($order_id))
                throw new exception('order id is required');

            $prepare = $this->conInterspire->prepare("
                select ot.* from `isc_order_taxes` ot
                WHERE ot.`order_id` = {$order_id}
            ");
            $prepare->execute();
            $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

            return $results;

        }
        catch (exception $e) {

        }
    }

    /**
     * Customers::getOrderShipping()
     * 
     * @param int $order_id
     * @return
     */
    public function getOrderShipping($order_id) {
        try {
            if (empty($order_id))
                throw new exception('order id is required');

            $prepare = $this->conInterspire->prepare("
                select os.* from `isc_order_shipping` os
                WHERE os.`order_id` = {$order_id}
            ");
            $prepare->execute();
            $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

            return $results;

        }
        catch (exception $e) {

        }
    }

    /**
     * Customers::getOrderAddress()
     * 
     * @param int $order_id
     * @return
     */
    public function getOrderAddress($order_id) {
        try {
            if (empty($order_id))
                throw new exception('order id is required');

            $prepare = $this->conInterspire->prepare("
                select oa.* from `isc_order_addresses` oa
                WHERE oa.`order_id` = {$order_id}
            ");
            $prepare->execute();
            $results = $prepare->fetchAll(PDO::FETCH_ASSOC);

            return $results;

        }
        catch (exception $e) {

        }
    }
    
    /**
     * Customers::getProduct()
     * 
     * @param int $id
     * @return array
     */
    public function getProduct($id){
        $product = Mage::getModel('catalog/product')->load($id);
        echo '<pre>';
        print_r(Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        print_r(Mage::app()->getWebsite()->getId());
        print_r(Mage::app()->getStore());
        print_r($product);
    }
    
    /**
     * Customers::addProduct()
     * 
     * @param array $productArray
     * @return int id
     */
    public function addProduct($productArray = array(), $categories = array()){
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $product = Mage::getModel('catalog/product');
        
        try{
            
            if(!is_array($productArray) || sizeof($productArray)<=0)
                throw new exception('product data is required!');
            
            $product->setStoreId($store) //you can set data in store scope
                ->setWebsiteIds(array($websiteId)) //website ID the product is assigned to, as an array
                ->setAttributeSetId(4) //ID of a attribute set named 'default'
                ->setTypeId('simple') //product type
                ->setCreatedAt(strtotime('now')) //product creation time
                ->setUpdatedAt(strtotime('now')) //product update time
             
                ->setSku($productArray['ordprodsku']) //SKU
                ->setName($productArray['ordprodname']) //product name
                ->setWeight($productArray['ordprodweight'])
                ->setStatus(1) //product status (1 - enabled, 2 - disabled)
                //->setTaxClassId(4) //tax class (0 - none, 1 - default, 2 - taxable, 4 - shipping)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH) //catalog and search visibility
                //->setManufacturer(28) //manufacturer id
                //->setColor(24)
                //->setNewsFromDate('06/26/2014') //product set as new from
                //->setNewsToDate('06/30/2014') //product set as new to
                //->setCountryOfManufacture('AF') //country of manufacture (2-letter country code)
             
                ->setPrice($productArray['base_price']) //price in form 11.22
                ->setCost($productArray['price_inc_tax']) //price in form 11.22
                ->setSpecialPrice($productArray['price_inc_tax']) //special price in form 11.22
                //->setSpecialFromDate('06/1/2014') //special price from (MM-DD-YYYY)
                //->setSpecialToDate('06/30/2014') //special price to (MM-DD-YYYY)
                //->setMsrpEnabled(1) //enable MAP
                ->setMsrpDisplayActualPriceType(1) //display actual price (1 - on gesture, 2 - in cart, 3 - before order confirmation, 4 - use config)
                //->setMsrp(99.99) //Manufacturer's Suggested Retail Price
             
                //->setMetaTitle('test meta title 2')
                //->setMetaKeyword('test meta keyword 2')
                //->setMetaDescription('test meta description 2')
             
                //->setDescription('This is a long description')
                //->setShortDescription('This is a short description')
             
                //->setMediaGallery (array('images'=>array (), 'values'=>array ())) //media gallery initialization
                //->addImageToMediaGallery('media/catalog/product/1/0/10243-1.png', array('image','thumbnail','small_image'), false, false) //assigning image, thumb and small image to media gallery
             
                ->setStockData(array(
                                   'use_config_manage_stock' => 0, //'Use config settings' checkbox
                                   'manage_stock'=>1, //manage stock
                                   'min_sale_qty'=>1, //Minimum Qty Allowed in Shopping Cart
                                   'max_sale_qty'=>99, //Maximum Qty Allowed in Shopping Cart
                                   'is_in_stock' => 1, //Stock Availability
                                   'qty' => 99999 //qty
                               )
                )
             
                ->setCategoryIds($categories); //assign product to categories
            $product->save();
            return $product->getId();
            //endif;
            }catch(Exception $e){
                Mage::log($e->getMessage());
        }
    }
    
    /**
     * Customers::customerGroup()
     * 
     * @return void
     */
    public function customerGroup($group_id){
        switch($group_id) {
            case 6;
            return 1;
            break;
            case 8;
            return 5;
            break;
            case 5;
            return 3;
            break;
            case 7;
            return 2;
            break;
            
            
            default:
            return '';
        }
    }
    
    /**
     * Customers::checkCustomerOrderExist()
     * 
     * @param array $address_array
     * @param int $customer_id
     * @return boolean
     */
    public function checkCustomerAddressExist($address_array = array(), $customer_id){
        $customer = Mage::getModel('customer/customer')
                    ->load($customer_id); // insert customer ID

        foreach ($customer->getAddresses() as $address){
            $data = $address->toArray();
            print_r($data);
        }
    }
    
    /**
     * Customers::orderStatus()
     * 
     * @return void
     */
    public function orderStatus($status_id){
        $interspire = array(
            '14'=>"on_hire",
            '12'=>"on_loan",
            '1'=>"pending",
            '7'=>"awaiting_payment",
            '11'=>"awaiting_fulfillment ",
            '9'=>"awaiting_shipment",
            '8'=>"awaiting_pickup",
            '3'=>"partially_shipped ",
            '10'=>"complete",
            '2'=>"shipped",
            '5'=>"canceled",
            '6'=>"declined",
            '4'=>"refunded",
            '13'=>"dispatch_cmortans"
        );
        
        return $interspire[$status_id];
    }
    
    /**
     * Customers::addCustomerAddress()
     * 
     * @param array $address_array
     * @return int id
     */
    public function addCustomerAddress($address_array = array(), $customer_id){
        try{
            if(!is_array($address_array) || sizeof($address_array)<0 )
                throw new exception('address array is required!');
            
            $customAddress = Mage::getModel('customer/address');
            $customAddress->setData($address_array)
                ->setCustomerId($customer_id)
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            $customAddress->save();
            
            return $customAddress->getId();
            
        }catch(Exception $e){
            Mage::log($e->getMessage());
        }
    }
    
    /**
     * Customers::checkProductExistBySku()
     * 
     * @param string $sku
     * @return int
     */
    public function checkProductExistBySku($sku){
        $sk = Mage::getModel('catalog/product')->getIdBySku($sku);
        return $sk;
    }
    
    /**
     * Customers::orderCalculations()
     * 
     * @param array $order
     * @return void
     */
    protected function orderCalculations($orderIncrementId, $order){
        try{
            
            if(empty($orderIncrementId))
                throw new exception('order increment id is required');
            
            /**
             * change order status to 'Completed'
             */
            $orderStatus = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            /*$orderStatus->setState($this->orderStatus($order['ordstatus']), true)->save();*/
            $orderStatus->setstatus($this->orderStatus($order['ordstatus']));
            /**
             * original order created date.
             */ 
            $orderDate = function($date){
                return date('Y-m-d H:i:s', $date);
            }; 
            $orderStatus->setCreatedAt($orderDate($order['orddate']));            
            $orderStatus->setUpdatesAt($orderDate($order['ordlastmodified']));
            /**
             * order tax rate.
             */ 
            if($order['total_tax']){
                $orderStatus->setTaxAmount($order['total_tax']);
            }
            /**
             * order discount amount: coupon
             */
            if(isset($order['coupon_discount']) && $order['coupon_discount'] > 0)
                $orderStatus->setDiscountAmount($order['coupon_discount']);
            
            /**
             * Set shipping settings
             */
            $orderStatus->setShippingDescription($order['shipping'][0]['method']);
            
            if(isset($order['shipping_cost_inc_tax'])){
                //$orderStatus->setBaseShippingAmount($order['shipping_cost_inc_tax']);
                $orderStatus->setShippingAmount($order['shipping_cost_inc_tax']);
                $orderStatus->setBaseShippingAmount($order['shipping_cost_inc_tax']);
            }
            
            $orderStatus->setBaseShippingTaxAmount($order['method']);             
            /**
              * Tax settings
              */
            $orderStatus->setBaseTaxAmount($order['tax'][0]['rate']);    
            /**
             * row total including tax.
             */
            $orderStatus->setGrandTotal($order['total_inc_tax']);    
            $orderSaved = $orderStatus->save();
            
            $order_id = $orderSaved->getId();
            
            /**
             * save order to: order attribute for reference order id of interspire.
             */            
            $reference = Mage::getModel('amorderattr/attribute');
            $reference->load($order_id, 'order_id');
            $reference->setFurtherOrderRef($order['orderid']);
            $reference->save();
        
        }catch(exception $e){
            var_dump($e->getMessage());
        }        
    }
    
    /**
     * Customers::addProductCustomOrderData()
     * 
     * @param object $customer
     * @param array $product
     * @param object $quote
     * @param object $qouteItem
     * @return null
     */
    protected function addProductCustomOrderData($customer, $order, $product, $quote, $qouteItem){
        if(!empty($product['ordprodoptions'])){
            $variation = function($variation){
                $name = unserialize($variation);
                $str = '';
                foreach($name as $k => $v){
                    $str .= '['.$k.': '.$v.'] ';
                }
                return $str;
            };
            $qouteItem->setName($product['ordprodname'].'-'.$variation($product['ordprodoptions']));
        }
        $qouteItem->setOriginalPrice($product['pricewithoutvat']);
        $qouteItem->setBasePrice($product['pricewithoutvat']);
        //if($product[''])
        $qouteItem->setBaseCost($product['base_cost_price']);
        $qouteItem->setTaxAmount($product['total_tax']);
        //$qouteItem->setCustomPrice('30');
        $qouteItem->setOriginalCustomPrice($product['price_inc_tax']);
        //$qouteItem->setRowTotal($product['pricewithoutvat']);
        //$qouteItem->setBaseRowTotal($product['pricewithoutvat']);
        //$qouteItem->setPriceInclTax($product['pricewithoutvat']);
        $qouteItem->setBasePriceInclTax($product['total_inc_tax']);
        
        //$qouteItem->setRowTotalInclTax($product['total_inc_tax']);
        //$qouteItem->setBaseRowTotalInclTax($product['pricewithoutvat']);
        //$qouteItem->setCreatedAt($order['pricewithoutvat']);
        //$qouteItem->setUpdatedAt($order['pricewithoutvat']);
        //$qouteItem->setBaseRowTotalInclTax($product['pricewithoutvat']);
        $qouteItem->setIsSuperMode(true);
        $quote->save();
    }
    

    /**
     * Customers::magentoImport()
     * 
     * @return void
     */
    public function magentoImport($data) {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        $customer = Mage::getModel("customer/customer");

        $customers = $data;
        
      
        /** 
        * customer group 
        */
        $customerGroup = $this->customerGroup($group_id);
        
        $inc = 1;
        foreach ($customers['customer'] as $c) {
          
            $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->
                loadByEmail($c['custconemail']);

            if ($customer->getId() == "") {
                /**
                 * save new customer 
                 */
                if(isset($c['salt'])):
                    $password=$c['custpassword'].':'.$c['salt'];
                else:
                    $password=$c['custpassword'];
                endif;
                $customer//->setWebsiteId($websiteId)
                        //->setStore($store)
                        ->setFirstname($c['custconfirstname'])
                        ->setLastname($c['custconlastname'])
                        ->setEmail($c['custconemail'])
                        ->setPassword($c['custpassword'])
                         ->setCreatedAt($c['custdatejoined'])
                        ->setGroupId($this->customerGroup($c['custgroupid']));
                $customer->save();
                $cid = $customer->getId();
                
                $this->AddMagentoCustomerIdtoInterspire($c['customerid'],$cid);
                
                /* Save Customer Address */
                
                foreach($c['addresses'] as $address):
                
                
                
                  $address2=array(
                                        //'customer_address_id' => '',
                                        'prefix' => '',
                                        'firstname' => $address['shipfirstname'],
                                        'middlename' => '',
                                        'lastname' => $address['shiplastname'],
                                        'suffix' => '',
                                        'company' => $address['shipcompany'],
                                        'street' => array('0' => $address['shipaddress1'], '1' => $address['shipaddress2']),
                                        'city' => $address['shipcity'],
                                        'country_id' => $address['countryiso2'],
                                        'region' => $address['shipstate'],
                                        'postcode' => $address['shipzip'],
                                        'telephone' => $address['shipphone'],
                                        'fax' => '',
                                        'vat_id' => '',
                                        'save_in_address_book' => 1);
                                    $this->addCustomerAddress($address2, $cid);
                
                
                
                
                endforeach;
                /* Save Customer Address */
                
                
                
                

            } else {
                $cid = $customer->getId();
            }
            try {
                /**
                 * assign orders to customer 
                 */
                
                
            }
            catch (exception $e) {
                echo '<font color="red"><b>Exception</b></font>::'.$c['custconemail'].'::order id::'.$order['orderid'].'='.$e->getMessage().'<br />';
            }
        
        if($inc % 50 == 0)
            sleep(5);
            
        $inc++;
        
        /**
         * prints
         */ 
        echo '<hr />';
        
        }
    }
    
}
echo time();

$customers = new Customers();
$data = $customers->customersData();

$customers->magentoImport($data);

/*
if(isset($_REQUEST['userid'])){
    $data = $customers->getCustomerDataById($_REQUEST['userid']);
}

if($_REQUEST['t'] == 1){
    echo '<pre>';
    print_r($data);
}

if($_REQUEST['t'] == 2){
    $customers->magentoImport($data);  
}
*/
echo time();
if(count($data)>0 ){
    ?>
<script>
document.location="http://dev.doability.co.uk/importer/customers.php";
</script>

<?php
}
?>