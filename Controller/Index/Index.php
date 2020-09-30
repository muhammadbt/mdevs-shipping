<?php

namespace MDevs\Shipping\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use MDevs\Shipping\Block\DeliveryOptions;

class Index extends Action
{
    /**
     * @var DeliveryOptions
     */
    private $deliveryOptions;

    /**
     * Index constructor.
     * @param Context $context
     * @param DeliveryOptions $deliveryOptions
     */
    public function __construct(
        Context $context,
        DeliveryOptions $deliveryOptions
    )
    {
        parent::__construct($context);
        $this->deliveryOptions = $deliveryOptions;
    }

    public function execute()
    {
        $postRequest = $this->getRequest()->getPost();

        if ($postRequest) {

            $postData = $this->getRequest()->getPostValue();
            $points = $this->deliveryOptions->getAllPoints($postData['zip']);

            echo json_encode($points);

        }

    }
}
