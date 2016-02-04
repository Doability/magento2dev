<?php
include_once('connections.php');

/**
 * Reviews
 * 
 * @package Doability
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class Reviews extends Connections{
    
    protected $reviews;
    
    /**
     * Reviews::getReviewsInterspire()
     * 
     * @return array
     */
     
    public function getReviewsInterspire(){
        $prepare = $this->conInterspire->prepare("
            SELECT p.`productid`, p.`prodcode`, p.`prodname`, r.* FROM `isc_products` p
            RIGHT JOIN `isc_reviews` r ON (r.`revproductid` = p.`productid`)
            ORDER BY r.`revproductid` DESC
        ");
        $prepare->execute();
        $results = $prepare->fetchAll(PDO::FETCH_ASSOC);
        $this->reviews = $results;
        
        return $this;
    }
    
    /**
     * Reviews::getReviewsData()
     * 
     * @return array
     */
    public function getReviewsData(){
        return $this->reviews;
    }
    
    /**
     * Reviews::getReviewsProductSku()
     * 
     * @return array
     */
    public function getReviewsProductSku(){
        $sku = array();
        if(sizeof($this->reviews) <= 0){
            return $sku;
        }else{
            foreach($this->reviews as $review){
                $sku[] = $review['prodcode'];
            }
        }
        
        return array_unique($sku);
    }
    
    /**
     * Reviews::getMagentoProductIdBySku()
     * 
     * @param mixed $sku
     * @return
     */
    public function getMagentoProductIdBySku($sku = array()){
        $collection = Mage::getModel('catalog/product')->getCollection()->getData();
        $p = array();
        foreach($collection as $product){
            $obj = Mage::getModel('catalog/product'); 
            $productid = $product['entity_id']; 
            $_product = $obj->load($productid); // Enter your Product Id in $product_id 
            $p[$_product->getId()] = $_product->getSku();
        }
        
        return $p;                
    }
    
    /**
     * Reviews::combinationSkuProdId()
     * 
     * @return array
     */
    public function combinationSkuProdId(){
        $reviews = $this->getReviewsInterspire()->getReviewsData();
        $magentoSku = $this->getMagentoProductIdBySku();
        $newReviews = array();
        $i = 0;
        foreach($reviews as $review){
            $review['magento_product_id'] = array_search($review['prodcode'], $magentoSku);
            $newReviews[$i] = $review;
            
            $i++;
        }
        return $newReviews;
    }
    
}

/*$reviews = new Reviews();
$combination = $reviews->combinationSkuProdId();
*/

$review = Mage::getModel('review/review');
$review->setEntityPkValue(505);//product id
$review->setStatusId(1); // approved
$review->setTitle("ekam Title");
$review->setDetail("ekam detail");
$review->setEntityId(1);                                      
$review->setStoreId(Mage::app()->getStore()->getId());                    
$review->setCustomerId(null);//null is for administrator
$review->setNickname("ekam singh");
$review->setReviewId($review->getId());
$review->setStores(array(Mage::app()->getStore()->getId()));   

$review->setSkipCreatedAtSet(true);   
$review->setCreatedAt('2014-05-20 08:28:34');              
$review->save();

$rating = Mage::getModel('rating/rating_option_vote');
$rating->setEntityPkValue(505);
$rating->setRatingId(1);
$rating->setPercent(60);
$rating->setValue(60);
$rating->save();