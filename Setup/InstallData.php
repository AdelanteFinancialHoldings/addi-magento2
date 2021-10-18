<?php


namespace Addi\Payment\Setup;

use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{

    protected $_quoteSetupFactory;
    protected $_salesSetupFactory;

    /**
     * Constructor
     *
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->_quoteSetupFactory = $quoteSetupFactory;
        $this->_salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     */

    // @codingStandardsIgnoreStart
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
    // @codingStandardsIgnoreEnd
        $quoteSetup = $this->_quoteSetupFactory->create(array('setup' => $setup));
        $quoteSetup->addAttribute(
            'quote', 'addi_url',
            array(
                'type' => 'text',
                'length' => 2048,
                'visible' => false,
                'required' => false,
                'grid' => false
            )
        );

        $salesSetup = $this->_salesSetupFactory->create(array('setup' => $setup));
        $salesSetup->addAttribute(
            'order', 'addi_url',
            array(
                'type' => 'text',
                'length' => 2048,
                'visible' => false,
                'required' => false,
                'grid' => false
            )
        );
    }
}
