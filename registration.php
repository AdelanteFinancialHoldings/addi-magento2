<?php
require_once "lib/autoloader.php";

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Addi_Payment',
    __DIR__
);

