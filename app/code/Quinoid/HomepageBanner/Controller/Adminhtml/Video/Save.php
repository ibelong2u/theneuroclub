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
namespace Quinoid\HomepageBanner\Controller\Adminhtml\Video;

class Save extends \Quinoid\HomepageBanner\Controller\Adminhtml\Video
{
    /**
     * Upload model
     *
     * @var \Quinoid\HomepageBanner\Model\Upload
     */
    protected $uploadModel;

    /**
     * File model
     *
     * @var \Quinoid\HomepageBanner\Model\Video\File
     */
    protected $fileModel;

    /**
     * Image model
     *
     * @var \Quinoid\HomepageBanner\Model\Video\Image
     */
    protected $imageModel;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * constructor
     *
     * @param \Quinoid\HomepageBanner\Model\Upload $uploadModel
     * @param \Quinoid\HomepageBanner\Model\Video\File $fileModel
     * @param \Quinoid\HomepageBanner\Model\Video\Image $imageModel
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Quinoid\HomepageBanner\Model\Upload $uploadModel,
        \Quinoid\HomepageBanner\Model\Video\File $fileModel,
        \Quinoid\HomepageBanner\Model\Video\Image $imageModel,
        \Magento\Backend\Model\Session $backendSession,
        \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->uploadModel    = $uploadModel;
        $this->fileModel      = $fileModel;
        $this->imageModel     = $imageModel;
        $this->backendSession = $backendSession;
        parent::__construct($videoFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('video');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $video = $this->initVideo();
            $video->setData($data);
            if ($data['upload_type'] == 0) {
                // $videothumbnail = $this->uploadModel->uploadFileAndGetName('videothumbnail', $this->imageModel->getBaseDir(), $data);
                // $video->setVideothumbnail($videothumbnail);
                $videofile = $this->uploadModel->uploadFileAndGetName('videofile', $this->fileModel->getBaseDir(), $data);
                $video['video_url'] = NULL;
            } elseif ($data['upload_type'] == 1) {
                $videofile = NULL;
            }
            $video->setVideofile($videofile);
            $this->_eventManager->dispatch(
                'quinoid_homepagebanner_video_prepare_save',
                [
                    'video' => $video,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $video->save();
                $this->messageManager->addSuccess(__('The Banner has been saved.'));
                $this->backendSession->setQuinoidHomepageBannerVideoData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'quinoid_homepagebanner/*/edit',
                        [
                            'video_id' => $video->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('quinoid_homepagebanner/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Banner.'));
            }
            $this->_getSession()->setQuinoidHomepageBannerVideoData($data);
            $resultRedirect->setPath(
                'quinoid_homepagebanner/*/edit',
                [
                    'video_id' => $video->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('quinoid_homepagebanner/*/');
        return $resultRedirect;
    }
}
