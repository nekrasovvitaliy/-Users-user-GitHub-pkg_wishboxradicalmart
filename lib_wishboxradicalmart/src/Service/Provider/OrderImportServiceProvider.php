<?php
/**
 * @copyright   (c) 2013-2026 Nekrasov Vitaliy <nekrasov_vitaliy@list.ru>
 * @license     GNU General Public License version 2 or later;
 */

namespace WishboxRadicalMartLibrary\Service\Provider;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use RuntimeException;
use WishboxRadicalMartLibrary\Service\Order\OrderImportService;
use WishboxRadicalMartLibrary\Service\Product\ProductService;

// phpcs:disable PSR1.Files.SideEffects
defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers the RadicalMart order import service.
 *
 * @since 1.0.0
 *
 * @noinspection PhpUnused
 */
final class OrderImportServiceProvider implements ServiceProviderInterface
{
	/**
	 * Register the RadicalMart order import service.
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
		if (!$container->has(ProductService::class))
		{
			$container->registerServiceProvider(new ProductServiceProvider());
		}

		$application = Factory::getApplication();

		if (!$application instanceof CMSApplication)
		{
			throw new RuntimeException('The current Joomla application cannot import RadicalMart orders.');
		}

		$container->share(
			OrderImportService::class,
			static fn (Container $container): OrderImportService => new OrderImportService(
				$application,
				$container->get(DatabaseInterface::class),
				$container->get(ProductService::class)
			)
		);
	}
}
