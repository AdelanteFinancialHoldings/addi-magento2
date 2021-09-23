<?php


namespace Addi\Payment\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    private $quoteSetupFactory;
    private $salesSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;

        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute('quote', 'addi_url',
            [
                'type' => 'text',
                'length' => 2048,
                'visible' => false,
                'required' => false,
                'grid' => false
            ]
        );

        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute('order', 'addi_url',
            [
                'type' => 'text',
                'length' => 2048,
                'visible' => false,
                'required' => false,
                'grid' => false
            ]
        );
    }
}
