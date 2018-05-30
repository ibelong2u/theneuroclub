<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Aheadworks\Sarp\Model\SubscriptionsCart\Address;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class SaveHandler implements ExtensionInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $cartAddresses = $entity->getAddresses();
        /** @var SubscriptionsCartAddressInterface $address */
        foreach ($cartAddresses as $address) {
            try {
                $address->setCartId($entity->getCartId());
                if ($address->getCustomerAddressId() && ($entity->getCustomerId() == $address->getCustomerId())) {
                    $address->setEmail($entity->getCustomerEmail());
                }
                $this->entityManager->save($address);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('Could not save subscriptions cart.'));
            }
        }
        return $entity;
    }
}
