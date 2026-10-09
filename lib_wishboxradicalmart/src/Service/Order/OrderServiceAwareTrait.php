<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides Wishbox RadicalMart service injection for service-aware classes.
 *
 * @since 1.0.0
 */
trait OrderServiceAwareTrait
{
	/**
	 * Wishbox RadicalMart order service.
	 *
	 * @var ProductService|null
	 *
	 * @since 1.0.0
	 */
	private ?ProductService $orderService = null;

	/**
	 * Get the configured Wishbox RadicalMart order service.
	 *
	 * @return ProductService
	 *
	 * @throws UnexpectedValueException When the service has not been injected.
	 *
	 * @since 1.0.0
	 */
	protected function getOrderService(): ProductService
	{
		if ($this->orderService instanceof ProductService)
		{
			return $this->orderService;
		}

		throw new UnexpectedValueException('OrderService not set in ' . static::class . '.');
	}

	/**
	 * Set the Wishbox RadicalMart order service.
	 *
	 * @param   ProductService  $orderService  RadicalMart order service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setOrderService(ProductService $orderService): void
	{
		$this->orderService = $orderService;
	}
}
