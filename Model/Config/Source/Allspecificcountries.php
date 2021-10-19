<?php

namespace Addi\Payment\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Allspecificcountries implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => __('Specific Countries'))
        );
    }
}
