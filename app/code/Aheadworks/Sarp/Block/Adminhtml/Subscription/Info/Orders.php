<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Info;

use Aheadworks\Sarp\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

/**
 * Class Orders
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Orders extends \Magento\Backend\Block\Template
{
    /**
     * Profile orders list page size
     */
    const PAGE_SIZE = 5;

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
    private $profileId;

    /**
     * @var int
     */
    private $page;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Sarp::subscription/info/orders.phtml';

    /**
     * @param Context $context
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
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        PriceCurrencyInterface $priceCurrency,
        OrderConfig $orderConfig,
        ProfileRepositoryInterface $profileRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->orderConfig = $orderConfig;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Set profile ID
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    /**
     * Set current page number
     *
     * @param int $page
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get profile ID
     *
     * @return int|null
     */
    private function getProfileId()
    {
        return $this->profileId ? : $this->getRequest()->getParam('profile_id');
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
        $profileId = $this->getProfileId();
        $page = $this->getPage();
        if ($profileId) {
            $this->searchCriteriaBuilder
                ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId)
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
        return [];
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
     * Get admin date
     *
     * @param string $date
     * @return \DateTime
     */
    public function getAdminDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
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
     * Render pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getLayout()->createBlock(
            \Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Orders\Pager::class,
            'orders_pager'
        );

        /* @var $pagerBlock \Aheadworks\Sarp\Block\Adminhtml\Subscription\Info\Orders\Pager */
        $pagerBlock
            ->setTemplate('Aheadworks_Sarp::subscription/info/orders/pager.phtml')
            ->setCurrentPage($this->getPage())
            ->setItemsCount($this->getTotalProfileOrdersCount())
            ->setPageSize(self::PAGE_SIZE);

        return $pagerBlock->toHtml();
    }
}
