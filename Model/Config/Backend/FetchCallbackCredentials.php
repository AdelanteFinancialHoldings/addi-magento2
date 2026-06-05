<?php

namespace Addi\Payment\Model\Config\Backend;

use Addi\Payment\Service\CallbackCredentialsService;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class FetchCallbackCredentials extends Value
{
    protected $_callbackCredentialsService;
    protected $_messageManager;
    protected $_storeManager;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CallbackCredentialsService $callbackCredentialsService,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->_callbackCredentialsService = $callbackCredentialsService;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
    }

    public function afterSave()
    {
        $websiteId = $this->resolveWebsiteId();
        $result = $this->_callbackCredentialsService->refreshCredentials($websiteId);

        if (!$result) {
            $this->_messageManager->addWarningMessage(
                __('Addi: there was a problem fetching the notification credentials. '
                    . 'Transactions may be affected. Please contact support.')
            );
        }

        return parent::afterSave();
    }

    /**
     * @return int
     */
    private function resolveWebsiteId()
    {
        $scope   = $this->getScope();
        $scopeId = (int)$this->getScopeId();

        if ($scope === 'websites') {
            return $scopeId;
        }

        if ($scope === 'stores') {
            try {
                return (int)$this->_storeManager->getStore($scopeId)->getWebsiteId();
            } catch (\Exception $e) {
                return 0;
            }
        }

        return 0;
    }
}
