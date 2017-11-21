<?php

namespace App\Realisation;

use Memcached;
use App\Interfaces\CacheInterface;
use App\Exceptions\InvalidArgumentException;

class CacheMemcached implements CacheInterface {

	/**
	 * @var Memcached
	 */
	private $memcached;
	const MEMCACHE_PORT = 11211;

	/**
	 * CacheMemcached constructor.
	 * @param string $host
	 */
	public function __construct(string $host = '127.0.0.1') {
		$this->memcached = new Memcached();
		$this->memcached->addServer($host, self::MEMCACHE_PORT);
	}

	/**
	 * @param string $key
	 * @param null $default
	 * @return InvalidArgumentException|mixed|null
	 */
	public function get($key, $default = null) {
		if (is_string($key)) {
			return new InvalidArgumentException('Key must be string.');
		}

		$value = $this->memcached->get($key);

		if ($value === false) {
			return $default;
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param null $ttl
	 * @return InvalidArgumentException|bool
	 */
	public function set($key, $value, $ttl = null) {
		if (!is_string($key) || !is_string($value)) {
			return new InvalidArgumentException('Key and value must be string');
		}

		return $this->memcached->set($key, $value, $ttl);
	}

	/**
	 * @param string $key
	 * @return InvalidArgumentException|bool
	 */
	public function delete($key) {
		if (!is_string($key)) {
			return new InvalidArgumentException('Key must be string');
		}

		return $this->memcached->delete($key);
	}

	/**
	 * @return bool
	 */
	public function clear() {
		return $this->memcached->flush();
	}

	/**
	 * @param iterable $keys
	 * @param null $default
	 * @return InvalidArgumentException|array
	 */
	public function getMultiple($keys, $default = null) {
		foreach ($keys as $key) {
			if (!is_string($key)) {
				return new InvalidArgumentException('Each key in keys must be string');
			}
		}

		$result = $this->memcached->getMulti($keys);

		foreach ($keys as $key) {
			if (!array_key_exists($key, $result)) {
				$result[$key] = $default;
			}
		}

		return $result;
	}

	/**
	 * @param iterable $values
	 * @param null $ttl
	 * @return InvalidArgumentException|bool
	 */
	public function setMultiple($values, $ttl = null) {
		foreach ($values as $key => $value) {
			if (!is_string($key) || !is_string($value)) {
				return new InvalidArgumentException('Keys and values in array must be string');
			}
		}

		return $this->memcached->setMulti($values, $ttl);
	}

	/**
	 * @param iterable $keys
	 * @return InvalidArgumentException|bool
	 */
	public function deleteMultiple($keys) {
		foreach ($keys as $key) {
			if (!is_string($key)) {
				return new InvalidArgumentException('Each key in keys must be string');
			}
		}

		return $this->memcached->deleteMulti($keys);
	}

	/**
	 * @param string $key
	 * @return InvalidArgumentException|bool
	 */
	public function has($key) {
		if (!is_string($key)) {
			return new InvalidArgumentException('Key must be string');
		}

		if ($this->memcached->get($key) === false) {
			return false;
		}

		return true;
	}
}