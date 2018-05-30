<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\PaymentMethodManagementInterface;
use Aheadworks\Sarp\Api\SubscriptionPlanRepositoryInterface;
use Aheadworks\Sarp\Api\SubscriptionsCartRepositoryInterface;

/**
 * Class PaymentMethodManagement
 * @package Aheadworks\Sarp\Model
 */
class PaymentMethodManagement implements PaymentMethodManagementInterface
{
    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var SubscriptionsCartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var PaymentMethodList
     */
    private $paymentMethodList;

    /**
     * @param SubscriptionPlanRepositoryInterface $planRepository
     * @param SubscriptionsCartRepositoryInterface $cartRepository
     * @param PaymentMethodList $paymentMethodList
     */
    public function __construct(
        SubscriptionPlanRepositoryInterface $planRepository,
        SubscriptionsCartRepositoryInterface $cartRepository,
        PaymentMethodList $paymentMethodList
    ) {
        $this->planRepository = $planRepository;
        $this->cartRepository = $cartRepository;
        $this->paymentMethodList = $paymentMethodList;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($cartId)
    {
        $methodList = [];
        $cart = $this->cartRepository->get($cartId);
        $planId = $cart->getSubscriptionPlanId();
        if ($planId) {
            $plan = $this->planRepository->get($planId);
            $methodList = $this->paymentMethodList->getMethods($plan->getEngineCode(), true);
        }
        return $methodList;
    }
}
