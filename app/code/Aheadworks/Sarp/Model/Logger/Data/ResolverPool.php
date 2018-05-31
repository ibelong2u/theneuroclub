<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\Logger\Data;

use Aheadworks\Sarp\Model\Logger\Data\Resolver\PaymentAuthorized;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\PaymentCaptured;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\PaymentFailed;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\PaymentPaid;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\PaymentStarted;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\ProfileCreated;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\ProfileCreationFailed;
use Aheadworks\Sarp\Model\Logger\Data\Resolver\ProfileStatusChange;
use Aheadworks\Sarp\Model\Logger\LoggerInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ResolverPool
 * @package Aheadworks\Sarp\Model\Logger\Data
 */
class ResolverPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $resolvers = [
        LoggerInterface::ENTRY_TYPE_PROFILE_STATUS_CHANGED => ProfileStatusChange::class,
        LoggerInterface::ENTRY_TYPE_PROFILE_CREATED_SUCCESSFUL => ProfileCreated::class,
        LoggerInterface::ENTRY_TYPE_PROFILE_CREATION_FAILED => ProfileCreationFailed::class,
        LoggerInterface::ENTRY_TYPE_PAYMENT_STARTED => PaymentStarted::class,
        LoggerInterface::ENTRY_TYPE_PAYMENT_AUTHORIZED => PaymentAuthorized::class,
        LoggerInterface::ENTRY_TYPE_PAYMENT_CAPTURED => PaymentCaptured::class,
        LoggerInterface::ENTRY_TYPE_PAYMENT_PAID => PaymentPaid::class,
        LoggerInterface::ENTRY_TYPE_PAYMENT_FAIL => PaymentFailed::class
    ];

    /**
     * @var ResolverInterface[]
     */
    private $resolverInstances = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $resolvers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $resolvers = []
    ) {
        $this->objectManager = $objectManager;
        $this->resolvers = array_merge($this->resolvers, $resolvers);
    }

    /**
     * Get data resolver
     *
     * @param string $entryType
     * @return ResolverInterface|null
     * @throws \Exception
     */
    public function getResolver($entryType)
    {
        if (!isset($this->resolverInstances[$entryType])) {
            if (!isset($this->resolvers[$entryType])) {
                throw new \Exception(sprintf('Unknown log entry type: %s requested', $entryType));
            }
            $resolverInstance = $this->objectManager->create($this->resolvers[$entryType]);
            if (!$resolverInstance instanceof ResolverInterface) {
                throw new \Exception(
                    sprintf('Resolver %s does not implement required interface.', $entryType)
                );
            }
            $this->resolverInstances[$entryType] = $resolverInstance;
        }
        return $this->resolverInstances[$entryType];
    }
}
