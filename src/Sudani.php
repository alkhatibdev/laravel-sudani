<?php

namespace AlkhatibDev\LaravelSudani;

use AlkhatibDev\LaravelSudani\Exceptions\InvlalidConfigsValuesException;
use AlkhatibDev\LaravelSudani\EncryptRSA;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Sudani
{

    /**
     * Base SPay server url
     *
     * @var string
     */
    protected $baseURL;

    /**
     * Service code
     *
     * @var string
     */
    protected $serviceCode;

    /**
     * Provider key
     *
     * @var string
     */
    protected $providerKey;

    /**
     * Service username
     *
     * @var string
     */
    protected $username;

    /**
     * Service password
     *
     * @var string
     */
    protected $password;

    /**
     * Custom SPay token
     *
     * @var string
     */
    protected $token;

    /**
     * Create Sudani instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->baseURL = config('laravel-sudani.base_url');
        $this->password = config('laravel-sudani.password');
        $this->username = config('laravel-sudani.username');
        $this->providerKey = config('laravel-sudani.provider_key');
        $this->serviceCode = config('laravel-sudani.service_code');

        $this->validateConfigs();
    }

    /**
     * Login to SPay
     *
     * @return array
     */
    public function login()
    {
        $this->log("Before Login");

        $response = Http::post($this->baseURL . 'Login/', [
            'login' => $this->username,
            'password' => $this->getEncryptedPassword(),
        ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Login (Login/)");
        $this->log($response->body());

        return $body;
    }

    /**
     * Get public key
     *
     * @return array
     */
    public function getPublicKey()
    {
        $this->log("Before Get Publick Key");

        $response = Http::post($this->baseURL . 'GetPublicKey/', [
            'providerKey' => $this->providerKey,
        ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Get Publick Key (GetPublicKey/)");
        $this->log($response->body());

        return $body;
    }

    /**
     * Initial subscription
     *
     * @param string $phone
     * @return array
     */
    public function initiate($phone)
    {
        $this->log("Before Initiate");

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseURL . 'InitPay/', [
                'msisdn' => $phone,
                'serviceCode' => $this->serviceCode,
            ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Initiate (InitPay/)");
        $this->log($body);

        return $body;
    }

    /**
     * Verify OTP and complete payment/subscription
     *
     * @param string $otp
     * @param string $requestId
     * @return array
     */
    public function verify($otp, $requestId)
    {
        $this->log("Before Verify OTP");

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseURL . 'Payment/', [
                'pin' => $otp,
                'requestId' => $requestId,
            ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Verify OTP (Payment/)");
        $this->log($body);

        return $body;
    }

    /**
     * Check subscription status of a single phone
     *
     * @param string $phone
     * @return array
     */
    public function checkSubscription($phone)
    {
        $this->log("Before Check Subscription");

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseURL . 'CheckSubscription/', [
                'msisdn' => $phone,
                'serviceCode' => $this->serviceCode,
            ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Check Subscription (CheckSubscription/)");
        $this->log($body);

        return $body;
    }

    /**
     * Unsubscribe specific phone
     *
     * @param string $phone
     * @return array
     */
    public function unsubscribe($phone)
    {
        $this->log("Before Unsubscribe");

        $response = Http::withHeaders($this->getHeaders())
            ->post($this->baseURL . 'UnSubscribe/', [
                'msisdn' => $phone,
                'serviceCode' => $this->serviceCode,
            ]);

        $body = json_decode($response->getBody(), true);

        $this->log("Unsubscribe (UnSubscribe/)");
        $this->log($body);

        return $body;
    }

    /**
     * Get headers
     *
     * @return array
     */
    private function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'token' => $this->getAvailableToken(),
        ];
    }

    /**
     * Get SPay token
     *
     * @return string
     */
    public function token()
    {
        $this->log("Get Remote Token");

        $response = $this->login();

        if (isset($response['status']) && $response['status'] === true) {

            $this->log("Remote Token Fetched");

            $this->token = $response['token']; // To be cached into this instance

            return $response['token'];
        }

        return null;
    }

    /**
     * Get available SPay token, from loacl or remote
     *
     * @return string
     */
    private function getAvailableToken()
    {
        return $this->token ?? $this->token();
    }

    /**
     * Provide ready local SPay token
     *
     * @return AlkhatibDev\LaravelSudani\Sudani
     */
    public function withToken($token)
    {
        $this->log("Set Token Manually");

        $this->token = $token;

        return $this;
    }

    /**
     * Validate config file and its values
     *
     * @return void
     */
    public function validateConfigs()
    {
        $this->log("Validate Configs");

        if (
            is_null($this->baseURL) ||
            is_null($this->password) ||
            is_null($this->username) ||
            is_null($this->providerKey) ||
            is_null($this->serviceCode)
        ) {
            $message = __('The provided configs is invalid, make sure laravel-sudani config file is published and all its configs are set.');

            $this->log($message, 'error');

            throw new InvlalidConfigsValuesException($message);
        }
    }

    /**
     * Encrypt password using public key
     *
     * @param string $publicKey
     * @return string
     */
    public function encryptPassword($publicKey)
    {
        $key = EncryptRSA::getpublicKey($publicKey);

        return base64_encode(
            EncryptRSA::encrypt($this->password, $key)
        );
    }

    /**
     * Get encrypted password text
     *
     * @return string
     */
    public function getEncryptedPassword()
    {
        $response = $this->getPublicKey();

        if ($response['status'] === true) {
            return $this->encryptPassword($response['publicKey']);
        }

        return null;
    }

    /**
     * Custom log function
     *
     * @param any $data
     * @param string $type
     * @return void
     */
    private function log($data, $type = 'debug')
    {
        if (config('laravel-sudani.enable_logging', false)) {
            if ($type === 'error') {
                Log::error($data);
            } else {
                Log::debug($data);
            }
        }
    }

}
