<?php

namespace AlkhatibDev\LaravelSudani;

use phpseclib\Crypt\RSA;

/**
 * Simple sodium crypto class for PHP >= 7.2
 * @author MRK
 */
class EncryptRSA {

    /**
     * Get decode public key
     *
     * @param $message - message to encrypt
     * @return type
     */
    static public function getpublicKey($publicKey) {
        $rsa = new RSA();
        $rsa->loadKey($publicKey);
        $rsa->setPublicKey($publicKey);
        return $rsa->getPublicKey();
    }

    /**
     * Encrypt a message
     *
     * @param $message - message to encrypt
     * @param $key - encryption key
     * @return string
     */
    static function encrypt($message, $key) {
        openssl_public_encrypt($message, $encrypted, $key, OPENSSL_PKCS1_PADDING);
        return $encrypted;
    }

    /**
     * Decrypt a message
     * @param $encrypted - message encrypted with safeEncrypt()
     * @param $key - key used for encryption
     * @return string
     */
    static function decrypt($encrypted, $key) {
        openssl_public_decrypt($encrypted, $plain, $key);
        return $plain;
    }

}
