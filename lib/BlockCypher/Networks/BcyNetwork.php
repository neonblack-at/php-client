<?php namespace BlockCypher\Networks;

use BitWasp\Bitcoin\Network\Networks\Bitcoin;

class BcyNetwork extends Bitcoin
{
    protected $base58PrefixMap = [
        self::BASE58_ADDRESS_P2PKH => "1b",
        self::BASE58_ADDRESS_P2SH => "1f",
        self::BASE58_WIF => "ef",
    ];

}
