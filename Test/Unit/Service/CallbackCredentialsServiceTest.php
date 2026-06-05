<?php

namespace Addi\Payment\Test\Unit\Service;

use Addi\Payment\Model\CallbackCredentials;
use Addi\Payment\Model\CallbackCredentialsFactory;
use Addi\Payment\Service\CallbackCredentialsService;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Addi\Payment\Logger\Logger as AddiLogger;
use PHPUnit\Framework\TestCase;

class CallbackCredentialsServiceTest extends TestCase
{
    private $encryptor;
    private $scopeConfig;
    private $logger;
    private $callbackCredentialsFactory;
    private $service;

    protected function setUp()
    {
        $this->encryptor = $this->createMock(EncryptorInterface::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->logger = $this->createMock(AddiLogger::class);
        $this->callbackCredentialsFactory = $this->createMock(CallbackCredentialsFactory::class);

        $this->service = new CallbackCredentialsService(
            $this->encryptor,
            $this->scopeConfig,
            $this->logger,
            $this->callbackCredentialsFactory
        );
    }

    public function testSaveCredentialsEncryptsAndSaves()
    {
        $websiteId = 1;
        $user = 'test_user';
        $password = 'test_pass';

        $this->encryptor->expects($this->exactly(2))
            ->method('encrypt')
            ->willReturnMap([
                [$user, 'enc_user'],
                [$password, 'enc_pass'],
            ]);

        $model = $this->createMock(CallbackCredentials::class);
        $model->expects($this->once())->method('load')->with($websiteId, 'website_id')->willReturnSelf();
        $model->expects($this->once())->method('setWebsiteId')->with($websiteId)->willReturnSelf();
        $model->expects($this->once())->method('setUsernameEncrypted')->with('enc_user')->willReturnSelf();
        $model->expects($this->once())->method('setPasswordEncrypted')->with('enc_pass')->willReturnSelf();
        $model->expects($this->once())->method('save');

        $this->callbackCredentialsFactory->expects($this->once())
            ->method('create')
            ->willReturn($model);

        $result = $this->service->saveCredentials($websiteId, $user, $password);
        $this->assertTrue($result);
    }

    public function testGetCredentialsReturnsDecryptedValues()
    {
        $websiteId = 1;

        $model = $this->createMock(CallbackCredentials::class);
        $model->method('load')->with($websiteId, 'website_id')->willReturnSelf();
        $model->method('getId')->willReturn(1);
        $model->method('getUsernameEncrypted')->willReturn('enc_user');
        $model->method('getPasswordEncrypted')->willReturn('enc_pass');

        $this->callbackCredentialsFactory->method('create')->willReturn($model);

        $this->encryptor->expects($this->exactly(2))
            ->method('decrypt')
            ->willReturnMap([
                ['enc_user', 'test_user'],
                ['enc_pass', 'test_pass'],
            ]);

        $result = $this->service->getCredentials($websiteId);

        $this->assertSame(['user' => 'test_user', 'password' => 'test_pass'], $result);
    }

    public function testGetCredentialsReturnsNullWhenNoRecord()
    {
        $websiteId = 99;

        $model = $this->createMock(CallbackCredentials::class);
        $model->method('load')->with($websiteId, 'website_id')->willReturnSelf();
        $model->method('getId')->willReturn(null);

        $this->callbackCredentialsFactory->method('create')->willReturn($model);

        $result = $this->service->getCredentials($websiteId);
        $this->assertNull($result);
    }

    public function testValidateCredentialsReturnsTrueOnMatch()
    {
        $websiteId = 1;

        $model = $this->createMock(CallbackCredentials::class);
        $model->method('load')->willReturnSelf();
        $model->method('getId')->willReturn(1);
        $model->method('getUsernameEncrypted')->willReturn('enc_user');
        $model->method('getPasswordEncrypted')->willReturn('enc_pass');

        $this->callbackCredentialsFactory->method('create')->willReturn($model);

        $this->encryptor->method('decrypt')
            ->willReturnMap([['enc_user', 'user1'], ['enc_pass', 'pass1']]);

        $result = $this->service->validateCredentials('user1', 'pass1', $websiteId);
        $this->assertTrue($result);
    }

    public function testValidateCredentialsReturnsFalseOnMismatch()
    {
        $websiteId = 1;

        $model = $this->createMock(CallbackCredentials::class);
        $model->method('load')->willReturnSelf();
        $model->method('getId')->willReturn(1);
        $model->method('getUsernameEncrypted')->willReturn('enc_user');
        $model->method('getPasswordEncrypted')->willReturn('enc_pass');

        $this->callbackCredentialsFactory->method('create')->willReturn($model);

        $this->encryptor->method('decrypt')
            ->willReturnMap([['enc_user', 'user1'], ['enc_pass', 'pass1']]);

        $result = $this->service->validateCredentials('wrong_user', 'wrong_pass', $websiteId);
        $this->assertFalse($result);
    }

    public function testValidateCredentialsReturnsFalseWhenNoStoredCredentials()
    {
        $websiteId = 1;

        $model = $this->createMock(CallbackCredentials::class);
        $model->method('load')->willReturnSelf();
        $model->method('getId')->willReturn(null);

        $this->callbackCredentialsFactory->method('create')->willReturn($model);

        $result = $this->service->validateCredentials('any', 'any', $websiteId);
        $this->assertFalse($result);
    }

    public function testRefreshCredentialsReturnsTrueOnSuccess()
    {
        $websiteId = 1;

        $service = $this->getMockBuilder(CallbackCredentialsService::class)
            ->setConstructorArgs([
                $this->encryptor,
                $this->scopeConfig,
                $this->logger,
                $this->callbackCredentialsFactory,
            ])
            ->setMethods(['fetchFromApi', 'saveCredentials'])
            ->getMock();

        $service->expects($this->once())
            ->method('fetchFromApi')
            ->with($websiteId)
            ->willReturn(['user' => 'u', 'password' => 'p']);

        $service->expects($this->once())
            ->method('saveCredentials')
            ->with($websiteId, 'u', 'p')
            ->willReturn(true);

        $this->assertTrue($service->refreshCredentials($websiteId));
    }

    public function testRefreshCredentialsReturnsFalseWhenFetchFails()
    {
        $websiteId = 1;

        $service = $this->getMockBuilder(CallbackCredentialsService::class)
            ->setConstructorArgs([
                $this->encryptor,
                $this->scopeConfig,
                $this->logger,
                $this->callbackCredentialsFactory,
            ])
            ->setMethods(['fetchFromApi'])
            ->getMock();

        $service->expects($this->once())
            ->method('fetchFromApi')
            ->with($websiteId)
            ->willReturn(null);

        $this->assertFalse($service->refreshCredentials($websiteId));
    }
}
