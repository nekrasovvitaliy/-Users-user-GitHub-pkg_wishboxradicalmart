<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license         GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Product;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the contract for classes that depend on the Wishbox RadicalMart order service.
 *
 * @since 1.0.0
 */
interface ProductServiceAwareInterface
{
	/**
	 * Set the WishBox RadicalMart product service.
	 *
	 * @param   ProductService  $productService  RadicalMart product service.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function setProductService(ProductService $productService): void;
}
