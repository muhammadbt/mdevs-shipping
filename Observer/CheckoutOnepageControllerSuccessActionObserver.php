<?php

namespace MDevs\Shipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use MDevs\Shipping\Model\ShipmentFactory;

class CheckoutOnepageControllerSuccessActionObserver implements ObserverInterface
{
    private $logger;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRespository;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * CheckoutOnepageControllerSuccessActionObserver constructor.
     * @param LoggerInterface $logger
     * @param OrderRepositoryInterface $orderRespository
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param ShipmentFactory $shipmentFactory
     */
    public function __construct(
        LoggerInterface $logger,
        OrderRepositoryInterface $orderRespository,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        ShipmentFactory $shipmentFactory
    )
    {
        $this->logger = $logger;
        $this->orderRepository = $orderRespository;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->shipmentFactory = $shipmentFactory;
    }

    public function execute(Observer $observer)
    {
        $orderID = $observer->getEvent()->getOrderIds()[0];
        $oI = $this->getOrderData($orderID);

        $cPath = "shippingConfig/ApiAccessConfig/";
        $token = $this->scopeConfig->getValue($cPath.'apiAccessToken');
        $sender = json_decode($this->scopeConfig->getValue($cPath.'apiSenderInfo'));

        $curl = curl_init();
        $url = "https://postbox.alfrednet.eu/alfred_api/v3/CreateShipment.php";
        $data = json_encode([
                "apitoken" => $token,
                "courier" => "hermes",
                "sender" => [
                    "name" => $sender->name,
                    "address" => $sender->address,
                    "zip" => $sender->zip,
                    "location" => $sender->location,
                    "province" => $sender->province,
                    "country" => $sender->country,
                    "phone" => $sender->phone,
                    "email" => $sender->email
                ],
                "receiver" => [
                    "name" => $oI->getShippingAddress()->getFirstName(),
                    "address" => $oI->getShippingAddress()->getStreet()[0],
                    "zip" => $oI->getShippingAddress()->getPostcode(),
                    "location" => $oI->getShippingAddress()->getStreet()[0],
                    "province" => $oI->getShippingAddress()->getRegion(),
                    "country" => "ITA", //$oI->getShippingAddress()->getCountryId(),
                    "phone" => $oI->getShippingAddress()->getTelephone(),
                    "email" => $oI->getShippingAddress()->getEmail()
                ],
                "shipment" => [
                    "date" => date("d/m/y"),
                    "content" => $oI->getAllVisibleItems()[0]->getName(),
                    "note" => $oI->getAllVisibleItems()[0]->getName(),
                    "cashOnDelivery" => 0,
                    "insurance" => 0,
                    "parcels" => [
                        ["height" => "0","width" => "0","depth" => "0","weight" => $oI->getAllVisibleItems()[0]->getWeight()],
                        ["height" => "0","width" => "0","depth" => "0","weight" => $oI->getAllVisibleItems()[0]->getWeight()]
                    ]
                ]
        ]);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain')
        ]);
        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if ($response->response_code==200) {

            $model = $this->shipmentFactory->create();

            $model->addData([
                "orderid" => $orderID,
                "main_barcode" => $response->payload->mainBarcode,
                "tracking_url" => $response->payload->urlTracking,
                "url_label" => $response->payload->urlLabel
            ]);

            $saveData = $model->save();

            if($saveData){
                $this->logger->debug("Insert Record Successfully !");
            }

            $this->messageManager->addSuccessMessage($response->message);
        } else {
            $this->messageManager->addErrorMessage("Error! Unable to create shipment. (".$response->message.")");
        }
    }

    private function getOrderData($order_id)
    {
        try {
            $order = $this->orderRepository->get($order_id);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('This order no longer exists.'));
        }
        return $order;
    }

}
