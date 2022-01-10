<?php

namespace Addi\Payment\Block\Product;

use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Store\Model\ScopeInterface;

class View extends ProductView
{
    /**
     * @return mixed
     */
    public function getAddiCountry()
    {
        return $this->getConfig("payment/addi/credentials/country");
    }

    /**
     * @return bool
     */
    public function isWidgetActive()
    {
        return boolval($this->getConfig("payment/addi/widget_styles/widget_active"));
    }

    /**
     * @return mixed
     */
    public function getAllySlug()
    {
        return $this->getConfig("payment/addi/credentials/ally_slug");
    }

    public function getStyles()
    {
        return json_encode(
            array(
            'widget'=> array(
                'borderColor'  => $this->getConfig("payment/addi/widget_styles/border_color"),
                'borderRadius' => $this->getConfig("payment/addi/widget_styles/border_radius"),
                'fontColor'    => $this->getConfig("payment/addi/widget_styles/font_color"),
                'fontFamily'   => $this->getConfig("payment/addi/widget_styles/font_family"),
                'fontSize'     => $this->getConfig("payment/addi/widget_styles/font_size"),
                'badgeBackgroundColor'
                                =>$this->getConfig("payment/addi/widget_styles/icon_background_color"),
                'infoBackgroundColor'
                                =>$this->getConfig("payment/addi/widget_styles/widget_background_color"),
                'margin' => $this->getConfig("payment/addi/widget_styles/margin"),
                'widget-addi-logo-white'
                                => (bool)$this->getConfig("payment/addi/widget_styles/addi_logo_blank")
            ),
            'modal' => array(
                'backgroundColor' => $this->getConfig("payment/addi/modal_styles/background_color"),
                'fontColor'       => $this->getConfig("payment/addi/modal_styles/font_color"),
                'fontFamily'      => $this->getConfig("payment/addi/modal_styles/font_family"),
                'PriceColor'      => $this->getConfig("payment/addi/modal_styles/price_color"),
                'badgeBackgroundColor'
                                => $this->getConfig("payment/addi/modal_styles/banner_background_color"),
                'badgeFontColor' => $this->getConfig("payment/addi/modal_styles/banner_background_color"),
                'cardColor' => $this->getConfig("payment/addi/modal_styles/backgrond_color_modal"),
                'buttonBorderColor' => $this->getConfig("payment/addi/modal_styles/button_border_color"),
                'buttonBorderRadius' => $this->getConfig("payment/addi/modal_styles/button_border_radius"),
                'buttonBackgroundColor'
                                => $this->getConfig("payment/addi/modal_styles/button_background_color"),
                'buttonFontColor' => $this->getConfig("payment/addi/modal_styles/button_font_color"),
            )
            )
        );
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }
}
