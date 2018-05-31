<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Aheadworks\Sarp\Model\Product\Attribute\Source\SubscriptionType as SourceSubscriptionType;

/**
 * Class SubscriptionType
 * @package Aheadworks\Sarp\Ui\DataProvider\Product\Form\Modifier
 */
class SubscriptionType extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $subscriptionType = $this->arrayManager->findPath(
            'aw_sarp_subscription_type',
            $meta,
            null,
            'children'
        );

        if ($subscriptionType) {
            $subscriptionRegularPrice = $this->arrayManager->findPath(
                'aw_sarp_regular_price',
                $meta,
                null,
                'children'
            );
            if ($subscriptionRegularPrice) {
                $meta = $this->arrayManager->merge(
                    $subscriptionRegularPrice . static::META_CONFIG_PATH,
                    $meta,
                    [
                        "component" => "Aheadworks_Sarp/js/ui/form/element/regular-price"
                    ]
                );
            }

            $regularPriceTarget = 'product_form.product_form.subscription-configuration.' .
                'container_aw_sarp_regular_price.aw_sarp_regular_price';
            $meta = $this->arrayManager->merge(
                $subscriptionType . static::META_CONFIG_PATH,
                $meta,
                [
                    "switcherConfig" => [
                        "enabled" => true,
                        "rules" => [
                            [
                                "value" => SourceSubscriptionType::NO,
                                "actions" => [
                                    [
                                        "target" => $regularPriceTarget,
                                        "callback" => 'setNotRequired'
                                    ]
                                ]
                            ],
                            [
                                "value" => SourceSubscriptionType::SUBSCRIPTION_ONLY,
                                "actions" => [
                                    [
                                        "target" => $regularPriceTarget,
                                        "callback" => 'setRequired'
                                    ]
                                ]
                            ],
                            [
                                "value" => SourceSubscriptionType::OPTIONAL,
                                "actions" => [
                                    [
                                        "target" => $regularPriceTarget,
                                        "callback" => 'setRequired'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
