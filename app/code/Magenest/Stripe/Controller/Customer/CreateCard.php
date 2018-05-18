<?php
/**
 * Created by PhpStorm.
 * User: thaivh
 * Date: 28/3/17
 * Time: 09:04
 */

namespace Magenest\Stripe\Controller\Customer;

use Magento\Framework\App\Action\Context;

class CreateCard extends \Magento\Framework\App\Action\Action
{
    protected $_formKeyValidator;
    protected $stripeHelper;
    protected $jsonFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\Stripe\Helper\Data $stripeHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
    
        parent::__construct($context);
        $this->_formKeyValidator = $formKeyValidator;
        $this->stripeHelper = $stripeHelper;
        $this->jsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'error' => true,
                'message' => 'Invalid Form Key'
            ]);
        }
        if ($this->getRequest()->isAjax()) {
            $requestResult = $this->getRequest()->getParam('result');
            if (isset($requestResult['source'])) {
                $customerSession = $this->_objectManager->create('\Magento\Customer\Model\Session');
                $customerId = $customerSession->getCustomerId();
                $stripeResponse = $requestResult['source'];
                if ($this->stripeHelper->saveCard($customerId, $stripeResponse)) {
                    return $result->setData([
                        'error' => false,
                        'success' => true
                    ]);
                } else {
                    return $result->setData([
                        'error' => true,
                        'message' => "Error"
                    ]);
                }
            }
        }
        return $result->setData([
            'error' => true,
            'message' => 'Invalid request'
        ]);
    }
}
