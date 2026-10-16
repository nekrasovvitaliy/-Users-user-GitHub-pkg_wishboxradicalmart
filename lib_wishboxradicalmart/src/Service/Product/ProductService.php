<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Product;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Helper\PriceHelper;
use Joomla\Component\RadicalMart\Administrator\Model\ProductModel;
use Joomla\Component\RadicalMart\Administrator\Model\ProductsModel;
use Joomla\Component\RadicalMart\Administrator\Table\ProductTable;
use Joomla\Registry\Registry;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides access to RadicalMart product data.
 *
 * @since 1.0.0
 */
final class ProductService
{
	/**
	 * Get a product id by its alias.
	 *
	 * @param   string  $alias  Product alias
	 *
	 * @return int
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getIdByAlias(string $alias): int
	{
		$alias = trim($alias);

		if ($alias === '')
		{
			throw new InvalidArgumentException('RadicalMart product alias must not be empty.');
		}

		$productTable = $this->createProductTable();

		if (!$productTable->load(['alias' => $alias]))
		{
			throw new UnexpectedValueException('RadicalMart product not found by alias: ' . $alias, 404);
		}

		return (int) $productTable->id;
	}

	/**
	 * Get a product id by its code.
	 *
	 * @param   string  $code  Product code
	 *
	 * @return int
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getIdByCode(string $code): int
	{
		$code = trim($code);

		if ($code === '')
		{
			throw new InvalidArgumentException('RadicalMart product code must not be empty.');
		}

		$productTable = $this->createProductTable();

		if (!$productTable->load(['code' => $code]))
		{
			throw new UnexpectedValueException('RadicalMart product not found by code: ' . $code, 404);
		}

		return (int) $productTable->id;
	}

	/**
	 * Get the product stock quantity.
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return float
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getStockQuantity(int $productId): float
	{
		$productTable = $this->loadProductTable($productId);

		$registry = new Registry($productTable->stock);

		return (float) $registry->get('all', 0);
	}

	/**
	 * Get an additional product field value by its alias.
	 *
	 * @param   int     $productId   Product id
	 * @param   string  $fieldAlias  Additional field alias
	 * @param   mixed   $default     Default value
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getProductFieldValue(int $productId, string $fieldAlias, mixed $default = null): mixed
	{
		$fieldAlias = trim($fieldAlias);

		if ($fieldAlias === '')
		{
			throw new InvalidArgumentException('RadicalMart product field alias must not be empty.');
		}

		$productTable  = $this->loadProductTable($productId);
		$productFields = new Registry($productTable->fields);

		return $productFields->get($fieldAlias, $default);
	}

	/**
	 * Check whether a product is published and available in stock.
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function isAvailable(int $productId): bool
	{
		$productTable = $this->loadProductTable($productId);

		return (int) $productTable->state === 1 && (int) $productTable->in_stock === 1;
	}

	/**
	 * Get product ids that have a non-empty additional field value.
	 *
	 * @param   string  $fieldAlias  Additional field alias
	 *
	 * @return list<int>
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getIdsWithProductField(string $fieldAlias): array
	{
		$fieldAlias = trim($fieldAlias);

		if ($fieldAlias === '')
		{
			throw new InvalidArgumentException('RadicalMart product field alias must not be empty.');
		}

		$productsModel = $this->createProductsModel();
		$productsModel->setState('list.start', 0);
		$productsModel->setState('list.limit', 0);

		$productIds = [];

		foreach ($productsModel->getItems() as $product)
		{
			$productFields = new Registry($product->fields ?? []);

			if (!empty($productFields->get($fieldAlias)))
			{
				$productIds[] = (int) $product->id;
			}
		}

		return $productIds;
	}

	/**
	 * Return normalized RadicalMart products for an external marketplace.
	 *
	 * @param   string     $externalProductIdField  External product ID field alias.
	 * @param   string     $currencyCode            Product price currency code.
	 * @param   float      $priceRatio              Product price multiplier.
	 * @param   list<int>  $productIds              Product IDs, or an empty list for all linked products.
	 *
	 * @return list<array{offer_id: string, product_id: int, price: float|null, stock: int}>
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function getProducts(
		string $externalProductIdField,
		string $currencyCode = 'RUB',
		float  $priceRatio = 1.0,
		array  $productIds = []
	): array
	{
		$externalProductIdField = trim($externalProductIdField);
		$currencyCode           = strtoupper(trim($currencyCode));

		if ($externalProductIdField === '')
		{
			throw new InvalidArgumentException('External product ID field alias must not be empty.');
		}

		if ($currencyCode === '')
		{
			throw new InvalidArgumentException('Product price currency code must not be empty.');
		}

		if ($priceRatio < 0)
		{
			throw new InvalidArgumentException('Product price ratio must not be negative.');
		}

		$currency             = PriceHelper::getCurrency($currencyCode);
		$resolvedCurrencyCode = strtoupper(trim((string) ($currency['code'] ?? '')));
		$currencyGroup        = trim((string) ($currency['group'] ?? ''));

		if ($resolvedCurrencyCode !== $currencyCode || $currencyGroup === '')
		{
			throw new UnexpectedValueException(
				'The requested currency is not available in RadicalMart: ' . $currencyCode
			);
		}

		if ($productIds === [])
		{
			$productIds = $this->getIdsWithProductField($externalProductIdField);
		}
		else
		{
			$productIds = array_filter($productIds, static fn(int $productId): bool => $productId > 0)
					|> array_unique(...)
					|> array_values(...);
		}

		$productModel = $this->createProductModel();
		$products     = [];

		foreach ($productIds as $productId)
		{
			$product = $productModel->getItem($productId);

			if ($product === false)
			{
				$products[] = [
					'offer_id'   => '',
					'product_id' => 0,
					'price'      => null,
					'stock'      => 0,
				];

				continue;
			}

			$externalProductId = $this->getProductFieldValue(
				$productId,
				$externalProductIdField,
				0
			);
			$productAvailable  = (int) ($product->state ?? 0) === 1
				&& (int) ($product->in_stock ?? 0) === 1;
			$stock             = $productAvailable
				? max(0, (int) floor($this->getStockQuantity($productId)))
				: 0;

			$products[] = [
				'offer_id'   => trim((string) ($product->alias ?? '')),
				'product_id' => max(0, (int) $externalProductId),
				'price'      => $this->getProductPrice($product, $currencyGroup, $priceRatio),
				'stock'      => $stock,
			];
		}

		return $products;
	}

	/**
	 * Set the product stock quantity.
	 *
	 * @param   int    $productId  Product id
	 * @param   float  $quantity   Stock quantity
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setStockQuantity(int $productId, float $quantity): void
	{
		$productModel = $this->createProductModel();

		if ($productModel->updateStock(
				$productId,
				['all' => $quantity],
				'merge'
			) === false)
		{
			$error = $productModel->getError();

			throw new RuntimeException(
				$error ?: 'Unable to update RadicalMart product stock: ' . $productId,
				500
			);
		}
	}

	/**
	 * Set an additional product field value by its alias.
	 *
	 * @param   int     $productId   Product id
	 * @param   string  $fieldAlias  Additional field alias
	 * @param   mixed   $value       Additional field value
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setProductFieldValue(int $productId, string $fieldAlias, mixed $value): void
	{
		if ($productId <= 0)
		{
			throw new InvalidArgumentException('RadicalMart product id must be greater than zero.');
		}

		$fieldAlias = trim($fieldAlias);

		if ($fieldAlias === '')
		{
			throw new InvalidArgumentException('RadicalMart product field alias must not be empty.');
		}

		$productModel = $this->createProductModel();
		$product      = $productModel->getItem($productId);

		if ($product === false)
		{
			throw new UnexpectedValueException('RadicalMart product not found: ' . $productId, 404);
		}

		$productFields = new Registry($product->fields ?? []);
		$productFields->set($fieldAlias, $value);

		if ($productModel->save(
				[
					'id'     => $productId,
					'fields' => $productFields->toArray()
				]
			) === false)
		{
			$error = $productModel->getError();

			if ($error instanceof Throwable)
			{
				throw new RuntimeException($error->getMessage(), 500, $error);
			}

			throw new RuntimeException(
				$error ?: 'Unable to update RadicalMart product fields: ' . $productId,
				500
			);
		}
	}

	/**
	 * Set external product identifiers for products matched by code.
	 *
	 * @param   string                                              $fieldAlias  External identifier field alias.
	 * @param   list<array{code: string, external_id: int}>         $productIds  Product codes and external identifiers.
	 *
	 * @return array{updated: int, skipped: int, failed: int, errors: list<string>} Update result.
	 *
	 * @throws InvalidArgumentException When the field alias is empty.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setExternalProductIdsByCode(string $fieldAlias, array $productIds): array
	{
		return $this->setExternalProductIds(
			$fieldAlias,
			$productIds,
			'code',
			$this->getIdByCode(...)
		);
	}

	/**
	 * Set external product identifiers for products matched by alias.
	 *
	 * @param   string                                        $fieldAlias  External identifier field alias.
	 * @param   list<array{alias: string, external_id: int}>  $productIds  Product aliases and external identifiers.
	 *
	 * @return array{updated: int, skipped: int, failed: int, errors: list<string>} Update result.
	 *
	 * @throws InvalidArgumentException When the field alias is empty.
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function setExternalProductIdsByAlias(string $fieldAlias, array $productIds): array
	{
		return $this->setExternalProductIds(
			$fieldAlias,
			$productIds,
			'alias',
			$this->getIdByAlias(...)
		);
	}

	/**
	 * Set external identifiers using the supplied product identifier resolver.
	 *
	 * @param   string                            $fieldAlias        External identifier field alias.
	 * @param   list<array<string, int|string>>  $productIds        Product identifiers and external identifiers.
	 * @param   string                            $identifierKey     Product identifier array key.
	 * @param   callable(string): int             $productIdResolver Product identifier resolver.
	 *
	 * @return array{updated: int, skipped: int, failed: int, errors: list<string>} Update result.
	 *
	 * @throws InvalidArgumentException When the field alias is empty.
	 *
	 * @since 1.0.0
	 */
	private function setExternalProductIds(
		string   $fieldAlias,
		array    $productIds,
		string   $identifierKey,
		callable $productIdResolver
	): array
	{
		$fieldAlias = trim($fieldAlias);

		if ($fieldAlias === '')
		{
			throw new InvalidArgumentException('RadicalMart product field alias must not be empty.');
		}

		$result = [
			'updated' => 0,
			'skipped' => 0,
			'failed'  => 0,
			'errors'  => [],
		];

		foreach ($productIds as $productIdData)
		{
			$productIdentifier = trim((string) ($productIdData[$identifierKey] ?? ''));
			$externalProductId = (int) ($productIdData['external_id'] ?? 0);

			if ($productIdentifier === '' || $externalProductId < 1)
			{
				$result['skipped']++;

				continue;
			}

			try
			{
				$productId = $productIdResolver($productIdentifier);

				if ($productId < 1)
				{
					$result['skipped']++;

					continue;
				}

				$this->setProductFieldValue($productId, $fieldAlias, $externalProductId);
				$result['updated']++;
			}
			catch (UnexpectedValueException)
			{
				$result['skipped']++;
			}
			catch (Throwable $throwable)
			{
				$result['failed']++;
				$result['errors'][] = 'RadicalMart product '
					. $productIdentifier . ': ' . $throwable->getMessage();
			}
		}

		return $result;
	}

