<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost;

use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\Config;
use Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost;
use Magento\Framework\DataObject;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Authorizenet\SilentPost
 */
class Validator extends AbstractValidator
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Returns true if and only request is valid for processing
     *
     * @param DataObject $request
     * @return bool
     */
    public function isValid($request)
    {
        $this->_clearMessages();

        if (!\Zend_Validate::is($request->getXSubscriptionId(), 'NotEmpty')) {
            $this->_addMessages(['Subscription Id has not been specified.']);
        }
        if ($request->getXResponseCode() != SilentPost::RESPONSE_CODE_APPROVED) {
            $this->_addMessages(['Invalid response code.']);
        }
        if ($request->getXResponseReasonCode() != SilentPost::RESPONSE_REASON_CODE_APPROVED) {
            $this->_addMessages(['Invalid response reason code.']);
        }

        $md5Hash = $this->config->getMerchantMD5() . $request->getXTransId() . $request->getXAmount();
        if (strtoupper($request->getData('x_MD5_Hash')) != strtoupper(md5($md5Hash))) {
            $this->_addMessages(['Invalid MD5 hash value.']);
        }

        return empty($this->getMessages());
    }
}
