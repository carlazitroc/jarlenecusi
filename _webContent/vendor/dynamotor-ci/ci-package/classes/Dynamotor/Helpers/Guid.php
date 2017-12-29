<?php 

namespace Dynamotor\Helpers;


class Guid_System
{
	static function currentTimeMillis()
	{
		list($usec, $sec) = explode(" ",microtime());
		return $sec.substr($usec, 2, 3);
	}

}

class Guid_NetAddress
{

	var $Name = 'localhost';
	var $IP = '127.0.0.1';

static function getLocalHost() // static
{
	$address = new Guid_NetAddress();
	$address->Name = isset($_ENV["COMPUTERNAME"])? $_ENV["COMPUTERNAME"]:$_SERVER['HTTP_HOST'];
	$address->IP = isset($_SERVER['LOCAL_ADDR'])?$_SERVER['LOCAL_ADDR']:$_SERVER["SERVER_ADDR"];

	return $address;
}

function toString()
{
	return strtolower($this->Name.'/'.$this->IP);
}

}

class Guid_Random
{
	static function nextLong()
	{
		$tmp = rand(0,1)?'-':'';
		return $tmp.rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(100, 999).rand(100, 999);
	}
}

class Guid
{

	static function newGuid()
	{
		$Guid = new Guid();
		return $Guid->toString();
	}

	var $valueBeforeMD5;
	var $valueAfterMD5;

	function __construct()
	{
		$this->getGuid();
	}
//
	function getGuid()
	{
		$address = Guid_NetAddress::getLocalHost();
		$this->valueBeforeMD5 = $address->toString().':'.Guid_System::currentTimeMillis().':'.Guid_Random::nextLong();
		$this->valueAfterMD5 = md5($this->valueBeforeMD5);
	}

	function toString()
	{
		$raw = strtolower($this->valueAfterMD5);
		return substr($raw,0,8).'-'.substr($raw,8,4).'-'.substr($raw,12,4).'-'.substr($raw,16,4).'-'.substr($raw,20);
	}

}

