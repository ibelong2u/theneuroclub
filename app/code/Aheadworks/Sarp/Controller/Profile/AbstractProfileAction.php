<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Controller\Profile;

use Aheadworks\Sarp\Api\ProfileRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Class AbstractProfileAction
 * @package Aheadworks\Sarp\Controller\Profile
 */
abstract class AbstractProfileAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $profileRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ProfileRepositoryInterface $profileRepository
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * Check if profile belongs to current customer
     *
     * @param int $profileId
     * @return bool
     */
    protected function isProfileBelongsToCustomer($profileId)
    {
        try {
            $profile = $this->profileRepository->get($profileId);
            if ($profile->getId() && $profile->getCustomerId() == $this->customerSession->getCustomerId()) {
                return true;
            }
        } catch (\Exception $e) {
        }
        return false;
    }
}
