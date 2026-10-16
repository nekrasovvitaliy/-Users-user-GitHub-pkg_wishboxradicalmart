<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the contract for classes that depend on the Wishbox RadicalMart order service.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
interface OrderServiceAwareInterface
{
	/**
	 * Get the WishBox RadicalMart order service.
	 *
	 * @return OrderService
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getOrderService(): OrderService;

	/**
	 * Set the Wishbox RadicalMart Order service.
	 *
	 * @param   OrderService  $orderService  RadicalMart order service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setOrderService(OrderService $orderService): void;
}
