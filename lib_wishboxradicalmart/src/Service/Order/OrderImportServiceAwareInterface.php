<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines injection of the RadicalMart order import service.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
interface OrderImportServiceAwareInterface
{
	/**
	 * Set the RadicalMart order import service.
	 *
	 * @param   OrderImportService  $orderImportService  RadicalMart order import service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setOrderImportService(OrderImportService $orderImportService): void;
}
