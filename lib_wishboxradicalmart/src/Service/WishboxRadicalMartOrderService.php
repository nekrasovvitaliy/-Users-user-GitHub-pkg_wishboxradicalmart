<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Registry\Registry;
use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Updates shipping data stored with RadicalMart orders.
 *
 * @since 1.0.0
 */
final class WishboxRadicalMartOrderService
{
	/**
	 * Set a value in an order shipping data registry.
	 *
	 * @param   int     $orderId  Order id
	 * @param   string  $name     Data name
	 * @param   string  $value    Data value
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setShippingDataValue(int $orderId, string $name, string $value): void
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		/** @var OrderTable $orderTable */
		$orderTable = $radicalMartComponent->getMVCFactory()
			->createTable('Order', 'Administrator');

		if (!$orderTable->load($orderId))
		{
			throw new UnexpectedValueException('RadicalMart order not found: ' . $orderId, 404);
		}

		$registry = new Registry($orderTable->shipping);
		$path     = 'data.' . $name;

		if ($registry->get($path, '') === $value)
		{
			return;
		}

		$registry->set($path, $value);
		$orderTable->shipping = $registry->toString();

		if (!$orderTable->store())
		{
			throw new Exception('$orderTable->store() return false', 500);
		}
	}
}
