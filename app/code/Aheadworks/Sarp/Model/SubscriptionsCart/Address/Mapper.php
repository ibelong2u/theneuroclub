<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Magento\Framework\EntityManager\MapperInterface;

/**
 * Class Mapper
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class Mapper implements MapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function entityToDatabase($entityType, $data)
    {
        if (is_array($data['street'])) {
            $data['street'] = implode('\n', $data['street']);
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function databaseToEntity($entityType, $data)
    {
        $data['street'] = explode('\n', $data['street']);
        return $data;
    }
}
