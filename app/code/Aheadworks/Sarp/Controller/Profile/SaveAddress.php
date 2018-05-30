<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Profile;

use Aheadworks\Sarp\Api\Data\ProfileAddressInterface;
use Aheadworks\Sarp\Api\Data\ProfileAddressInterfaceFactory;
use Aheadworks\Sarp\Api\ProfileManagementInterface;
use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveAddress
 * @package Aheadworks\Sarp\Controller\Profile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveAddress extends AbstractProfileAction
{
    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var ProfileAddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     * @param FormKeyValidator $formKeyValidator
     * @param ProfileAddressInterfaceFactory $addressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ProfileRepositoryInterface $profileRepository,
        FormKeyValidator $formKeyValidator,
        ProfileAddressInterfaceFactory $addressFactory,
        DataObjectHelper $dataObjectHelper,
        ProfileManagementInterface $profileManagement
    ) {
        parent::__construct($context, $customerSession, $profileRepository);
        $this->formKeyValidator = $formKeyValidator;
        $this->addressFactory = $addressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->profileManagement = $profileManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $addressData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($addressData && $this->isPostDataValid($addressData)) {
            /** @var ProfileAddressInterface $address */
            $address = $this->addressFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $address,
                $addressData,
                ProfileAddressInterface::class
            );
            try {
                $this->profileManagement->updateProfileAddress(
                    $addressData['profile_id'],
                    $addressData['address_type'],
                    $address
                );
                $this->messageManager->addSuccessMessage(__('The address was successfully updated.'));
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the address.')
                );
            }
        }
        return $resultRedirect->setRefererUrl();
    }

    /**
     * Check if post data is valid
     *
     * @param array $data
     * @return bool
     */
    private function isPostDataValid($data)
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return false;
        }
        if (!isset($data['profile_id']) || !isset($data['address_type'])) {
            return false;
        }
        return true;
    }
}
