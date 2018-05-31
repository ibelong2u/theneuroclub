<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model\SubscriptionEngine\Exception;

use Aheadworks\Sarp\Api\Exception\OperationIsNotSupportedExceptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class OperationIsNotSupportedException
 * @package Aheadworks\Sarp\Model\SubscriptionEngine\Exception
 */
class OperationIsNotSupportedException extends LocalizedException implements OperationIsNotSupportedExceptionInterface
{
}
