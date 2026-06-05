<?php

namespace Addi\Payment\Service;

use Addi\Payment\Model\CallbackCredentialsFactory;
use Addi\Payment\Logger\Logger as AddiLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class CallbackCredentialsService
{
    protected $_encryptor;
    protected $_scopeConfig;
    protected $_logger;
    protected $_callbackCredentialsFactory;

    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $scopeConfig,
        AddiLogger $logger,
        CallbackCredentialsFactory $callbackCredentialsFactory
    ) {
        $this->_encryptor = $encryptor;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_callbackCredentialsFactory = $callbackCredentialsFactory;
    }

    /**
     * @param int $websiteId
     * @param string $user
     * @param string $password
     * @return bool
     */
    public function saveCredentials($websiteId, $user, $password)
    {
        try {
            $model = $this->_callbackCredentialsFactory->create()->load($websiteId, 'website_id');
            $model->setWebsiteId($websiteId);
            $model->setUsernameEncrypted($this->_encryptor->encrypt($user));
            $model->setPasswordEncrypted($this->_encryptor->encrypt($password));
            $model->save();
            return true;
        } catch (\Exception $e) {
            $this->_logger->info('ADDI: Error saving notification credentials: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @param int $websiteId
     * @return array|null ['user' => string, 'password' => string] or null
     */
    public function getCredentials($websiteId)
    {
        try {
            $model = $this->_callbackCredentialsFactory->create()->load($websiteId, 'website_id');
            if (!$model->getId()) {
                return null;
            }
            return [
                'user'     => $this->_encryptor->decrypt($model->getUsernameEncrypted()),
                'password' => $this->_encryptor->decrypt($model->getPasswordEncrypted()),
            ];
        } catch (\Exception $e) {
            $this->_logger->info('ADDI: Error reading notification credentials: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param int $websiteId
     * @return array|null ['user' => string, 'password' => string] or null on failure
     */
    public function fetchFromApi($websiteId)
    {
        $clientId     = $this->getWebsiteConfig('payment/addi/credentials/client_id', $websiteId);
        $clientSecret = $this->getWebsiteConfig('payment/addi/credentials/client_secret', $websiteId);
        $country      = $this->getWebsiteConfig('payment/addi/credentials/country', $websiteId);
        $sandbox      = (bool)$this->getWebsiteConfig('payment/addi/credentials/sandbox', $websiteId);

        if (empty($clientId) || empty($clientSecret) || empty($country)) {
            $this->_logger->info(
                'ADDI: Cannot fetch notification credentials — operation credentials missing for website ' . $websiteId
            );
            return null;
        }

        try {
            $addi = new \Addi\Payment\lib\Addi(
                'temp', 'temp',
                $clientId, $clientSecret, $country,
                '', '', '', '', '',
                $sandbox
            );
            $token = $addi->getToken();
        } catch (\Exception $e) {
            $this->_logger->info('ADDI: Error getting JWT for credential fetch: ' . $e->getMessage());
            return null;
        }

        $baseUrl = $sandbox
            ? \Addi\Payment\lib\Addi::URL_SANDBOX_CO
            : \Addi\Payment\lib\Addi::URL_PRODUCTION_CO;

        $url = $baseUrl . 'v1/online-applications/callback-credentials';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->_logger->info(
            'ADDI: GET callback-credentials HTTP ' . $httpCode . ' for website ' . $websiteId
        );

        if ($httpCode !== 200) {
            $this->_logger->info('ADDI: Failed to fetch notification credentials. Response: ' . $result);
            return null;
        }

        $response = json_decode($result, true);
        if (empty($response['user']) || empty($response['password'])) {
            $this->_logger->info('ADDI: Unexpected response format from callback-credentials endpoint');
            return null;
        }

        return ['user' => $response['user'], 'password' => $response['password']];
    }

    /**
     * @param int $websiteId
     * @return bool
     */
    public function refreshCredentials($websiteId)
    {
        $credentials = $this->fetchFromApi($websiteId);
        if ($credentials === null) {
            return false;
        }
        return $this->saveCredentials($websiteId, $credentials['user'], $credentials['password']);
    }

    /**
     * @param string $incomingUser
     * @param string $incomingPassword
     * @param int $websiteId
     * @return bool
     */
    public function validateCredentials($incomingUser, $incomingPassword, $websiteId)
    {
        $stored = $this->getCredentials($websiteId);
        if ($stored === null) {
            return false;
        }
        return hash_equals($stored['user'], $incomingUser)
            && hash_equals($stored['password'], $incomingPassword);
    }

    /**
     * @param string $path
     * @param int $websiteId
     * @return mixed
     */
    private function getWebsiteConfig($path, $websiteId)
    {
        if ($websiteId > 0) {
            return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
        }
        return $this->_scopeConfig->getValue($path);
    }
}
