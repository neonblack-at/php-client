<?php namespace BlockCypher\Networks;

use BitWasp\Bitcoin\Network\Network;

class DogeNetwork extends Network
{
    protected $base58PrefixMap = [
        self::BASE58_ADDRESS_P2PKH => "1e",
        self::BASE58_ADDRESS_P2SH => "16",
        self::BASE58_WIF => "9e",
    ];

    protected $bip32PrefixMap = [
        self::BIP32_PREFIX_XPUB => "02fd3929",
        self::BIP32_PREFIX_XPRV => "02fd3955",
    ];

    protected $p2pMagic = "c0c0c0c0";

}
