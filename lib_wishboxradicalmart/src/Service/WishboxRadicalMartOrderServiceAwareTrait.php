<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service;

use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides Wishbox RadicalMart service injection for service-aware classes.
 *
 * @since 1.0.0
 */
trait WishboxRadicalMartOrderServiceAwareTrait
{
	/**
	 * Wishbox RadicalMart order service.
	 *
	 * @var WishboxRadicalMartOrderService|null
	 *
	 * @since 1.0.0
	 */
	private ?WishboxRadicalMartOrderService $wishboxRadicalMartOrderService = null;

	/**
	 * Get the configured Wishbox RadicalMart order service.
	 *
	 * @return WishboxRadicalMartOrderService
	 *
	 * @throws UnexpectedValueException When the service has not been injected.
	 *
	 * @since 1.0.0
	 */
	protected function getWishboxRadicalMartOrderService(): WishboxRadicalMartOrderService
	{
		if ($this->wishboxRadicalMartOrderService instanceof WishboxRadicalMartOrderService)
		{
			return $this->wishboxRadicalMartOrderService;
		}

		throw new UnexpectedValueException('WishboxRadicalMartOrderService not set in ' . static::class . '.');
	}

	/**
	 * Set the Wishbox RadicalMart order service.
	 *
	 * @param   WishboxRadicalMartOrderService  $wishboxRadicalMartOrderService  RadicalMart order service
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setWishboxRadicalMartOrderService(WishboxRadicalMartOrderService $wishboxRadicalMartOrderService): void
	{
		$this->wishboxRadicalMartOrderService = $wishboxRadicalMartOrderService;
	}
}
