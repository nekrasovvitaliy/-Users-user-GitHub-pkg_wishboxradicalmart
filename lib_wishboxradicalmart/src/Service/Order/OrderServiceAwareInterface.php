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
 */
interface OrderServiceAwareInterface
{
	/**
	 * Set the Wishbox RadicalMart Order service.
	 *
	 * @param   ProductService  $orderService  RadicalMart order service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setOrderService(ProductService $orderService): void;
}
