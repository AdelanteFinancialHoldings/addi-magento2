<?php

namespace Addi\Payment\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;

class View extends ProductView
{
    /**
     * @return mixed
     */
    public function getAddiCountry()
    {
        return $this->_scopeConfig->getValue("payment/addi/credentials/country");
    }

    public function isWidgetActive()
    {
        return $this->_scopeConfig->getValue("payment/addi/widget_styles/widget_active");
    }

    public function getStyles()
    {
        return json_encode(
            array(
            'widget'=> array(
                'borderColor'  => $this->_scopeConfig->getValue("payment/addi/widget_styles/border_color"),
                'borderRadius' => $this->_scopeConfig->getValue("payment/addi/widget_styles/border_radius"),
                'fontColor'    => $this->_scopeConfig->getValue("payment/addi/widget_styles/font_color"),
                'fontFamily'   => $this->_scopeConfig->getValue("payment/addi/widget_styles/font_family"),
                'fontSize'     => $this->_scopeConfig->getValue("payment/addi/widget_styles/font_size"),
                'badgeBackgroundColor'
                                =>$this->_scopeConfig->getValue("payment/addi/widget_styles/icon_background_color"),
                'infoBackgroundColor'
                                =>$this->_scopeConfig->getValue("payment/addi/widget_styles/widget_background_color"),
                'margin' => $this->_scopeConfig->getValue("payment/addi/widget_styles/margin"),
                'widget-addi-logo-white'
                                => (bool)$this->_scopeConfig->getValue("payment/addi/widget_styles/addi_logo_blank")
            ),
            'modal' => array(
                'backgroundColor' => $this->_scopeConfig->getValue("payment/addi/modal_styles/background_color"),
                'fontColor'       => $this->_scopeConfig->getValue("payment/addi/modal_styles/font_color"),
                'fontFamily'      => $this->_scopeConfig->getValue("payment/addi/modal_styles/font_family"),
                'PriceColor'      => $this->_scopeConfig->getValue("payment/addi/modal_styles/price_color"),
                'badgeBackgroundColor'
                                => $this->_scopeConfig->getValue("payment/addi/modal_styles/banner_background_color"),
                'badgeFontColor' => $this->_scopeConfig->getValue("payment/addi/modal_styles/banner_background_color"),
                'cardColor' => $this->_scopeConfig->getValue("payment/addi/modal_styles/backgrond_color_modal"),
                'buttonBorderColor' => $this->_scopeConfig->getValue("payment/addi/modal_styles/button_border_color"),
                'buttonBorderRadius' => $this->_scopeConfig->getValue("payment/addi/modal_styles/button_border_radius"),
                'buttonBackgroundColor'
                                => $this->_scopeConfig->getValue("payment/addi/modal_styles/button_background_color"),
                'buttonFontColor' => $this->_scopeConfig->getValue("payment/addi/modal_styles/button_font_color"),
            )
            )
        );
    }
}
