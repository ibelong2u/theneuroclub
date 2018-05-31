<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\PaymentMethodInterface;
use Aheadworks\Sarp\Api\Data\PaymentMethodInterfaceFactory;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineAvailability;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Factory as MethodFactory;
use Magento\Payment\Model\MethodInterface;

/**
 * Class PaymentMethodList
 * @package Aheadworks\Sarp\Model
 */
class PaymentMethodList
{
    /**
     * @var PaymentMethodInterface[]
     */
    private $methodInstances = [];

    /**
     * @var PaymentMethodInterfaceFactory
     */
    private $paymentMethodFactory;

    /**
     * @var MethodFactory
     */
    private $methodFactory;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @var EngineAvailability
     */
    private $engineAvailability;

    /**
     * @param PaymentMethodInterfaceFactory $paymentMethodFactory
     * @param MethodFactory $methodFactory
     * @param EngineMetadataPool $engineMetadataPool
     * @param EngineAvailability $engineAvailability
     */
    public function __construct(
        PaymentMethodInterfaceFactory $paymentMethodFactory,
        MethodFactory $methodFactory,
        EngineMetadataPool $engineMetadataPool,
        EngineAvailability $engineAvailability
    ) {
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->methodFactory = $methodFactory;
        $this->engineMetadataPool = $engineMetadataPool;
        $this->engineAvailability = $engineAvailability;
    }

    /**
     * Get engine payment methods
     *
     * @param string $engineCode
     * @param bool $availableOnly
     * @return PaymentMethodInterface[]
     * @throws \Exception
     * @throws LocalizedException
     */
    public function getMethods($engineCode, $availableOnly = false)
    {
        if (!isset($this->methodInstances[$engineCode])) {
            $methods = [];
            $engineMetadata = $this->engineMetadataPool->getMetadata($engineCode);
            if ($this->engineAvailability->isAvailable($engineMetadata)) {
                if ($engineMetadata->isGateway()) {
                    $methods[] = $this->getMethodInstanceUsingEngineMetadata($engineMetadata);
                } else {
                    foreach ($engineMetadata->getPaymentMethods() as $methodDeclaration) {
                        $method = $this->methodFactory->create($methodDeclaration['model']);
                        if (!$availableOnly) {
                            $methods[] = $this->getMethodInstanceUsingPaymentMethod($method);
                        } elseif ($method->isAvailable()) {
                            $methods[] = $this->getMethodInstanceUsingPaymentMethod($method);
                        }
                    }
                }
            }
            $this->methodInstances[$engineCode] = $methods;
        }
        return $this->methodInstances[$engineCode];
    }

    /**
     * Get payment method using engine code and method code
     *
     * @param string $engineCode
     * @param string|null $methodCode
     * @return PaymentMethodInterface|null
     */
    public function getMethod($engineCode, $methodCode = null)
    {
        $paymentMethodCode = $methodCode ? : $engineCode;
        foreach ($this->getMethods($engineCode) as $method) {
            if ($method->getCode() == $paymentMethodCode) {
                return $method;
            }
        }
        return null;
    }

    /**
     * Get payment method instance using engine metadata
     *
     * @param EngineMetadataInterface $engineMetadata
     * @return PaymentMethodInterface
     */
    private function getMethodInstanceUsingEngineMetadata(EngineMetadataInterface $engineMetadata)
    {
        /** @var PaymentMethodInterface $instance */
        $instance = $this->paymentMethodFactory->create();
        $instance
            ->setCode($engineMetadata->getCode())
            ->setTitle($engineMetadata->getLabel());
        return $instance;
    }

    /**
     * Get payment method instance using payment method
     *
     * @param MethodInterface $method
     * @return PaymentMethodInterface
     */
    private function getMethodInstanceUsingPaymentMethod(MethodInterface $method)
    {
        /** @var PaymentMethodInterface $instance */
        $instance = $this->paymentMethodFactory->create();
        $instance
            ->setCode($method->getCode())
            ->setTitle($method->getTitle());
        return $instance;
    }
}
