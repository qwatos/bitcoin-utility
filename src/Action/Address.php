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
	public function getAddressInfo($address)
    {
		return $this->getBitcoinClient()->getAddressInfo($address);
    }
}
