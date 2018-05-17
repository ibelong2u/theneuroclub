<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 24/05/2016
 * Time: 03:06
 */

namespace Magenest\Stripe\Block\Catalog\Product;

use Magento\Catalog\Block\Product\Context;
use Magenest\Stripe\Model\AttributeFactory;
use Magenest\Stripe\Helper\Config;

class View extends \Magento\Catalog\Block\Product\AbstractProduct
{
    protected $_attributeFactory;

    protected $_config;

    protected $attributeModel;

    public function __construct(
        Context $context,
        AttributeFactory $attributeFactory,
        Config $config,
        $data = []
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_config = $config;
        parent::__construct($context, $data);
        $this->getProductAttributeData();
    }

    public function getIsSubscriptionProduct()
    {
        return $this->attributeModel->getData('is_enabled');
    }

    public function getBillingOptions()
    {
        $value = $this->attributeModel->getData('value');
        $value = json_decode($value, true);
        if (!$value) {
            $value = [];
        }
        return $value;
    }

    public function isTotalCycleEnabled()
    {
        return false;
        return $this->_config->getIsTotalCycleEnabled();
    }

    public function getMaxTotalCycle()
    {
        return $this->_config->getMaxTotalCycle();
    }

    private function getProductAttributeData()
    {
        $product = $this->_coreRegistry->registry('current_product');
        $productId = $product->getId();
        if ($productId) {
            $this->attributeModel = $this->_attributeFactory->create()->load($productId, "entity_id");
        }
    }
}
