<?php

namespace MDevs\Shipping\Model;

use Magento\Framework\Model\AbstractModel;

class Shipment extends AbstractModel
{
    protected $_eventPrefix = 'md_shipments';

    protected function _construct()
    {
        $this->_init(\MDevs\Shipping\Model\ResourceModel\Shipment::class);
    }
}
