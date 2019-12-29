<?php

namespace App\Action\Traits;

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Address\Base58AddressInterface;
use BitWasp\Bitcoin\Network\NetworkInterface;
use App\Exception\Fatality;

trait Base58AddressTrait
{
	/**
	 * @param string $addressString
	 * @param NetworkInterface $network
	 * @return Base58AddressInterface
	 * @throws Fatality
	 */
	protected function getBase58AddressFromString(string $addressString, NetworkInterface $network): Base58AddressInterface
	{
		if (!$addressString) {
			throw new Fatality('Error: empty bitcoin address');
		}

		$addressCreator = new AddressCreator();
		$address = $addressCreator->fromString($addressString, $network);
		if (!$address instanceof Base58AddressInterface) {
			throw new Fatality('Error: bitcoin address is not Base58');
		}

		return $address;
	}
}
