<?php
$classes = array(
    __DIR__ . '/src/Addi.php',
    __DIR__ . '/src/Data/Amount.php',
    __DIR__ . '/src/Data/Client.php',
    __DIR__ . '/src/Data/Item.php',
    __DIR__ . '/src/Data/Shipping.php',
    __DIR__ . '/src/Data/Billing.php',
    __DIR__ . '/src/Data/PickUpAddress.php',
);
foreach ($classes as $class) {
    require_once $class;
}
