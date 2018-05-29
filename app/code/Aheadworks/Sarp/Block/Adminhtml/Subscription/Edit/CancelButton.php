<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Aheadworks\Sarp\Model\SubscriptionEngine\ProfileActionValidator;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class CancelButton
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Edit
 */
class CancelButton implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileActionValidator
     */
    private $profileActionValidator;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileActionValidator $profileActionValidator
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ProfileRepositoryInterface $profileRepository,
        ProfileActionValidator $profileActionValidator
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->profileRepository = $profileRepository;
        $this->profileActionValidator = $profileActionValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $profileId = $this->request->getParam('profile_id');
        if ($profileId) {
            $profile = $this->profileRepository->get($profileId);
            if ($this->profileActionValidator->isValidForAction($profile, Action::CANCEL)) {
                $data = [
                    'label' => __('Cancel this Subscription'),
                    'class' => 'save',
                    'on_click' => sprintf(
                        "deleteConfirm('%s', '%s')",
                        __('Are you sure you want to do this?'),
                        $this->urlBuilder->getUrl('*/*/cancel', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 40
                ];
            }
        }
        return $data;
    }
}
