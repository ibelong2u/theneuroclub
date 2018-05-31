<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Stripe;

use Magento\Payment\Block\Info as InfoBlock;
use Magento\Payment\Model\Method\Substitution;

/**
 * Class PaymentMethod
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Stripe
 */
class PaymentMethod extends Substitution
{
    /**
     * {@inheritdoc}
     */
    const CODE = 'aw_sarp_stripe';

    /**
     * {@inheritdoc}
     */
    protected $_code = self::CODE;

    /**
     * {@inheritdoc}
     */
    protected $_isGateway = true;

    /**
     * {@inheritdoc}
     */
    protected $_infoBlockType = InfoBlock::class;

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }
}
