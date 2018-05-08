<?php

namespace Quinoid\PriceDiscount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;


class Discountprice implements ObserverInterface {

  public function execute(\Magento\Framework\Event\Observer $observer) {

           $item = $observer->getEvent()->getData('quote_item');
           $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
           if($item->getProductType() == 'bundle'){
             //$this->writemessage(serialize($this->_request->getPost()));
             //$bundleOptions = $item->getOptions();
             $price =  $this->getPrice('1','2'); //set your price here
             $item->setCustomPrice($price);
             $item->setOriginalCustomPrice($price);
             $item->getProduct()->setIsSuperMode(true);
           }
  }

  public function getPrice($sku1,$sku2){
    $sku1 = 'Agilin';
    $sku2 = 'Enkodin';
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $tableName = $resource->getTableName('pricediscount_bundlediscount'); //gives table name with prefix

    $sql = "Select * FROM " . $tableName. " where firstproduct ='".$sku1."' and secondproduct ='".$sku2."'";
    $this->writemessage($sql);
    $result = $connection->fetchAll($sql);
    if(count($result) > 0){
        $this->writemessage(serialize($result[0]));
        return $result[0]['discount'];
    }else{
      $sql = "Select * FROM " . $tableName. " where firstproduct ='".$sku2."' and secondproduct ='".$sku1."'";
      $result = $connection->fetchAll($sql);
      if(count($result) > 0){
        return $result[0]['discount'];
      }else{
        return false;
      }
    }
  }

  public function writemessage($message){
            $fh = fopen("/var/www/html/neuroclub/var/my_settings.txt", 'w') or die("Failed to create file");
            $text = $message;
            fwrite($fh, print_r($text, TRUE)) or die("Could not write to file");
            fclose($fh);
  }
}
