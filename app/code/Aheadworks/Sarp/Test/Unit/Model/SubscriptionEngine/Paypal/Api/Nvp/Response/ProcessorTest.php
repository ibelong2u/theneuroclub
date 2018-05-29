<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionEngine\Paypal\Api\Nvp\Response;

use Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response\Processor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionEngine\Paypal\Api\Nvp\Response\Processor
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->processor = $objectManager->getObject(Processor::class);
    }

    public function testProcessRawResponse()
    {
        $rawResponse = 'HTTP/1.1 200 OK' . "\n\n"
            . 'PROFILESTATUS=PendingProfile&TIMESTAMP=2017%2d01%2d18T07%3a00%3a14Z&ACK=Success';
        $this->assertEquals(
            [
                'PROFILESTATUS' => 'PendingProfile',
                'TIMESTAMP' => '2017-01-18T07:00:14Z',
                'ACK' => 'Success'
            ],
            $this->processor->processRawResponse($rawResponse)
        );
    }
}
