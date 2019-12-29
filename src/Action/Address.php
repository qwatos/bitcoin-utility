<?php

namespace App\Action;

use App\Action\Response\AddressInfo;
use App\Exception\Fatality;

class Address extends BaseAction
{
	/**
	 * @param string $address
	 * @return AddressInfo
	 * @throws Fatality
	 */
	public function getAddressInfo(string $address): AddressInfo
    {
		return $this->getBitcoinClient()->getAddressInfo($address);
    }
}
