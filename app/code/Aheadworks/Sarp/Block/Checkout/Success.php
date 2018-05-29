<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Checkout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Success
 * @package Aheadworks\Sarp\Block\Checkout
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get my subscriptions page url in customer account
     *
     * @return string
     */
    public function getCustomerSubscriptionsUrl()
    {
        return $this->_urlBuilder->getUrl('aw_sarp/profile/index');
    }

    /**
     * Get my orders page url in customer account
     *
     * @return string
     */
    public function getCustomerOrdersUrl()
    {
        return $this->_urlBuilder->getUrl('sales/order/history');
    }

    /**
     * Get continue shopping url
     *
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        return $this->_urlBuilder->getUrl();
    }
}
