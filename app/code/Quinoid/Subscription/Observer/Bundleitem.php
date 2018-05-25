<?php
namespace Quinoid\Subscription\Observer;
//use Psr\Log\LoggerInterface;

class Bundleitem implements \Magento\Framework\Event\ObserverInterface
{
  /** @var LoggerInterface  */
  /*  protected $_logger;

    public function __construct(
        LoggerInterface $logger,
        array $data = []
    )
    {
        $this->_logger = $logger;
        parent::__construct($data);
    }*/

  public function execute(\Magento\Framework\Event\Observer $observer)
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $logger = $objectManager->get("Psr\Log\LoggerInterface");
    $addedItemId = $observer->getRequest()->getParam('product');
     $logger->info('test'.$addedItemId); // add logs in system.log
    //  $this->_logger->addDebug("observer");
      // return $this;
  }
}
