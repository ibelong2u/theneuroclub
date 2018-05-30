<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Profile;

use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp\Model\Profile\Source\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Suspend
 * @package Aheadworks\Sarp\Controller\Profile
 */
class Suspend extends AbstractProfileAction
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param ProfileManagementInterface $profileManagement
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        ProfileManagementInterface $profileManagement,
        Session $customerSession,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct($context, $customerSession, $profileRepository);
        $this->profileManagement = $profileManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('profile_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($profileId && $this->isProfileBelongsToCustomer($profileId)) {
            try {
                $this->profileManagement->changeStatusAction($profileId, Action::SUSPEND);
                $this->messageManager->addSuccessMessage(__('The subscription was successfully suspended.'));
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while suspend the subscription.')
                );
            }
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }
}
