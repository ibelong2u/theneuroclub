<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Test\Unit\Model\SubscriptionsCart;

use Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp\Model\SubscriptionsCart\BuyRequestProcessor
 */
class BuyRequestProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BuyRequestProcessor
     */
    private $buyRequestProcessor;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var ProductType|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productTypeMock;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectFactoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->productTypeMock = $this->createMock(ProductType::class);
        $this->objectFactoryMock = $this->createMock(DataObjectFactory::class);
        $this->buyRequestProcessor = $objectManager->getObject(
            BuyRequestProcessor::class,
            [
                'productRepository' => $this->productRepositoryMock,
                'productType' => $this->productTypeMock,
                'objectFactory' => $this->objectFactoryMock
            ]
        );
    }

    public function testSetQty()
    {
        $qty = 2;
        $buyRequestOriginal = 'a:2:{s:10:"product_id";s:1:"1";s:3:"qty";i:1;}';
        $buyRequestModified = 'a:2:{s:10:"product_id";s:1:"1";s:3:"qty";i:2;}';

        $this->assertEquals(
            $buyRequestModified,
            $this->buyRequestProcessor->setQty($buyRequestOriginal, $qty)
        );
    }

    public function testGetCartCandidates()
    {
        $productId = 1;
        $buyRequest = 'a:2:{s:10:"product_id";s:1:"1";s:3:"qty";i:1;}';

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $cartCandidateMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productTypeInstanceMock = $this->getMockForAbstractClass(
            AbstractType::class,
            [],
            '',
            false,
            false,
            true,
            ['prepareForCartAdvanced']
        );
        $buyRequestObjectMock = $this->createMock(DataObject::class);

        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId, false, null, true)
            ->willReturn($productMock);
        $this->productTypeMock->expects($this->once())
            ->method('factory')
            ->with($productMock)
            ->willReturn($productTypeInstanceMock);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with(unserialize($buyRequest))
            ->willReturn($buyRequestObjectMock);
        $productTypeInstanceMock->expects($this->once())
            ->method('prepareForCartAdvanced')
            ->with($buyRequestObjectMock, $productMock, AbstractType::PROCESS_MODE_FULL)
            ->willReturn([$cartCandidateMock]);

        $this->buyRequestProcessor->getCartCandidates($buyRequest, $productId);
        $this->assertEquals(
            [$cartCandidateMock],
            $this->buyRequestProcessor->getCartCandidates($buyRequest, $productId)
        );
    }
}
