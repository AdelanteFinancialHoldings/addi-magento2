<?php

namespace Addi\Payment\Test\Unit\Model\Config\Backend;

use Addi\Payment\Model\Config\Backend\FetchCallbackCredentials;
use Addi\Payment\Service\CallbackCredentialsService;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class FetchCallbackCredentialsTest extends TestCase
{
    private $callbackCredentialsService;
    private $messageManager;
    private $storeManager;
    private $model;

    protected function setUp()
    {
        $this->callbackCredentialsService = $this->createMock(CallbackCredentialsService::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $context = $this->createMock(\Magento\Framework\Model\Context::class);
        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $config = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cacheTypeList = $this->createMock(\Magento\Framework\App\Cache\TypeListInterface::class);

        $this->model = new FetchCallbackCredentials(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $this->callbackCredentialsService,
            $this->messageManager,
            $this->storeManager
        );
    }

    public function testAfterSaveCallsRefreshForWebsiteScope()
    {
        $this->model->setScope('websites');
        $this->model->setScopeId(2);

        $this->callbackCredentialsService->expects($this->once())
            ->method('refreshCredentials')
            ->with(2)
            ->willReturn(true);

        $this->messageManager->expects($this->never())->method('addWarningMessage');

        $this->model->afterSave();
    }

    public function testAfterSaveAddsWarningWhenRefreshFails()
    {
        $this->model->setScope('websites');
        $this->model->setScopeId(1);

        $this->callbackCredentialsService->expects($this->once())
            ->method('refreshCredentials')
            ->with(1)
            ->willReturn(false);

        $this->messageManager->expects($this->once())->method('addWarningMessage');

        $this->model->afterSave();
    }

    public function testAfterSaveUsesDefaultScopeWhenScopeIsDefault()
    {
        $this->model->setScope('default');
        $this->model->setScopeId(0);

        $this->callbackCredentialsService->expects($this->once())
            ->method('refreshCredentials')
            ->with(0)
            ->willReturn(true);

        $this->model->afterSave();
    }
}
