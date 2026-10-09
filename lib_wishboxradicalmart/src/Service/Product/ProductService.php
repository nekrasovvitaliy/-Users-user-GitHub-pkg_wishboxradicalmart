<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Product;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Component\RadicalMart\Administrator\Extension\RadicalMartComponent;
use Joomla\Component\RadicalMart\Administrator\Model\ProductModel;
use Joomla\Component\RadicalMart\Administrator\Table\ProductTable;
use Joomla\Registry\Registry;
use RuntimeException;
use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides access to RadicalMart product stock data.
 *
 * @since 1.0.0
 */
final class ProductService
{
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
		$app = Factory::getApplication();

		/** @var RadicalMartComponent $radicalMartComponent */
		$radicalMartComponent = $app->bootComponent('com_radicalmart');

		$mvcFactory = $radicalMartComponent->getMVCFactory();

		/** @var ProductTable $productTable */
		$productTable = $mvcFactory->createTable('Product', 'Administrator');

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
}
