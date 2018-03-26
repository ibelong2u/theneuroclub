<?php
namespace Quinoid\FlagshipsWidget\Model\Config\Source;

class ProductsList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $collection = $productCollection->addAttributeToSelect(array('sku', 'name'));
        $productsList = array();
        foreach ($collection as $item) {
            $sku = $item->getSku();
            $name = $item->getName();
            $productsList[] = ['label' => $name, 'value' => $sku];
        }
        return $productsList;
    }
}
