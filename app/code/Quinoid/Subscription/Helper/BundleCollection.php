<?php

namespace Quinoid\Subscription\Helper;

use Magento\Catalog\Model\ProductFactory;

class BundleCollection extends \Magento\Framework\App\Helper\AbstractHelper {

  private $_resource;
  protected $connection;
  protected $jsonHelper;
 	/**
   * @param \Magento\Framework\App\Helper\Context $context
  */
 	public function __construct(
 						\Magento\Framework\App\Helper\Context $context,
 						\Magento\Framework\App\ResourceConnection $resource,
 						ProductFactory $productFactory,
            \Magento\Framework\Json\Helper\Data $jsonHelper
 	) {
 		$this->_resource = $resource;
 		$this->_productFactory = $productFactory;
    $this->jsonHelper = $jsonHelper;
    parent::__construct($context);
 	}

 	protected function getConnection()
 	 {
 			 if (!$this->connection) {
 					 $this->connection = $this->_resource->getConnection('core_read');
 			 }
 			 return $this->connection;
 	 }

  public function getParentBundle($cartArr,$prdcnt){
    //Custom Query for getting bundle product list with bundle item selection
    $items = implode(",",$cartArr);
    $bundledFound =0;
    $returnArr = array();
    $tableName = $this->getConnection()->getTableName('catalog_product_bundle_selection');
    $sql = "SELECT parent_product_id FROM " . $tableName ." GROUP BY parent_product_id HAVING count(parent_product_id) =" . $prdcnt;
    $result = $this->getConnection()->fetchAll($sql);
    foreach($result as $res){
      // check whether the selectons are same for this parent_product_id
      $sql = "SELECT * FROM " . $tableName ." WHERE parent_product_id =" . $res['parent_product_id'] ." AND product_id IN (".$items.")";
      $parentResult = $this->getConnection()->fetchAll($sql);
    //  print_r($parentResult);
      if(count($parentResult) == $prdcnt){
          $bundledFound = 1;
          foreach ($parentResult as $key=>$value) {
             $parentBundledItem = $value['parent_product_id'];
             break;
          }
      }
    }
    $returnArr['found']  =  $bundledFound;
    if($bundledFound == 1) {
       //Bundled Item found
       $returnArr['bundleid']  =  $parentBundledItem;
    } else {
       $returnArr['bundleid']  =  0;
    }
    $encodedData = $this->jsonHelper->jsonEncode($returnArr);
    return $encodedData;
  }

  //Function to get the bundled getItemsCount
  public function getBundledItems($bundleId){
      $tableName = $this->getConnection()->getTableName('catalog_product_bundle_selection');
      $sql = "SELECT * FROM " . $tableName ." WHERE parent_product_id =" . $bundleId;
      $result = $this->getConnection()->fetchAll($sql);
      $bundledItem = array();
      $i=0;
      foreach($result as $key => $res){
        $bundledItem[$i] = $res['product_id'];
        $i++;
      }
     $encodedData = $this->jsonHelper->jsonEncode($bundledItem);
     return $encodedData;
  }
  //Get bundle details in array format
  public function getBundledItemsDetails($bundleId){
      $tableName = $this->getConnection()->getTableName('catalog_product_bundle_selection');
      $sql = "SELECT option_id,product_id,selection_id FROM ". $tableName ." WHERE parent_product_id =" . $bundleId;
      $resultSql = $this->getConnection()->fetchAll($sql);
      foreach ($resultSql as $row) {
        $bundleOptions[$row['option_id']][$row['product_id']] = $row['selection_id'];
      }
     return $bundleOptions;
  }

}
?>
