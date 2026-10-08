# WishBox RadicalMart

Joomla-пакет устанавливает библиотеку `lib_wishboxradicalmart` с сервисом для изменения данных доставки в заказах RadicalMart.

## Требования

- установленный и включённый компонент `com_radicalmart`;
- версия PHP, поддерживаемая установленными версиями Joomla и RadicalMart.

## Сервис заказов

Класс `WishboxRadicalMartLibrary\Service\WishboxRadicalMartOrderService` записывает значение в секцию `data` реестра `shipping` заказа:

```php
use WishboxRadicalMartLibrary\Service\WishboxRadicalMartOrderService;

$orderService = new WishboxRadicalMartOrderService();
$orderService->setShippingDataValue($orderId, 'track_number', $trackNumber);
```

Если сервис используется через `WishboxRadicalMartOrderServiceAwareTrait`, создавший объект код должен предварительно внедрить его методом `setWishboxRadicalMartOrderService()`.

## Сборка

Из корня проекта выполните:

```bash
php phing-latest.phar
```

Команда сначала создаёт `lib_wishboxradicalmart.zip`, затем помещает его вместе с manifest и языковыми файлами в `pkg_wishboxradicalmart.zip`. Временный архив библиотеки после сборки удаляется.
