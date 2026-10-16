<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Component\RadicalMart\Administrator\Table\OrderTable;
use Joomla\Registry\Registry;
use RuntimeException;
use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides RadicalMart order operations.
 *
 * @since        1.0.0
 *
 * @noinspection PhpUnused
 */
final class OrderService
{
	/**
	 * Change the RadicalMart order status.
	 *
	 * @param   int  $orderId   RadicalMart order identifier.
	 * @param   int  $statusId  RadicalMart status identifier.
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	public function updateStatus(int $orderId, int $statusId): void
	{
		if ($orderId < 1)
		{
			throw new InvalidArgumentException('RadicalMart order ID must be greater than zero.');
		}

		if ($statusId < 1)
		{
			throw new InvalidArgumentException('RadicalMart status ID must be greater than zero.');
		}

		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		/** @var OrderModel $radicalMartOrderModel */
		$radicalMartOrderModel = $radicalMartComponent->getMVCFactory()
			->createModel('Order', 'Administrator', ['ignore_request' => true]);

		if (!$radicalMartOrderModel->updateStatus($orderId, $statusId))
		{
			$message = trim((string) $radicalMartOrderModel->getError());

			throw new RuntimeException(
				$message !== '' ? $message : 'RadicalMart order status could not be updated.'
			);
		}
	}

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
