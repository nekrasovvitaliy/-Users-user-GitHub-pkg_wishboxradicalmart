<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides access to the RadicalMart order import service.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
trait OrderImportServiceAwareTrait
{
	private ?OrderImportService $orderImportService = null;

	/**
	 * Get the RadicalMart order import service.
	 *
	 * @return OrderImportService RadicalMart order import service.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	protected function getOrderImportService(): OrderImportService
	{
		if (!$this->orderImportService instanceof OrderImportService)
		{
			throw new UnexpectedValueException('OrderImportService has not been set.');
		}

		return $this->orderImportService;
	}

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
	public function setOrderImportService(OrderImportService $orderImportService): void
	{
		$this->orderImportService = $orderImportService;
	}
}
