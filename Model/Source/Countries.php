<?php

namespace Addi\Payment\Model\Source;

class Countries
{
    /**
     * @return array
     */
    public function getOptions()
    {
        return array(
            array('value' => 'CO', 'label' => 'Colombia'),
            array('value' => 'BR', 'label' => 'Brazil')
        );
    }
}
