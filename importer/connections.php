<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once '../app/Mage.php';
Mage::app();
class Connections{
    
    protected $conMagento;
    
    protected $conInterspire;
    
    /**
     * Connections::__construct()
     * 
     * @return mysql connections object
     */
    public function __construct(){
        try {
            /* interspire */
            $this->conInterspire = new PDO("mysql:host=localhost;dbname=doability_2011", 'ctaylor_2011', 'an1cca2016'); //Initiates connection
            $this->conInterspire->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // Sets error mode
            
            /* magento */
            $this->conMagento = new PDO("mysql:host=localhost;dbname=dev_doabuk_2", 'dev_ukdoab_2', '0q%bGd64'); //Initiates connection
            $this->conMagento->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); // Sets error mode
            
        } catch (PDOException $e) {
            file_put_contents(__DIR__."/log/dberror.log", "Date: " . date('M j Y - G:i:s') . " ---- Error: " . $e->getMessage().PHP_EOL, FILE_APPEND);
            die($e->getMessage());
        }
    }
    
    /**
     * Connections::__destruct()
     * 
     * @return close mysql connections
     */
    public function __destruct(){
        try {
            $this->conInterspire = null; //Closes connection
            $this->conMagento = null; //Closes connection
        } catch (PDOException $e) {
            file_put_contents(__DIR__."/log/dberror.log", "Date: " . date('M j Y - G:i:s') . " ---- Error: " . $e->getMessage().PHP_EOL, FILE_APPEND);
            die($e->getMessage());
        }
    }
}
?>