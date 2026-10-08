<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the contract for classes that depend on the Wishbox RadicalMart order service.
 *
 * @since 1.0.0
 */
interface WishboxRadicalMartOrderServiceAwareInterface
{
	/**
	 * Set the Wishbox RadicalMart Order service.
	 *
	 * @param   WishboxRadicalMartOrderService  $wishboxRadicalMartOrderService  RadicalMart order service
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setWishboxRadicalMartOrderService(WishboxRadicalMartOrderService $wishboxRadicalMartOrderService): void;
}
