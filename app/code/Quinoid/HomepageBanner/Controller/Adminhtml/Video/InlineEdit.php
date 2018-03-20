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

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * Video Factory
     *
     * @var \Quinoid\HomepageBanner\Model\VideoFactory
     */
    protected $videoFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Quinoid\HomepageBanner\Model\VideoFactory $videoFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->jsonFactory  = $jsonFactory;
        $this->videoFactory = $videoFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach (array_keys($postItems) as $videoId) {
            /** @var \Quinoid\HomepageBanner\Model\Video $video */
            $video = $this->videoFactory->create()->load($videoId);
            try {
                $videoData = $postItems[$videoId];//todo: handle dates
                $video->addData($videoData);
                $video->save();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithVideoId($video, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithVideoId($video, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithVideoId(
                    $video,
                    __('Something went wrong while saving the Banner.')
                );
                $error = true;
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Video id to error message
     *
     * @param \Quinoid\HomepageBanner\Model\Video $video
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithVideoId(\Quinoid\HomepageBanner\Model\Video $video, $errorText)
    {
        return '[Banner ID: ' . $video->getId() . '] ' . $errorText;
    }
}
