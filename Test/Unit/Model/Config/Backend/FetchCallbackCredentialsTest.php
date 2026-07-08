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

    public function testAfterSaveUsesFieldsetDataAndCallsRefreshWithValues()
    {
        $this->model->setScope('websites');
        $this->model->setScopeId(2);
        $this->model->setData('fieldset_data', [
            'client_id'     => 'test_client_id',
            'client_secret' => 'test_secret',
            'country'       => 'CO',
            'sandbox'       => '1',
        ]);

        $this->callbackCredentialsService->expects($this->once())
            ->method('refreshCredentialsWithValues')
            ->with(2, 'test_client_id', 'test_secret', 'CO', true)
            ->willReturn(true);

        $this->messageManager->expects($this->never())->method('addWarningMessage');

        $this->model->afterSave();
    }

    public function testAfterSaveAddsWarningWhenRefreshFails()
    {
        $this->model->setScope('websites');
        $this->model->setScopeId(1);
        $this->model->setData('fieldset_data', [
            'client_id'     => 'test_client_id',
            'client_secret' => 'test_secret',
            'country'       => 'CO',
            'sandbox'       => '0',
        ]);

        $this->callbackCredentialsService->expects($this->once())
            ->method('refreshCredentialsWithValues')
            ->with(1, 'test_client_id', 'test_secret', 'CO', false)
            ->willReturn(false);

        $this->messageManager->expects($this->once())->method('addWarningMessage');

        $this->model->afterSave();
    }

    public function testAfterSaveAddsWarningWhenCredentialsMissingInFieldsetData()
    {
        $this->model->setScope('default');
        $this->model->setScopeId(0);
        $this->model->setData('fieldset_data', [
            'client_id'     => '',
            'client_secret' => '',
            'country'       => 'CO',
        ]);

        $this->callbackCredentialsService->expects($this->never())->method('refreshCredentialsWithValues');
        $this->messageManager->expects($this->once())->method('addWarningMessage');

        $this->model->afterSave();
    }
}
