<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Provider;

use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use WishboxRadicalMartLibrary\Service\Product\ProductService;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers the RadicalMart product service.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class ProductServiceProvider implements ServiceProviderInterface
{
	/**
	 * Register the RadicalMart product service.
	 *
	 * @param   Container  $container  Dependency injection container.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function register(Container $container): void
	{
		$container->share(
			ProductService::class,
			static fn (): ProductService => new ProductService()
		);
	}
}
