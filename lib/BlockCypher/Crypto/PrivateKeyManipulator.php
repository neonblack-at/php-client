<?php

namespace BlockCypher\Crypto;

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BlockCypher\Exception\BlockCypherInvalidPrivateKeyException;
use BlockCypher\Validation\CoinSymbolValidator;

/**
 * Class PrivateKeyManipulator
 * @package BlockCypher\Crypto
 */
class PrivateKeyManipulator
{
    /**
     * @param string $plainPrivateKey
     * @return PrivateKeyInterface
     */
    public static function importPrivateKey($plainPrivateKey)
    {
        $privateKey = null;
        $extraMsg = '';

        // TODO: Code Review. Method to detect private key format.

        $factory = new PrivateKeyFactory();
        if ($privateKey === null) {
            try {
                $privateKey = $factory->fromWif($plainPrivateKey);
            } catch (\Exception $e) {
                $extraMsg .= " Error trying to import from Wif: " . $e->getMessage();
            }
        }

        if ($privateKey === null) {
            try {
                $privateKey = $factory->fromHexCompressed($plainPrivateKey);
            } catch (\Exception $e) {
                $extraMsg .= " Error trying to import from Hex: " . $e->getMessage();
            }
        }

        if ($privateKey === null) {
            throw new \InvalidArgumentException("Invalid private key format. " . $extraMsg);
        }

        return $privateKey;
    }

    /**
     * @param string $wifPrivateKey
     * @return PrivateKeyInterface
     * @throws \Exception
     */
    public static function importPrivateKeyFromWif($wifPrivateKey)
    {
        $privateKey = PrivateKeyFactory::fromWif($wifPrivateKey);
        return $privateKey;
    }

    /**
     * @param string $hexPrivateKey
     * @param bool $compressed
     * @return string
     */
    public static function generateHexPubKeyFromHexPrivKey($hexPrivateKey, $compressed = true)
    {
        $factory = new PrivateKeyFactory();
        $privateKey = $compressed ? $factory->fromHexCompressed($hexPrivateKey) : $factory->fromHexUncompressed($hexPrivateKey);
        $hexPublicKey = $privateKey->getPublicKey()->getHex();
        return $hexPublicKey;
    }

    /**
     * @param string $hexPrivateKey
     * @param bool $compressed
     * @return PrivateKeyInterface
     * @throws BlockCypherInvalidPrivateKeyException
     */
    public static function importPrivateKeyFromHex($hexPrivateKey, $compressed = true)
    {
        $privateKey = null;

        try {
            $factory = new PrivateKeyFactory();
            $privateKey = $compressed ? $factory->fromHexCompressed($hexPrivateKey) : $factory->fromHexUncompressed($hexPrivateKey);
        } catch (\Exception $e) {
            throw new BlockCypherInvalidPrivateKeyException('Invalid private key format, hex expected.' . $e->getMessage());
        }

        return $privateKey;
    }

    /**
     * @param PrivateKeyInterface $privateKey
     * @param string $coinSymbol
     * @return mixed
     * @throws \Exception
     */
    public static function getAddressFromPrivateKey($privateKey, $coinSymbol)
    {
        CoinSymbolValidator::validate($coinSymbol, 'coinSymbol');

        $network = CoinSymbolNetworkMapping::getNetwork($coinSymbol);

        $publicKey = $privateKey->getPublicKey();
        $hash = $publicKey->getPubKeyHash();
        $address = (new PayToPubKeyHashAddress($hash))->getAddress($network);

        return $address;
    }
}