	/**
	 * Read the final product price in the requested currency group.
	 *
	 * @param   object  $product        RadicalMart product data.
	 * @param   string  $currencyGroup  RadicalMart currency group.
	 * @param   float   $priceRatio     Product price multiplier.
	 *
	 * @return float|null Product price or null when unavailable.
	 *
	 * @since 1.0.0
	 */
	private function getProductPrice(object $product, string $currencyGroup, float $priceRatio): ?float
	{
		$prices = is_array($product->prices ?? null) ? $product->prices : [];
		$price  = $prices[$currencyGroup]['final'] ?? null;

		if (!is_numeric($price))
		{
			return null;
		}

		return max(0.0, (float) $price * $priceRatio);
	}

	/**
	 * Create the RadicalMart administrator product table.
	 *
	 * @return ProductTable
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function createProductTable(): ProductTable
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		$mvcFactory = $radicalMartComponent->getMVCFactory();

		/** @var ProductTable|null $productTable */
		$productTable = $mvcFactory->createTable('Product', 'Administrator');

		if (!$productTable instanceof ProductTable)
		{
			throw new RuntimeException('Unable to create RadicalMart product table.', 500);
		}

		return $productTable;
	}

	/**
	 * Load a RadicalMart product table by id.
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return ProductTable
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function loadProductTable(int $productId): ProductTable
	{
		if ($productId <= 0)
		{
			throw new InvalidArgumentException('RadicalMart product id must be greater than zero.');
		}

		$productTable = $this->createProductTable();

		if (!$productTable->load($productId))
		{
			throw new UnexpectedValueException('RadicalMart product not found: ' . $productId, 404);
		}

		return $productTable;
	}

	/**
	 * Create the RadicalMart administrator product model.
	 *
	 * @return ProductModel
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function createProductModel(): ProductModel
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		$mvcFactory = $radicalMartComponent->getMVCFactory();

		/** @var ProductModel|null $productModel */
		$productModel = $mvcFactory->createModel(
			'Product',
			'Administrator',
			[
				'ignore_request' => true
			]
		);

		if (!$productModel instanceof ProductModel)
		{
			throw new RuntimeException('Unable to create RadicalMart product model.', 500);
		}

		return $productModel;
	}

	/**
	 * Create the RadicalMart administrator products model.
	 *
	 * @return ProductsModel
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
	 */
	private function createProductsModel(): ProductsModel
	{
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		$mvcFactory = $radicalMartComponent->getMVCFactory();

		/** @var ProductsModel|null $productsModel */
		$productsModel = $mvcFactory->createModel(
			'Products',
			'Administrator',
			[
				'ignore_request' => true
			]
		);

		if (!$productsModel instanceof ProductsModel)
		{
			throw new RuntimeException('Unable to create RadicalMart products model.', 500);
		}

		return $productsModel;
	}
}
