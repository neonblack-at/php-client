<?php

namespace BlockCypher\Crypto;

use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\EcAdapter\Adapter\EcAdapterInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Signature\Signature;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Crypto\Random\Rfc6979;
use BitWasp\Buffertools\Buffer;
use BlockCypher\Validation\ArgumentArrayValidator;

/**
 * Class Signer
 *
 * @package BlockCypher\Crypto
 */
class Signer
{
    /**
     * Sign array of hex data deterministically using deterministic k.
     *
     * @param string[] $hexDataToSign
     * @param PrivateKeyInterface|string $privateKey
     *
     * @return string[]
     * @throws \Exception
     */
    public static function signMultiple($hexDataToSign, $privateKey)
    {
        ArgumentArrayValidator::validate($hexDataToSign, 'hexDataToSign');

        $signatures = array();
        foreach ($hexDataToSign as $tosign) {
            $signatures[] = self::sign($tosign, $privateKey);
        }

        return $signatures;
    }

    /**
     * Sign hex data deterministically using deterministic k.
     *
     * @param string $hexDataToSign
     * @param PrivateKeyInterface|string $privateKey
     *
     * @return string
     * @throws \Exception
     */
    public static function sign($hexDataToSign, $privateKey)
    {
        if (is_string($privateKey)) {
            $privateKey = PrivateKeyManipulator::importPrivateKey($privateKey);
        }

        // Convert hex data to buffer
        $data = Buffer::hex($hexDataToSign);

        /** @var EcAdapterInterface $ecAdapter */
        $ecAdapter = Bitcoin::getEcAdapter();

        // Deterministic digital signature generation
        $sig = self::_sign($data, $privateKey, $ecAdapter);

        return $sig->getHex();
    }

    /**
     * @param Buffer $data
     * @param PrivateKeyInterface|string $privateKey
     * @param EcAdapterInterface $ecAdapter
     *
     * @return Signature
     */
    protected static function _sign($data, $privateKey, $ecAdapter)
    {
        $rbg = new Rfc6979($ecAdapter, $privateKey, $data, 'sha256');

        $randomK = $rbg->bytes(32);
        $math = $ecAdapter->getMath();
        $generator = $ecAdapter->getGenerator();

        $n = $generator->getOrder();
        $k = $math->mod($randomK->getGmp(), $n);
        $r = $generator->mul($k)->getX();

        if ($math->cmp($r, gmp_init(0, 10)) == 0) {
            throw new \RuntimeException('Random number r = 0');
        }

        $s = $math->mod(
            $math->mul(
                $math->inverseMod($k, $n),
                $math->mod(
                    $math->add(
                        $data->getGmp(),
                        $math->mul(
                            $privateKey->getSecret(),
                            $r
                        )
                    ),
                    $n
                )
            ),
            $n
        );

        if ($math->cmp($s, gmp_init(0, 10)) == 0) {
            throw new \RuntimeException('Signature s = 0');
        }

        if (!$ecAdapter->validateSignatureElement($s, true)) {
            $s = $math->sub($n, $s);
        }

        return new Signature($ecAdapter, $r, $s);
    }
}
