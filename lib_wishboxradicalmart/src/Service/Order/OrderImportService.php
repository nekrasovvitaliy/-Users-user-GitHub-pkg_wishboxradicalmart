<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Order;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Model\OrderModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use RuntimeException;
use Throwable;
use Wishbox\Dto\OrderImport\MarketplaceOrder;
use Wishbox\Dto\OrderImport\MarketplaceOrderDelivery;
use Wishbox\Dto\OrderImport\MarketplaceOrderItem;
use Wishbox\Dto\OrderImport\OrderImportResult;
use WishboxRadicalMartLibrary\Service\Product\ProductService;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Imports normalized marketplace orders into RadicalMart.
 *
 * @since        1.0.0
 *
 * @noinspection PhpUnused
 */
final readonly class OrderImportService
{
	/**
	 * Initialize the RadicalMart order import service.
	 *
	 * @param   CMSApplication     $application     Joomla application.
	 * @param   DatabaseInterface  $database        Joomla database connection.
	 * @param   ProductService     $productService  RadicalMart product service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function __construct(
		private CMSApplication    $application,
		private DatabaseInterface $database,
		private ProductService    $productService
	)
	{
	}

	/**
	 * Import normalized marketplace orders.
	 *
	 * @param   list<MarketplaceOrder>  $orders   Normalized marketplace orders.
	 * @param   array<string, mixed>    $options  RadicalMart order mapping options.
	 *
	 * @return OrderImportResult Import result.
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function importOrders(array $orders, array $options = []): OrderImportResult
	{
		$created  = 0;
		$skipped  = 0;
		$failed   = 0;
		$orderIds = [];
		$errors   = [];

		foreach ($orders as $marketplaceOrder)
		{
			if (!$marketplaceOrder instanceof MarketplaceOrder)
			{
				$failed++;
				$errors['unknown'] = 'The order must be an instance of MarketplaceOrder.';

				continue;
			}

			$externalId = $marketplaceOrder->externalId !== '' ? $marketplaceOrder->externalId : 'unknown';

			try
			{
				$this->validateOrder($marketplaceOrder, $options);
				$source = $this->getSource($options);

				if ($this->orderExists($source, $externalId))
				{
					$skipped++;

					continue;
				}

				$orderId = $this->createOrder($marketplaceOrder, $options, $source);
				$created++;
				$orderIds[$externalId] = $orderId;
			}
			catch (Throwable $throwable)
			{
				$failed++;
				$errors[$externalId] = $throwable->getMessage();
			}
		}

		return new OrderImportResult($created, $skipped, $failed, $orderIds, $errors);
	}

	/**
	 * Validate normalized order data and import options.
	 *
	 * @param   MarketplaceOrder      $marketplaceOrder  Normalized marketplace order.
	 * @param   array<string, mixed>  $options           RadicalMart order mapping options.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	private function validateOrder(MarketplaceOrder $marketplaceOrder, array $options): void
	{
		$error = trim($marketplaceOrder->error);

		if ($error !== '')
		{
			throw new RuntimeException($error);
		}

		if (trim($marketplaceOrder->externalId) === '')
		{
			throw new RuntimeException('Marketplace order external ID must be configured.');
		}

		if ((int) ($options['user_id'] ?? 0) < 1)
		{
			throw new RuntimeException('A Joomla user must be configured for imported marketplace orders.');
		}
	}

	/**
	 * Determine whether a marketplace order was already imported.
	 *
	 * @param   string  $source      Marketplace source code.
	 * @param   string  $externalId  Marketplace order identifier.
	 *
	 * @return bool True when the marketplace order already exists.
	 *
	 * @since 1.0.0
	 */
	private function orderExists(string $source, string $externalId): bool
	{
		$externalIdPattern = '%' . $externalId . '%';
		$query             = $this->database->createQuery()
			->select($this->database->quoteName('plugins'))
			->from($this->database->quoteName('#__radicalmart_orders'))
			->where($this->database->quoteName('plugins') . ' LIKE :externalId')
			->bind(':externalId', $externalIdPattern);

		$orderPlugins = $this->database->setQuery($query)
			->loadColumn();

		foreach ($orderPlugins as $pluginsData)
		{
			$plugins = new Registry($pluginsData);

			if ((string) $plugins->get($source . '.external_id') === $externalId)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Create one RadicalMart order.
	 *
	 * @param   MarketplaceOrder      $marketplaceOrder  Normalized marketplace order.
	 * @param   array<string, mixed>  $options           RadicalMart order mapping options.
	 * @param   string                $source            Marketplace source code.
	 *
	 * @return int Created RadicalMart order identifier.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function createOrder(MarketplaceOrder $marketplaceOrder, array $options, string $source): int
	{
		$radicalMartOrderModel = $this->getOrderModel();
		$saveData              = $this->buildOrderData($marketplaceOrder, $options, $source);
		$radicalMartOrderModel->setFormData(0, $saveData);
		$order = $radicalMartOrderModel->save($saveData);

		if (!is_object($order) || (int) ($order->id ?? 0) < 1)
		{
			$message = trim((string) $radicalMartOrderModel->getError());

			throw new RuntimeException($message !== '' ? $message : 'RadicalMart did not create the order.');
		}

		return (int) $order->id;
	}

	/**
	 * Build RadicalMart order data.
	 *
	 * @param   MarketplaceOrder      $marketplaceOrder  Normalized marketplace order.
	 * @param   array<string, mixed>  $options           RadicalMart order mapping options.
	 * @param   string                $source            Marketplace source code.
	 *
	 * @return array<string, mixed> RadicalMart order data.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function buildOrderData(MarketplaceOrder $marketplaceOrder, array $options, string $source): array
	{
		$externalId      = trim($marketplaceOrder->externalId);
		$managerComment  = trim($marketplaceOrder->managerComment);
		$customerComment = trim($marketplaceOrder->customerComment);
		$note            = implode("\n", array_filter([$managerComment, $customerComment]));

		return [
			'id'         => 0,
			'state'      => 1,
			'created_by' => (int) $options['user_id'],
			'currency'   => strtoupper(trim((string) ($options['currency'] ?? 'RUB'))),
			'products'   => $this->buildProducts($marketplaceOrder->items),
			'contacts'   => [
				'first_name'  => $marketplaceOrder->firstName,
				'second_name' => $marketplaceOrder->patronymic,
				'last_name'   => $marketplaceOrder->lastName,
				'email'       => $marketplaceOrder->email,
				'phone'       => $marketplaceOrder->phone,
			],
			'note'       => $note,
			'plugins'    => [
				$source => [
					'external_id' => $externalId,
					'source_id'   => (string) $marketplaceOrder->sourceId,
					'delivery'    => $this->getDeliveryData($marketplaceOrder->delivery),
				],
			],
		];
	}

	/**
	 * Build RadicalMart product rows from normalized items.
	 *
	 * @param   list<MarketplaceOrderItem>  $items  Normalized marketplace items.
	 *
	 * @return list<array{id: int, quantity: float, base: float}> RadicalMart order products.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function buildProducts(array $items): array
	{
		$products = [];

		foreach ($items as $itemData)
		{
			if (!$itemData instanceof MarketplaceOrderItem)
			{
				continue;
			}

			$productCode = trim($itemData->productCode);

			if ($productCode === '')
			{
				throw new RuntimeException('Marketplace order item does not contain a product code.');
			}

			$productId = $this->productService->getIdByAlias($productCode);
			$quantity  = max(1.0, $itemData->quantity);
			$price     = $itemData->price ?? 0.0;

			if (isset($products[$productId]))
			{
				$products[$productId]['quantity'] += $quantity;

				continue;
			}

			$products[$productId] = [
				'id'       => $productId,
				'quantity' => $quantity,
				'base'     => $price,
			];
		}

		if ($products === [])
		{
			throw new RuntimeException('Marketplace order does not contain any mapped products.');
		}

		return array_values($products);
	}

	/**
	 * Convert delivery DTO to RadicalMart plugin metadata.
	 *
	 * @param   MarketplaceOrderDelivery|null  $delivery  Marketplace delivery data.
	 *
	 * @return array<string, float|string> Delivery metadata.
	 *
	 * @since 1.0.0
	 */
	private function getDeliveryData(?MarketplaceOrderDelivery $delivery): array
	{
		if ($delivery === null)
		{
			return [];
		}

		return [
			'cost'     => $delivery->cost,
			'country'  => $delivery->country,
			'region'   => $delivery->region,
			'city'     => $delivery->city,
			'zip'      => $delivery->zip,
			'street'   => $delivery->street,
			'building' => $delivery->building,
			'flat'     => $delivery->flat,
			'text'     => $delivery->text,
			'notes'    => $delivery->notes,
		];
	}

	/**
	 * Normalize a marketplace source code for Registry paths.
	 *
	 * @param   array<string, mixed>  $options  RadicalMart order mapping options.
	 *
	 * @return string Marketplace source code.
	 *
	 * @since 1.0.0
	 */
	private function getSource(array $options): string
	{
		$source = strtolower(trim((string) ($options['source'] ?? 'marketplace')));
		$source = preg_replace('/[^a-z0-9_]+/', '_', $source) ?? '';

		return trim($source, '_') ?: 'marketplace';
	}

	/**
	 * Create a RadicalMart administrator order model.
	 *
	 * @return OrderModel RadicalMart order model.
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function getOrderModel(): OrderModel
	{
		$radicalMartComponent = $this->application->bootComponent('com_radicalmart');

		if (!$radicalMartComponent instanceof RadicalMartComponent)
		{
			throw new RuntimeException('The RadicalMart component could not be booted.');
		}

		$mvcFactory = $radicalMartComponent->getMVCFactory();

		/** @var OrderModel $radicalMartOrderModel */
		$radicalMartOrderModel = $mvcFactory->createModel(
			'Order',
			'Administrator',
			['ignore_request' => true]
		);

		if (!$radicalMartOrderModel instanceof OrderModel)
		{
			throw new RuntimeException('The RadicalMart order model could not be created.');
		}

		return $radicalMartOrderModel;
	}
}
