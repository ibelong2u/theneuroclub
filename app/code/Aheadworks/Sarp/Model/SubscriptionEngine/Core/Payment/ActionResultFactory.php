<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class ActionResultFactory
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Core\Payment
 */
class ActionResultFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create payment action result instance
     *
     * @param array $data
     * @return ActionResult
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(ActionResult::class, ['data' => $data]);
    }
}
