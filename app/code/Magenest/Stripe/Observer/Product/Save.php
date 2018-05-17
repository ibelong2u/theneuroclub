<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 20/05/2016
 * Time: 01:15
 */

namespace Magenest\Stripe\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magenest\Stripe\Model\AttributeFactory;
use Magenest\Stripe\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

class Save implements ObserverInterface
{
    protected $_request;

    protected $_storeManager;

    protected $_attributeFactory;

    protected $_helper;

    protected $subscriptionHelper;

    public function __construct(
        RequestInterface $requestInterface,
        StoreManagerInterface $storeManagerInterface,
        AttributeFactory $attributeFactory,
        HelperData $helperData,
        \Magenest\Stripe\Helper\SubscriptionHelper $subscriptionHelper
    ) {
        $this->_request = $requestInterface;
        $this->_storeManager = $storeManagerInterface;
        $this->_attributeFactory = $attributeFactory;
        $this->_helper = $helperData;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getParams();
        $product = $observer->getProduct();
        $productId = $product->getId();
        $productName = $product->getName();
        $price = $product->getPrice();
        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
        $subscriptionOptions = isset($data['product']['stripe_billing_options']) ? $data['product']['stripe_billing_options'] : [];
        $subscriptionActive = isset($data['product']['stripe_subscription_enabled']) ? $data['product']['stripe_subscription_enabled'] : "0";
        $attribute = $this->_attributeFactory->create()->load($productId, "entity_id");
        $value = [];
        if ($attribute->getId()) {
            $value = json_decode($attribute->getData('value'), true);
            $stripeProductId = $attribute->getData('product_id');
        } else {
            //create product
            $createProductResponse = $this->subscriptionHelper->createProduct($productName);
            $stripeProductId = isset($createProductResponse['id']) ? $createProductResponse['id'] : "";
            $attribute->setData('product_id', $stripeProductId);
            $attribute->setData('entity_id', $productId);
        }
        if ($stripeProductId) {
            $planCurrentListId = [];
            foreach ($subscriptionOptions as $option) {
                $planId = isset($option['plan_id']) ? $option['plan_id'] : "";
                if ($planId && array_key_exists($planId, $value)) {
                    $planCurrentListId[] = $planId;
                }
                $unitId = isset($option['unit_id']) ? $option['unit_id'] : "";
                $frequency = isset($option['frequency']) ? $option['frequency'] : "";
                $trialDay = false;
                $nickName = isset($option['plan_name']) ? $option['plan_name'] : "";
                if (isset($option['is_trial_enabled']) && ($option['is_trial_enabled'] == "1")) {
                    $trialDay = isset($option['trial_day']) ? $option['trial_day'] : false;
                }
                if (!$planId) {
                    //create plan
                    $createPlanResponse = $this->subscriptionHelper->createPlan(
                        $currency,
                        $unitId,
                        $frequency,
                        $stripeProductId,
                        $price,
                        $nickName,
                        $trialDay
                    );
                    $planId = isset($createPlanResponse['id']) ? $createPlanResponse['id'] : "";
                    if ($planId) {
                        $planCurrentListId[] = $planId;
                        $option['plan_id'] = $planId;
                        $value[$planId] = $option;
                    }
                } else {
                    //update plan
                    $valueData = $value[$planId];
                    if (($valueData['plan_name'] != $nickName) || ($valueData['trial_day'] != $trialDay)) {
                        $updatePlanResponse = $this->subscriptionHelper->updatePlan($planId, $nickName, $trialDay);
                        $planId = isset($updatePlanResponse['id']) ? $updatePlanResponse['id'] : "";
                        if ($planId) {
                            //if update done
                            $value[$planId] = $option;
                        }
                    }
                }
            }
            $oldPlanIdList = array_keys($value);
            $listIdDel = array_diff($oldPlanIdList, $planCurrentListId);
            foreach ($listIdDel as $_planId) {
                $delResponse = $this->subscriptionHelper->deletePlan($_planId);
                if (isset($delResponse) && ($delResponse['deleted'] == true)) {
                    unset($value[$_planId]);
                }
            }
        }
        $attribute->setData('is_enabled', $subscriptionActive);
        $attribute->setData('value', json_encode($value));
        $attribute->save();
        return;
    }
}
