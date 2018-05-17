<?php
/**
 * Created by Magenest.
 * Author: Pham Quang Hau
 * Date: 26/05/2016
 * Time: 14:57
 */

namespace Magenest\Stripe\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider implements ConfigProviderInterface
{
    protected $_helper;

    protected $_cardFactory;

    protected $_customerSession;

    protected $_checkoutSession;

    protected $stripeConfigHelper;

    protected $_urlBuilder;

    const CODE = 'magenest_stripe';

    public function __construct(
        PaymentHelper $paymentHelper,
        \Magenest\Stripe\Model\CardFactory $cardFactory,
        \Magenest\Stripe\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\Stripe\Helper\Config $stripeConfigHelper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $dataHelper;
        $this->_cardFactory = $cardFactory;
        $this->stripeConfigHelper = $stripeConfigHelper;
        $this->_urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        $cardData = $this->getDataCard();
        return [
            'payment' => [
                "magenest_stripe_config" => [
                    'publishableKey' => $this->stripeConfigHelper->getPublishableKey(),
                    'isLogin' => $this->_customerSession->isLoggedIn(),
                    'isZeroDecimal' => $this->checkIsZeroDecimal()
                ],
                "magenest_stripe" => [
                    'isSave' => $this->stripeConfigHelper->isSave(),
                    'saveCards' => json_encode($cardData),
                    'hasCard' => count($cardData)>0 ? true:false,
                    'instructions' => $this->stripeConfigHelper->getInstructions(),
                    'api' => $this->stripeConfigHelper->getApiVersion()
                ],
                "magenest_stripe_iframe" => $this->getStripeCheckoutConfigOption(),
                "magenest_stripe_applepay" => $this->getStripeApplePayConfig()
            ]
        ];
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    public function getDataCard()
    {
        $objectManager = ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        if ($customerSession->isLoggedIn()) {
            $customer_id = $customerSession->getCustomerId();
            $model = $this->_cardFactory->create()
                ->getCollection()
                ->addFieldToFilter('magento_customer_id', $customer_id)
                ->addFieldToFilter('status', "active");
            return $model->getData();
        } else {
            return [];
        }
    }

    public function checkIsZeroDecimal()
    {
        $currency = $this->_checkoutSession->getQuote()->getBaseCurrencyCode();
        return $this->_helper->isZeroDecimal($currency) ? '1' : '0';
    }

    public function getStripeCheckoutConfigOption()
    {
        $canCollectShipping = $this->stripeConfigHelper->getCheckoutCanCollectShipping();
        $canCollectBilling = $this->stripeConfigHelper->getCheckoutCanCollectBilling();
        $canCollectZipCode = $this->stripeConfigHelper->getCheckoutCanCollectZip();
        $displayName = $this->stripeConfigHelper->getDisplayName();
        $imageUrl = $this->stripeConfigHelper->getCheckoutImageUrl();
        return [
            'can_collect_billing' => $canCollectBilling,
            'can_collect_shipping' => $canCollectShipping,
            'can_collect_zip' => $canCollectZipCode,
            'display_name' => $displayName,
            'button_label' => $this->stripeConfigHelper->getButtonLabel(),
            'allow_remember' => $this->stripeConfigHelper->getAllowRemember(),
            'accept_bitcoin' => $this->stripeConfigHelper->getCanAcceptBitcoin(),
            'accept_alipay' => $this->stripeConfigHelper->getCanAcceptAlipay(),
            'image_url' => $imageUrl,
            'locale' => $this->stripeConfigHelper->getLocale()
        ];
    }

    public function getStripeApplePayConfig()
    {
        return [
            'replace_placeorder' => $this->stripeConfigHelper->getReplacePlaceOrder(),
            'button_type' => $this->stripeConfigHelper->getButtonType(),
            'button_theme' => $this->stripeConfigHelper->getButtonTheme(),
        ];
    }
}
