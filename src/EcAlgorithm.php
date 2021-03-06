<?php

namespace HttpSignatures;

class EcAlgorithm implements AlgorithmInterface
{
    /** @var string */
    private $digestName;

    /**
     * @param string $digestName
     */
    public function __construct($digestName)
    {
        $this->digestName = $digestName;
    }

    /**
     * @return string
     */
    public function name()
    {
        return sprintf('ec-%s', $this->digestName);
    }

    /**
     * @param string $key
     * @param string $data
     *
     * @return string
     *
     * @throws \HttpSignatures\AlgorithmException
     */
    public function sign($signingKey, $data)
    {
        $algo = $this->getEcHashAlgo($this->digestName);
        if (!openssl_get_privatekey($signingKey)) {
            throw new AlgorithmException("OpenSSL doesn't understand the supplied key (not valid or not found)");
        }
        $signature = '';
        openssl_sign($data, $signature, $signingKey, $algo);

        return $signature;
    }

    public function verify($message, $signature, $verifyingKey)
    {
        $algo = $this->getEcHashAlgo($this->digestName);

        return 1 === openssl_verify($message, base64_decode($signature), $verifyingKey, $algo);
    }

    private function getEcHashAlgo($digestName)
    {
        switch ($digestName) {
        case 'sha256':
            return OPENSSL_ALGO_SHA256;
        case 'sha1':
            return OPENSSL_ALGO_SHA1;
        default:
            throw new HttpSignatures\AlgorithmException($digestName.' is not a supported hash format');
      }
    }
}
