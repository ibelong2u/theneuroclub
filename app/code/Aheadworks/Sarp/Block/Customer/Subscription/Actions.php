<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Customer\Subscription;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Aheadworks\Sarp\Model\SubscriptionEngine\ProfileActionValidator;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Actions
 * @package Aheadworks\Sarp\Block\Customer\Subscription
 */
class Actions extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileActionValidator
     */
    private $profileActionValidator;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @param Context $context
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileActionValidator $profileActionValidator
     * @param Session $customerSession
     * @param EngineMetadataPool $engineMetadataPool
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProfileRepositoryInterface $profileRepository,
        ProfileActionValidator $profileActionValidator,
        Session $customerSession,
        EngineMetadataPool $engineMetadataPool,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileRepository = $profileRepository;
        $this->profileActionValidator = $profileActionValidator;
        $this->customerSession = $customerSession;
        $this->engineMetadataPool = $engineMetadataPool;
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
     * Check if refresh action is enabled
     *
     * @return bool
     */
    public function isRefreshActionEnabled()
    {
        $profile = $this->profileRepository->get($this->getProfileId());
        $metadata = $this->engineMetadataPool->getMetadata($profile->getEngineCode());
        return $metadata->isGateway();
    }

    /**
     * Check if suspend action is enabled
     *
     * @return bool
     */
    public function isSuspendActionEnabled()
    {
        $profile = $this->profileRepository->get($this->getProfileId());
        return $this->profileActionValidator->isValidForAction($profile, Action::SUSPEND);
    }

    /**
     * Check if cancel action is enabled
     *
     * @return bool
     */
    public function isCancelActionEnabled()
    {
        $profile = $this->profileRepository->get($this->getProfileId());
        return $this->profileActionValidator->isValidForAction($profile, Action::CANCEL);
    }

    /**
     * Check if activate action is enabled
     *
     * @return bool
     */
    public function isActivateActionEnabled()
    {
        $profile = $this->profileRepository->get($this->getProfileId());
        return $this->profileActionValidator->isValidForAction($profile, Action::ACTIVATE);
    }

    /**
     * Get refresh url
     *
     * @return string
     */
    public function getRefreshUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/refresh',
            ['profile_id' => $this->getProfileId()]
        );
    }

    /**
     * Get suspend url
     *
     * @return string
     */
    public function getSuspendUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/suspend',
            ['profile_id' => $this->getProfileId()]
        );
    }

    /**
     * Get cancel url
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/cancel',
            ['profile_id' => $this->getProfileId()]
        );
    }

    /**
     * Get activate url
     *
     * @return string
     */
    public function getActivateUrl()
    {
        return $this->_urlBuilder->getUrl(
            'aw_sarp/profile/activate',
            ['profile_id' => $this->getProfileId()]
        );
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
}
