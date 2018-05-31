<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api;

use Magento\Framework\Simplexml\Element;
use Magento\Framework\Simplexml\ElementFactory;

/**
 * Class ResponseProcessor
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Api
 */
class ResponseProcessor
{
    /**
     * @var ElementFactory
     */
    private $xmlElementFactory;

    /**
     * @param ElementFactory $xmlElementFactory
     */
    public function __construct(ElementFactory $xmlElementFactory)
    {
        $this->xmlElementFactory = $xmlElementFactory;
    }

    /**
     * Process response data
     *
     * @param string $rawResponse
     * @return array
     */
    public function process($rawResponse)
    {
        libxml_use_internal_errors(true);
        /** @var Element $xml */
        $xml = $this->xmlElementFactory->create(['data' => $rawResponse]);
        libxml_use_internal_errors(false);
        return $xml->asArray();
    }
}
