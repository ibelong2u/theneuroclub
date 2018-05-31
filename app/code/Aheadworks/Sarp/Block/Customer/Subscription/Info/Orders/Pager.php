<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription\Info\Orders;

/**
 * Class Pager
 * @package Aheadworks\Sarp\Block\Customer\Subscription\Info\Orders
 */
class Pager extends \Magento\Framework\View\Element\Template
{
    /**
     * @var int
     */
    private $itemsCount;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $pageSize = 5;

    /**
     * @var int
     */
    private $frameStart;

    /**
     * @var int
     */
    private $frameEnd;

    /**
     * @var int
     */
    private $frameLength = 5;

    /**
     * Get items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->itemsCount;
    }

    /**
     * Set items count
     *
     * @param int $itemsCount
     * @return $this
     */
    public function setItemsCount($itemsCount)
    {
        $this->itemsCount = $itemsCount;

        return $this;
    }

    /**
     * Get current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Set current page
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * Get last page number
     *
     * @return int
     */
    public function getLastPageNum()
    {
        return ceil($this->getItemsCount() / $this->getPageSize());
    }

    /**
     * Is current page first
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getCurrentPage() == 1;
    }

    /**
     * Is current page last
     *
     * @return bool
     */
    public function isLastPage()
    {
        return $this->getCurrentPage() >= $this->getLastPageNum();
    }

    /**
     * Check if page is current
     *
     * @param string $page
     * @return bool
     */
    public function isPageCurrent($page)
    {
        return $this->getCurrentPage() == $page;
    }

    /**
     * Get previous page url
     *
     * @return string
     */
    public function getPreviousPageUrl()
    {
        $previousPage = $this->getCurrentPage() - 1;
        if ($previousPage < 1) {
            $previousPage = 1;
        }
        return $this->getPageUrl($previousPage);
    }

    /**
     * Get next page url
     *
     * @return string
     */
    public function getNextPageUrl()
    {
        $nextPage = $this->getCurrentPage() + 1;
        if ($nextPage > $this->getLastPageNum()) {
            $nextPage = $this->getLastPageNum();
        }
        return $this->getPageUrl($nextPage);
    }

    /**
     * Get page url
     *
     * @param string $page
     * @return string
     */
    public function getPageUrl($page)
    {
        return $this->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_escape' => true,
                'page' => $page,
            ]
        );
    }

    /**
     * Get pages except first and last
     *
     * @return array
     */
    public function getFramePages()
    {
        $start = $this->getFrameStart();
        $end = $this->getFrameEnd();
        return range($start, $end);
    }

    /**
     * Is previous jump can be shown
     *
     * @return bool
     */
    public function canShowPreviousJump()
    {
        return $this->getPreviousJumpPage() !== null;
    }

    /**
     * Is next jump can be shown
     *
     * @return bool
     */
    public function canShowNextJump()
    {
        return $this->getNextJumpPage() !== null;
    }

    /**
     * Get previous jump page
     *
     * @return int|null
     */
    public function getPreviousJumpPage()
    {
        $frameStart = $this->getFrameStart();
        if ($frameStart - 1 > 1) {
            return max(2, $frameStart - $this->frameLength);
        }

        return null;
    }

    /**
     * Get next jump page
     *
     * @return int|null
     */
    public function getNextJumpPage()
    {
        $frameEnd = $this->getFrameEnd();
        if ($this->getLastPageNum() - $frameEnd > 1) {
            return min($this->getLastPageNum() - 1, $frameEnd + $this->frameLength);
        }

        return null;
    }

    /**
     * Get previous jump page
     *
     * @return string
     */
    public function getPreviousJumpUrl()
    {
        return $this->getPageUrl($this->getPreviousJumpPage());
    }

    /**
     * Get next jump page
     *
     * @return string
     */
    public function getNextJumpUrl()
    {
        return $this->getPageUrl($this->getNextJumpPage());
    }

    /**
     * Get frame start page
     *
     * @return int
     */
    public function getFrameStart()
    {
        if (!$this->frameStart) {
            $this->initFrame();
        }
        return $this->frameStart;
    }

    /**
     * Get frame end page
     *
     * @return int
     */
    public function getFrameEnd()
    {
        if (!$this->frameEnd) {
            $this->initFrame();
        }
        return $this->frameEnd;
    }

    /**
     * Initialize frame start end frame end page numbers
     *
     * @return $this
     */
    private function initFrame()
    {
        if ($this->getLastPageNum() <= $this->frameLength) {
            $start = 1;
            $end = $this->getLastPageNum();
        } else {
            $half = ceil($this->frameLength / 2);
            if ($this->getCurrentPage() >= $half &&
                $this->getCurrentPage() <= $this->getLastPageNum() - $half
            ) {
                $start = $this->getCurrentPage() - $half + 1;
                $end = $start + $this->frameLength - 1;
            } elseif ($this->getCurrentPage() < $half) {
                $start = 1;
                $end = $this->frameLength;
            } elseif ($this->getCurrentPage() > $this->getLastPageNum() - $half) {
                $start = $this->getLastPageNum() - $this->frameLength + 1;
                $end = $this->getLastPageNum();
            } else {
                $start = 0;
                $end = 0;
            }
        }
        $this->frameStart = $start;
        $this->frameEnd = $end;

        return $this;
    }

    /**
     * Is first page can be shown
     *
     * @return bool
     */
    public function canShowFirst()
    {
        return $this->frameLength > 1 && $this->getFrameStart() > 1;
    }

    /**
     * Is lasr page can be shown
     *
     * @return bool
     */
    public function canShowLast()
    {
        return $this->frameLength > 1 && $this->getFrameEnd() < $this->getLastPageNum();
    }

    /**
     * Get first page url
     *
     * @return string
     */
    public function getFirstPageUrl()
    {
        return $this->getPageUrl(1);
    }

    /**
     * Get last page url
     *
     * @return string
     */
    public function getLastPageUrl()
    {
        return $this->getPageUrl($this->getLastPageNum());
    }
}
