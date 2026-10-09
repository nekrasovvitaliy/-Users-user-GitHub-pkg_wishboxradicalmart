<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Product;

use UnexpectedValueException;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides Wishbox RadicalMart service injection for service-aware classes.
 *
 * @since 1.0.0
 */
trait ProductServiceAwareTrait
{
	/**
	 * WishBox RadicalMart product service.
	 *
	 * @var ProductService|null
	 *
	 * @since 1.0.0
	 */
	private ?ProductService $productService = null;

	/**
	 * Get the configured WishBox RadicalMart product service.
	 *
	 * @return ProductService
	 *
	 * @throws UnexpectedValueException When the service has not been injected.
	 *
	 * @since 1.0.0
	 */
	protected function getProductService(): ProductService
	{
		if ($this->productService instanceof ProductService)
		{
			return $this->productService;
		}

		throw new UnexpectedValueException('ProductService not set in ' . static::class . '.');
	}

	/**
	 * Set the WishBox RadicalMart product service.
	 *
	 * @param   ProductService  $productService  RadicalMart product service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setProductService(ProductService $productService): void
	{
		$this->productService = $productService;
	}
}
