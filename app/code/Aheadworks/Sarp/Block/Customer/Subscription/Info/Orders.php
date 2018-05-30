<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

/**
 * Class Orders
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Orders extends \Magento\Framework\View\Element\Template
{
    /**
     * Profile orders list page size
     */
    const PAGE_SIZE = 5;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var int
     */
    private $page;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param OrderConfig $orderConfig
     * @param ProfileRepositoryInterface $profileRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        PriceCurrencyInterface $priceCurrency,
        OrderConfig $orderConfig,
        ProfileRepositoryInterface $profileRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->orderConfig = $orderConfig;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->getRequest()->getParam('profile_id');
    }

    /**
     * Get current page
     *
     * @return int
     */
    private function getPage()
    {
        if (!$this->page) {
            $page = $this->getRequest()->getParam('page', 1);
            $lastPageNum = $this->getLastPageNum();

            if (!is_numeric($page) || $page < 1) {
                $this->page = 1;
            } elseif ($page > $lastPageNum) {
                $this->page = $lastPageNum;
            } else {
                $this->page = (int)$page;
            }
        }
        return $this->page;
    }

    /**
     * Get last page num
     *
     * @return int
     */
    private function getLastPageNum()
    {
        return (int)ceil($this->getTotalProfileOrdersCount() / self::PAGE_SIZE);
    }

    /**
     * Get profile orders
     *
     * @return ProfileOrderInterface[]
     */
    public function getProfileOrders()
    {
        $page = $this->getPage();
        $this->searchCriteriaBuilder
            ->addFilter(ProfileOrderInterface::PROFILE_ID, $this->getProfileId())
            ->setPageSize(self::PAGE_SIZE)
            ->setCurrentPage($page);
        $orderDateOrder = $this->sortOrderBuilder
            ->setField(ProfileOrderInterface::ORDER_DATE)
            ->setDescendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($orderDateOrder);

        $searchResults = $this->profileOrderRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
        return $searchResults->getItems();
    }

    /**
     * Get total profile orders count
     *
     * @return int
     */
    public function getTotalProfileOrdersCount()
    {
        $profileId = $this->getProfileId();
        if ($profileId) {
            $this->searchCriteriaBuilder
                ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId);
            $searchResults = $this->profileOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            return $searchResults->getTotalCount();
        }
        return 0;
    }

    /**
     * Get left orders count
     *
     * @return bool|int
     */
    public function getOrdersLeftCount()
    {
        $ordersLeft = 0;
        try {
            /** @var \Aheadworks\Sarp\Api\Data\ProfileInterface $profile */
            $profile = $this->profileRepository->get($this->getProfileId());
            if ($profile->getTotalBillingCycles()) {
                $ordersLeft = $profile->getTotalBillingCycles();
                if ($profile->getTrialTotalBillingCycles()) {
                    $ordersLeft += $profile->getTrialTotalBillingCycles();
                }
                $ordersLeft = $ordersLeft - $this->getTotalProfileOrdersCount();
            }
        } catch (\Exception $e) {
            return false;
        }

        return $ordersLeft;
    }

    /**
     * Get displayed orders numbers
     *
     * @return string
     */
    public function getDisplayedOrdersNumbers()
    {
        $totalOrders = $this->getTotalProfileOrdersCount();
        if ($totalOrders > self::PAGE_SIZE) {
            $pageCount = ceil($totalOrders / self::PAGE_SIZE);
            if ($this->getPage() < $pageCount) {
                $frameStart = self::PAGE_SIZE * ($this->getPage() - 1) + 1;
                $frameEnd = self::PAGE_SIZE * ($this->getPage() - 1) + self::PAGE_SIZE;
                $displayedNumbers = $frameStart . '-' . $frameEnd;
            } else {
                $frameStart = self::PAGE_SIZE * ($this->getPage() - 1) + 1;
                $displayedNumbers = $frameStart . '-' . $totalOrders;
            }
        } else {
            $displayedNumbers = $totalOrders;
        }
        return $displayedNumbers;
    }

    /**
     * Get order view url
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl($orderId)
    {
        return $this->_urlBuilder->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Get order status label
     *
     * @param string $status
     * @return string
     */
    public function getOrderStatusLabel($status)
    {
        return $this->orderConfig->getStatusLabel($status);
    }

    /**
     * Format order amount
     *
     * @param float $amount
     * @param string $currencyCode
     * @return float
     */
    public function formatOrderAmount($amount, $currencyCode)
    {
        return $this->priceCurrency->format($amount, true, 2, null, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfileId() || !$this->customerSession->isLoggedIn()) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Render pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('orders_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Aheadworks\Sarp\Block\Customer\Subscription\Info\Orders\Pager */
            $pagerBlock
                ->setCurrentPage($this->getPage())
                ->setItemsCount($this->getTotalProfileOrdersCount())
                ->setPageSize(self::PAGE_SIZE);

            return $pagerBlock->toHtml();
        }

        return '';
    }
}
