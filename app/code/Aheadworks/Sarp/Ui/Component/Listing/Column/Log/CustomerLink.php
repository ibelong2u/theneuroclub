<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Ui\Component\Listing\Column\Log;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;

/**
 * Class CustomerLink
 * @package Aheadworks\Sarp\Ui\Component\Listing\Column\Log
 */
class CustomerLink extends \Aheadworks\Sarp\Ui\Component\Listing\Column\CustomerName
{
    /**
     * Format for guest info output
     */
    const GUEST_INFO_FORMAT = '%s <%s>';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerViewHelper
     */
    private $customerViewHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerViewHelper $customerViewHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        CustomerRepositoryInterface $customerRepository,
        CustomerViewHelper $customerViewHelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $url, $components, $data);
        $this->customerRepository = $customerRepository;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareLinkText(array $item)
    {
        $customerFullName = null;
        if (isset($item['customer_id'])) {
            $customerId = $item['customer_id'];
            $customer = $this->customerRepository->getById($customerId);
            $customerFullName = $this->customerViewHelper->getCustomerName($customer);
        }
        return $customerFullName;
    }

    /**
     * {@inheritdoc}
     */
    protected function preparePlainText(array $item)
    {
        $customerFullName = null;
        if (isset($item['customer_fullname']) && isset($item['customer_email'])) {
            $customerFullName = sprintf(self::GUEST_INFO_FORMAT, $item['customer_fullname'], $item['customer_email']);
        }
        return $customerFullName;
    }
}
