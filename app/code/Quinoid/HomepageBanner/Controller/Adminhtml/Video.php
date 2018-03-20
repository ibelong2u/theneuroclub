<?php
/**
 * Quinoid_HomepageBanner extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  Quinoid
 *                     @package   Quinoid_HomepageBanner
 *                     @copyright Copyright (c) 2017
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Quinoid\HomepageBanner\Controller\Adminhtml;

abstract class Video extends \Magento\Backend\App\Action
{
    /**
     * Video Factory
     *
     * @var \Quinoid\HomepageBanner\Model\VideoFactory
     */
    protected $videoFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * constructor
     *
     * @param \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->videoFactory          = $videoFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Video
     *
     * @return \Quinoid\HomepageBanner\Model\Video
     */
    protected function initVideo()
    {
        $videoId  = (int) $this->getRequest()->getParam('video_id');
        /** @var \Quinoid\HomepageBanner\Model\Video $video */
        $video    = $this->videoFactory->create();
        if ($videoId) {
            $video->load($videoId);
        }
        $this->coreRegistry->register('quinoid_homepagebanner_video', $video);
        return $video;
    }
}
