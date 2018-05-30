<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;

use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ActionPool
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment
 */
class ActionPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var ActionInterface[]
     */
    private $actionInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->objectManager = $objectManager;
        $this->engineMetadataPool = $engineMetadataPool;
    }

    /**
     * Retrieve payment action instance
     *
     * @param string $engineCode
     * @param string $methodCode
     * @return ActionInterface
     * @throws \Exception
     */
    public function getAction($engineCode, $methodCode)
    {
        $key = $engineCode . '-' . $methodCode;
        if (!isset($this->actionInstances[$key])) {
            $metadata = $this->engineMetadataPool->getMetadata($engineCode);
            $paymentMethods = $metadata->getPaymentMethods();
            if (!isset($paymentMethods[$methodCode])) {
                throw new \Exception(
                    sprintf('Unknown payment method: %s in engine: %s requested', $methodCode, $engineCode)
                );
            }
            $actionInstance = $this->objectManager->create($paymentMethods[$methodCode]['payment_action']);
            if (!$actionInstance instanceof ActionInterface) {
                throw new \Exception(
                    sprintf('Payment action %s does not implement required interface.', $methodCode)
                );
            }
            $this->actionInstances[$key] = $actionInstance;
        }
        return $this->actionInstances[$key];
    }
}
