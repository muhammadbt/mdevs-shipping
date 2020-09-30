<?php

namespace MDevs\Shipping\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Message\ManagerInterface;

class DeliveryOptions extends Template
{
    private $apiUrl = "https://postbox.alfrednet.eu/alfred_api/v3/";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * DeliveryOptions constructor.
     * @param Template\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $writer
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writer,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->storeManager = $storeManager;
        $this->requestAccessTokenIfNotExist();
        $this->messageManager = $messageManager;
    }

    /**
     * @return array
     */
    public function showListOfOptions()
    {
        $options = [];
        if ($this->getShipConf("deliveryToAddress")) {
            $options['da'] = $this->getShipConf("deliveryToAddressLabel");
        }
        if ($this->getShipConf("alfredPoint")) {
            $options['ap'] = $this->getShipConf("alfredPointLabel");
        }
        if ($this->getShipConf("alfredLocker")) {
            $options['al'] = $this->getShipConf("alfredLockerLabel");
        }
        return $options;
    }

    public function getAllPoints ($zipcode=false)
    {
        if ($zipcode) {

            $response = json_decode($this->requestEndPoint('GetPoints.php', [
                "apitoken" => $this->getApiAccConf('apiAccessToken'),
                "zip" => $zipcode
            ]));

            if ( $response->response_code==200 ) {
                return $response->items;
            }

            return [];

        } else {

            $response = json_decode($this->requestEndPoint('GetAllPoints.php', [
                "apitoken" => $this->getApiAccConf('apiAccessToken')
            ]));

            if ( $response->response_code==200 ) {
                return $response->items;
            }

        }

        return [];

    }

    public function getAjaxUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    private function requestAccessTokenIfNotExist ()
    {

        if( $this->getApiAccConf("apiAccessToken")=='' ) {

            $response = json_decode($this->requestEndPoint('GetToken.php', [
                "user"=> "gianorla19@gmail.com",
                "password"=> "gianfranco"
            ]));

            if ($response->response_code==200) {
                $this->writer->save('shippingConfig/ApiAccessConfig/apiAccessToken', $response->token);
                $this->writer->save('shippingConfig/ApiAccessConfig/apiSenderInfo', json_encode($response->sender));
            } else {
                $this->messageManager->addErrorMessage("Access denied! unable to get the token.");
            }

        }

    }

    /**
     * @param $fieldPath
     * @return mixed
     */
    private function getConfigValue ($fieldPath)
    {
        return $this->scopeConfig->getValue($fieldPath);
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getShipConf($field) {
        return $this->scopeConfig->getValue("shippingConfig/shippingAddConfig/".$field);
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getApiAccConf($field) {
        return $this->scopeConfig->getValue("shippingConfig/ApiAccessConfig/".$field);
    }

    /**
     * @param $url
     * @param array $data
     * @return bool|string
     */
    private function requestEndPoint($endPoint, array $data)
    {

        $curl = curl_init();

        $url = $this->apiUrl.$endPoint;

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

}
