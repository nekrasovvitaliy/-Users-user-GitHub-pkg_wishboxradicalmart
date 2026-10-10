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
use Joomla\Component\RadicalMart\Administrator\Model\ProductModel;
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
	 * @param   string  $alias Product alias
	 *
	 * @return int
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
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
		$productTable = $this->createProductTable();

		if (!$productTable->load($productId))
		{
			throw new UnexpectedValueException('RadicalMart product not found: ' . $productId, 404);
		}

		$registry = new Registry($productTable->stock);

		return (float) $registry->get('all', 0);
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
	 * @param   int     $productId  Product id
	 * @param   string  $fieldAlias Additional field alias
	 * @param   mixed   $value      Additional field value
	 *
	 * @return void
	 *
	 * @throws Exception
	 *
	 * @since 1.0.0
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
	 * Create the RadicalMart administrator product table.
	 *
	 * @return ProductTable
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
	 * Create the RadicalMart administrator product model.
	 *
	 * @return ProductModel
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
}
