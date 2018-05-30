<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionsCart\Address;

use Aheadworks\Sarp\Api\Data\SubscriptionsCartAddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Directory\Model\CountryFactory;

/**
 * Class Validator
 * @package Aheadworks\Sarp\Model\SubscriptionsCart\Address
 */
class Validator extends AbstractValidator
{
    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(CountryFactory $countryFactory)
    {
        $this->countryFactory = $countryFactory;
    }

    /**
     * Returns true if and only if address entity meets the validation requirements
     *
     * @param SubscriptionsCartAddressInterface $address
     * @return bool
     */
    public function isValid($address)
    {
        $this->_clearMessages();

        $email = $address->getEmail();
        if ($email && !\Zend_Validate::is($email, 'EmailAddress')) {
            $this->_addMessages(['Invalid email format.']);
        }

        $countryId = $address->getCountryId();
        if ($countryId) {
            $countryCodeErrMessage = 'Invalid country code.';
            try {
                $country = $this->countryFactory->create()->loadByCode($countryId);
                if (!$country->getCountryId()) {
                    $this->_addMessages([$countryCodeErrMessage]);
                }
            } catch (LocalizedException $e) {
                $this->_addMessages([$countryCodeErrMessage]);
            }
        }

        return empty($this->getMessages());
    }
}
