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

class Delete extends \Quinoid\HomepageBanner\Controller\Adminhtml\Video
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('video_id');
        if ($id) {
            $title = "";
            try {
                /** @var \Quinoid\HomepageBanner\Model\Video $video */
                $video = $this->videoFactory->create();
                $video->load($id);
                $title = $video->getTitle();
                $video->delete();
                $this->messageManager->addSuccess(__('The Banner has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_quinoid_homepagebanner_video_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                $resultRedirect->setPath('quinoid_homepagebanner/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_quinoid_homepagebanner_video_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('quinoid_homepagebanner/*/edit', ['video_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Banner to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('quinoid_homepagebanner/*/');
        return $resultRedirect;
    }
}
