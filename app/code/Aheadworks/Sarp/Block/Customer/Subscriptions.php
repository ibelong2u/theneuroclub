<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer;

use Aheadworks\Sarp\Model\Profile;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp\Model\ResourceModel\Profile\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Url as ProductUrl;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

/**
 * Class Subscriptions
 * @package Aheadworks\Sarp\Block\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Subscriptions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductUrl
     */
    private $productUrl;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param StatusSource $statusSource
     * @param ProductRepositoryInterface $productRepository
     * @param ProductUrl $productUrl
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        StatusSource $statusSource,
        ProductRepositoryInterface $productRepository,
        ProductUrl $productUrl,
        Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->statusSource = $statusSource;
        $this->productRepository = $productRepository;
        $this->productUrl = $productUrl;
        $this->customerSession = $customerSession;
    }

    /**
     * Get profiles
     *
     * @return Collection|null
     */
    public function getProfiles()
    {
        if ($this->customerSession->isLoggedIn()) {
            if (!$this->collection) {
                $this->collection = $this->collectionFactory->create();
                $this->collection
                    ->addFieldToFilter(
                        Profile::CUSTOMER_ID,
                        ['eq' => $this->customerSession->getCustomerId()]
                    )
                    ->addOrder(Profile::CREATED_AT, Collection::SORT_ORDER_DESC);
            }
            return $this->collection;
        }
        return null;
    }

    /**
     * Get status label
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        $statusOptions = $this->statusSource->getOptions();
        return $statusOptions[$status];
    }

    /**
     * Check if product exists
     *
     * @param int $productId
     * @return bool
     */
    public function isProductExists($productId)
    {
        try {
            $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * Check if product has url
     *
     * @param int $productId
     * @return bool
     */
    public function hasProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get product url
     *
     * @param int $productId
     * @return string
     */
    public function getProductUrl($productId)
    {
        /** @var ProductInterface|Product $product */
        $product = $this->productRepository->getById($productId);
        return $this->productUrl->getUrl($product);
    }

    /**
     * Get view profile url
     *
     * @param int $profileId
     * @return string
     */
    public function getViewUrl($profileId)
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/view',
            ['profile_id' => $profileId]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getProfiles()) {
            /** @var Pager $pager */
            $pager = $this->getLayout()
                ->createBlock(
                    Pager::class,
                    'aw_sarp.customer.subscriptions.pager'
                );
                $pager->setCollection($this->getProfiles());
            $this->setChild('pager', $pager);
            $this->getProfiles()->load();
        }
        return $this;
    }
}
