# Payeer Trade API

Library for work with Payer Trade API: https://payeer.com/api/trade/

* [Usage](#usage)
* [Available Methods](#available-methods)

##Usage
You shall wrap API calls using try {} catch() {} to handle any errors.
<br>
```php
$api = new API();
try {
    $time = $api->time();
    $user = new User("API-ID", "API-SECRET");
    $balance = $api->account($user);
    var_dump($time);
    var_dump($balance);
} catch (\Gurezkiy\TradeApi\Exceptions\TradeException $e) {
    var_dump($e);
}
```

##Available Methods

```php
$api->time();
$api->info(pairs: $pairs = []);
$api->orders(pairs: $pairs);
$api->trades(pairs: $pairs);
$api->account(user: $user);
$api->orderCreate(params: $params, user: $user);
$api->orderStatus(orderId: 123456, user: $user);
$api->orderCancel(orderId: 123456, user: $user);
$api->ordersCancel(params: $params, user: $user);
$api->ordersMy(user: $user, pairs: $pairs = [], action: $action = null);
$api->historyMy(user: $user, params: $params = []);
$api->tradesMy(user: $user, params: $params = []);
```

##Licence
MIT