<?php

namespace BlockCypher\Crypto;

use BitWasp\Bitcoin\Network\NetworkFactory;
use BlockCypher\Networks\BcyNetwork;
use BlockCypher\Networks\BethNetwork;
use BlockCypher\Networks\DogeNetwork;
use BlockCypher\Validation\CoinSymbolValidator;

/**
 * Class CoinSymbolNetworkMapping
 * @package BlockCypher\Crypto
 */
class CoinSymbolNetworkMapping
{
    /**
     * @param $coinSymbol
     * @return \BitWasp\Bitcoin\Network\Network
     * @throws \Exception
     */
    public static function getNetwork($coinSymbol)
    {
        CoinSymbolValidator::validate($coinSymbol, 'coinSymbol');

        $network = null;

        switch ($coinSymbol) {
            case 'btc':
                $network = NetworkFactory::bitcoin();
                break;
            case 'btc-testnet':
                $network = NetworkFactory::bitcoinTestnet();
                break;
            case 'ltc':
                $network = NetworkFactory::litecoin();
                break;
            case 'doge':
                $network = NetworkFactory::dogecoin();
                break;
            case 'dash':
                $network = NetworkFactory::dash();
                break;
            case 'bcy':
                // not used for the time being
                $network = new BcyNetwork();
                break;
            case 'eth':
                // not used for the time being
                $network = NetworkFactory::bitcoin();
                break;
            case 'beth':
                // not used for the time being
                $network = NetworkFactory::bitcoin();
                break;
            default:
                throw new \Exception("Unsupported coin symbol: $coinSymbol by php-client");
        }

        return $network;
    }
}
