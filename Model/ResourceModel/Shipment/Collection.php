<?php

namespace MDevs\Shipping\Model\ResourceModel\Shipment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MDevs\Shipping\Model\Shipment;
use MDevs\Shipping\Model\ResourceModel\Shipment as ShipmentResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init(Shipment::class, ShipmentResource::class);
    }
}
