<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Block\Adminhtml\Subscription\Edit;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\SubscriptionEngine\EngineMetadataPool;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class RefreshButton
 * @package Aheadworks\Sarp\Block\Adminhtml\Subscription\Edit
 */
class RefreshButton implements ButtonProviderInterface
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
     * @var EngineMetadataPool
     */
    private $engineMetadataPool;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param ProfileRepositoryInterface $profileRepository
     * @param EngineMetadataPool $engineMetadataPool
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        ProfileRepositoryInterface $profileRepository,
        EngineMetadataPool $engineMetadataPool
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->profileRepository = $profileRepository;
        $this->engineMetadataPool = $engineMetadataPool;
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
            $engineMetadata = $this->engineMetadataPool->getMetadata($profile->getEngineCode());
            if ($engineMetadata->isGateway()) {
                $data = [
                    'label' => __('Refresh Data'),
                    'class' => 'save primary',
                    'on_click' => sprintf(
                        "location.href = '%s';",
                        $this->urlBuilder->getUrl('*/*/refresh', ['profile_id' => $profileId])
                    ),
                    'sort_order' => 50
                ];
            }
        }
        return $data;
    }
}
