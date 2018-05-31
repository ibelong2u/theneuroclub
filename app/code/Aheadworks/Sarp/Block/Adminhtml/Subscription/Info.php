<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Addresses as AddressesBlock;
use Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Orders as OrdersBlock;
use Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Plan as PlanBlock;
use Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Products as ProductsBlock;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Info
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription
 */
class Info extends \Magento\Backend\Block\Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscription/info.phtml';

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        StatusSource $statusSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->statusSource = $statusSource;
    }

    /**
     * Get profile entity
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        return $this->profileRepository->get($profileId);
    }

    /**
     * Get profile status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $statusOptions = $this->statusSource->getOptions();
        return $statusOptions[$this->getProfile()->getStatus()];
    }

    /**
     * Get orders block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getOrdersHtml()
    {
        $ordersBlock = $this->getLayout()
            ->createBlock(OrdersBlock::class, 'aw_sarp.subscription.orders');
        return $ordersBlock->toHtml();
    }

    /**
     * Get subscription plan block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSubscriptionPlanHtml()
    {
        /** @var PlanBlock $planBlock */
        $planBlock = $this->getLayout()
            ->createBlock(PlanBlock::class, 'aw_sarp.subscription.subscription_plan');
        return $planBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Get products block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getProductsHtml()
    {
        /** @var ProductsBlock $productsBlock */
        $productsBlock = $this->getLayout()
            ->createBlock(ProductsBlock::class, 'aw_sarp.subscription.products');
        return $productsBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * Get addresses, shipping and payment block html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAddressesHtml()
    {
        /** @var AddressesBlock $addressesBlock */
        $addressesBlock = $this->getLayout()
            ->createBlock(AddressesBlock::class, 'aw_sarp.subscription.addresses');
        return $addressesBlock
            ->setProfile($this->getProfile())
            ->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Title $pageTitle */
        $pageTitle = $this->getLayout()->getBlock('page.title');
        if ($pageTitle) {
            $pageTitle->setPageTitle(' ');
        }
        return $this;
    }
}
