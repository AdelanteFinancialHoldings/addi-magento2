<?php
namespace Addi\Payment\Logger;

use Monolog\Logger;
use \Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO; // @codingStandardsIgnoreLine

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/addi.log'; // @codingStandardsIgnoreLine
}
