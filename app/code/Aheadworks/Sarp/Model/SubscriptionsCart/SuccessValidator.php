<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart;

use Aheadworks\Sarp\Model\Session as SarpSession;

/**
 * Class SuccessValidator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart
 */
class SuccessValidator
{
    /**
     * @var SarpSession
     */
    private $sarpSession;

    /**
     * @param SarpSession $sarpSession
     */
    public function __construct(SarpSession $sarpSession)
    {
        $this->sarpSession = $sarpSession;
    }

    /**
     * Checks if cart session data valid for achieve final state of create subscription process
     *
     * @return bool
     */
    public function isValid()
    {
        if (!$this->sarpSession->getLastSuccessCartId()
            || !$this->sarpSession->getLastProfileId()
        ) {
            return false;
        }
        return true;
    }
}
