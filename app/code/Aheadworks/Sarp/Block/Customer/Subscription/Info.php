<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Profile\Source\Status as StatusSource;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Info
 * @package Aheadworks\Sarp\Block\Customer\Subscription
 */
class Info extends \Magento\Framework\View\Element\Template
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
     * @return ProfileInterface|null
     */
    public function getProfile()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        if ($profileId) {
            return $this->profileRepository->get($profileId);
        }
        return null;
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
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Subscription #%1', $this->getProfile()->getReferenceId())
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if (!$this->getProfile()) {
            return '';
        }
        return parent::_toHtml();
    }
}
