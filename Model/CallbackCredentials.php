<?php

namespace Addi\Payment\Model;

use Magento\Framework\Model\AbstractModel;

class CallbackCredentials extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Addi\Payment\Model\ResourceModel\CallbackCredentials::class);
    }
}
