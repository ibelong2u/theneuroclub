<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Checkout;

use Magento\Checkout\Block\Checkout\AttributeMerger;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\Form\AttributeMapper;

/**
 * Class LayoutProcessor
 * @package Aheadworks\Sarp\Block\Checkout
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var AttributeMapper
     */
    private $attributeMapper;

    /**
     * @var AttributeMerger
     */
    private $merger;

    /**
     * @var \Magento\Customer\Model\Options
     */
    private $options;

    /**
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Customer\Model\Options $options
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Customer\Model\Options $options,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->options = $options;
        $this->attributeMapper = $attributeMapper;
        $this->merger = $merger;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $attributesToConvert = [
            'prefix' => [$this->options, 'getNamePrefixOptions'],
            'suffix' => [$this->options, 'getNameSuffixOptions'],
        ];

        $elements = $this->convertElementsToSelect(
            $this->getAddressAttributes(),
            $attributesToConvert
        );

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])
        ) {
            $fields = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'] = $this->merger
                ->merge(
                    $elements,
                    'checkoutProvider',
                    'shippingAddress',
                    $fields
                );
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'])
        ) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'] = $this->processPaymentChildrenComponents(
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children'],
                $elements
            );
        }

        return $jsLayout;
    }

    /**
     * Ge address attributes
     *
     * @return array
     */
    private function getAddressAttributes()
    {
        /** @var AttributeInterface[] $attributes */
        $attributes = $this->attributeMetadataDataProvider->loadAttributesCollection(
            'customer_address',
            'customer_register_address'
        );

        $elements = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();
            if (!$attribute->getIsUserDefined()) {
                $attributeMeta = $this->attributeMapper->map($attribute);
                if (isset($attributeMeta['label'])) {
                    $attributeMeta['label'] = __($attributeMeta['label']);
                }
                $elements[$code] = $attributeMeta;
            }
        }
        return $elements;
    }

    /**
     * Convert elements(like prefix and suffix) from inputs to selects when necessary
     *
     * @param array $elements
     * @param array $attributesToConvert
     * @return array
     */
    private function convertElementsToSelect($elements, $attributesToConvert)
    {
        $codes = array_keys($attributesToConvert);
        foreach (array_keys($elements) as $code) {
            if (in_array($code, $codes)) {
                $options = call_user_func($attributesToConvert[$code]);
                if (is_array($options)) {
                    $elements[$code]['dataType'] = 'select';
                    $elements[$code]['formElement'] = 'select';

                    foreach ($options as $key => $value) {
                        $elements[$code]['options'][] = [
                            'value' => $key,
                            'label' => $value,
                        ];
                    }
                }
            }
        }

        return $elements;
    }

    /**
     * Process payment children components
     *
     * @param array $paymentLayout
     * @param array $elements
     * @return array
     */
    private function processPaymentChildrenComponents(array $paymentLayout, array $elements)
    {
        if (!isset($paymentLayout['payments-list']['children'])) {
            $paymentLayout['payments-list']['children'] = [];
        }

        if (!isset($paymentLayout['afterMethods']['children'])) {
            $paymentLayout['afterMethods']['children'] = [];
        }

        $paymentLayout['payments-list']['children'] =
            array_merge_recursive(
                $paymentLayout['payments-list']['children'],
                $this->processPaymentConfiguration(
                    $paymentLayout['renders']['children'],
                    $elements
                )
            );

        return $paymentLayout;
    }

    /**
     * Inject billing address component into every payment component
     *
     * @param array $configuration list of payment components
     * @param array $elements attributes that must be displayed in address form
     * @return array
     */
    private function processPaymentConfiguration(array &$configuration, array $elements)
    {
        $output = [];
        foreach ($configuration as $paymentGroup => $groupConfig) {
            foreach ($groupConfig['methods'] as $paymentCode => $paymentComponent) {
                if (!empty($paymentComponent['isBillingAddressRequired'])) {
                    $output[$paymentCode . '-form'] = $this->getBillingAddressComponent($paymentCode, $elements);
                }
            }
            unset($configuration[$paymentGroup]['methods']);
        }

        return $output;
    }

    /**
     * Get billing address component details
     *
     * @param string $paymentCode
     * @param array $elements
     * @return array
     */
    private function getBillingAddressComponent($paymentCode, $elements)
    {
        $addressFormFieldsConfig = [
            'region' => ['visible' => false],
            'region_id' => [
                'component' => 'Magento_Ui/js/form/element/region',
                'config' => [
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'customEntry' => 'billingAddress' . $paymentCode . '.region'
                ],
                'filterBy' => [
                    'target' => '${ $.provider }:${ $.parentScope }.country_id',
                    'field' => 'country_id'
                ],
                'validation' => ['required-entry' => true]
            ],
            'postcode' => [
                'component' => 'Magento_Ui/js/form/element/post-code',
                'validation' => ['required-entry' => true]
            ],
            'company' => ['validation' => ['min_text_length' => 0]],
            'fax' => ['validation' => ['min_text_length' => 0]],
            'country_id' => ['sortOrder' => 115],
            'telephone' => [
                'config' => [
                    'tooltip' => ['description' => __('For delivery questions.')]
                ],
            ],
        ];

        return [
            'component' => 'Aheadworks_Sarp/js/ui/checkout/view/billing-address',
            'displayArea' => 'billing-address-form-' . $paymentCode,
            'provider' => 'checkoutProvider',
            'deps' => 'checkoutProvider',
            'dataScopePrefix' => 'billingAddress' . $paymentCode,
            'sortOrder' => 1,
            'children' => [
                'form-fields' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'additional-fieldsets',
                    'children' => $this->merger->merge(
                        $elements,
                        'checkoutProvider',
                        'billingAddress' . $paymentCode,
                        $addressFormFieldsConfig
                    ),
                ],
            ],
        ];
    }
}
