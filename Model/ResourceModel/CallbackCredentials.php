<?php

namespace Addi\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CallbackCredentials extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('addi_callback_credentials', 'id');
    }
}
