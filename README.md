# WishBox RadicalMart

Joomla-пакет устанавливает библиотеку `lib_wishboxradicalmart` с сервисом для изменения данных доставки в заказах RadicalMart.

## Требования

- установленный и включённый компонент `com_radicalmart`;
- версия PHP, поддерживаемая установленными версиями Joomla и RadicalMart.

## Сервис заказов

Класс `WishboxRadicalMartLibrary\Service\Order\OrderService` записывает значение в секцию `data` реестра `shipping` заказа:

```php
use WishboxRadicalMartLibrary\Service\Order\ProductService;

$orderService = new ProductService();
$orderService->setShippingDataValue($orderId, 'track_number', $trackNumber);
```

Если сервис используется через `ProductServiceAwareTrait`, создавший объект код должен предварительно внедрить его методом `setOrderService()`.

## Сборка

Из корня проекта выполните:

```bash
php phing-latest.phar
```

Команда сначала создаёт `lib_wishboxradicalmart.zip`, затем помещает его вместе с manifest и языковыми файлами в `pkg_wishboxradicalmart.zip`. Временный архив библиотеки после сборки удаляется.
