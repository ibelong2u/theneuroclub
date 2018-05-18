<?php

namespace Magenest\Stripe\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Api extends \Magento\Framework\App\Config\Value
{
    protected $configWriter;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $writerInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->configWriter = $writerInterface;
    }

    public function afterSave()
    {
        $configValue = $this->getData('value');
        if ($configValue == 'direct') {
            $this->configWriter->save("payment/magenest_stripe/model", \Magenest\Stripe\Model\StripeDirectApi::class);
            $this->configWriter->save("payment/magenest_stripe/three_d_secure", "0");
            $this->configWriter->save("payment/magenest_stripe/save", "0");
        } else {
            $this->configWriter->save("payment/magenest_stripe/model", \Magenest\Stripe\Model\StripePaymentMethod::class);
        }

        return parent::afterSave();
    }
}
