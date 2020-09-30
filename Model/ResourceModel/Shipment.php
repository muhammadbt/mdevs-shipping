<?php

namespace MDevs\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Shipment extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('md_shipments', 'id');
    }
}
