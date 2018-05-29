<?php
/**
 * Created by PhpStorm.
 * User: ducanh
 * Date: 22/05/2018
 * Time: 16:21
 */
namespace Magenest\Stripe\Helper;

class OrderHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_product;
    protected $_formkey;
    protected $quote;
    protected $quoteManagement;
    protected $customerFactory;
    protected $customerRepository;
    protected $orderService;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\Data\Form\FormKey $formKey $formkey,
     * @param \Magento\Quote\Model\Quote $quote,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory,
     * @param \Magento\Sales\Model\Service\OrderService $orderService,
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        parent::__construct($context);
    }

    /**
     *
     * @param array $orderData
     * @return array
     *
     */
    public function createOrderx($orderData)
    {
        $store=$this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer=$this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);
        if (!$customer->getEntityId()) {
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email']);
            $customer->save();
        }
        $quote=$this->quote->create();
        $quote->setStore($store);
        $customer= $this->customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer);

        foreach ($orderData['items'] as $item) {
            $product=$this->_product->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);


        $shippingAddress=$quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('freeshipping_freeshipping');
        $quote->setPaymentMethod('checkmo');
        $quote->setInventoryProcessed(false);
        $quote->save();

        $quote->getPayment()->importData(['method' => 'checkmo']);

        $quote->collectTotals()->save();

        $order = $this->quoteManagement->submit($quote);

        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if ($order->getEntityId()) {
            $result['order_id']= $order->getRealOrderId();
        } else {
            $result=['error'=>1,'msg'=>'Error in OrderHelper'];
        }
        return $result;
    }
}
