<?php

namespace Gurezkiy\TradeApi;

use Gurezkiy\TradeApi\Exceptions\TradeException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class API
{
    /**
     * Basic URL
     * @var string $basicUrl
     */
    private string $basicUrl = "https://payeer.com/api/trade/";

    /**
     * @var Client $client
     */
    private Client $client;

    /**
     * @param string|null $basicUrl
     */
    public function __construct(?string $basicUrl = null)
    {
        if(isset($basicUrl))
        {
            $this->basicUrl = $basicUrl;
        }
        $this->client = new Client([
            "base_uri"=>$this->basicUrl,
            "headers"=> [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ]
        ]);

    }

    /**
     * Get timestamp
     *
     * @return int
     */
    private function getTimestamp(): int
    {
       return (int)round(microtime(true) * 1000);
    }

    /**
     * Make a request
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @param User|null $user
     * @return array
     * @throws TradeException
     */
    protected function request(string $method,
                               string $path,
                               array $params = [],
                               User $user = null): array
    {
        try
        {
            $requestParams = [];
            if($user)
            {
                $req = json_encode($params);
                $sign = hash_hmac('sha256', $path.$req, $user->getSecret());
                $requestParams['headers'] = [
                    "API-ID" => $user->getApiId(),
                    "API-SIGN" => $sign,
                ];
            }
            match ($method) {
                Methods::POST => $requestParams['json'] = $params,
                Methods::GET => $requestParams['query'] = $params
            };
            $response = $this->client->request($method, $path, $requestParams);
            $status = $response->getStatusCode();
            if($status !== 200)
            {
                throw new TradeException("API connection status: $status", $status);
            }
            $result = json_decode($response->getBody()->getContents(), true);
            return is_array($result) ? $result : throw new TradeException("Response is`n JSON content");
        }
        catch (GuzzleException $exception)
        {
            throw new TradeException("Guzzle exception: " . $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Connection test
     *
     * @return array
     * @throws TradeException
     */
    public function time(): array
    {
        return $this->request(Methods::GET, 'time');
    }

    /**
     * Pairs and limits
     *
     * @param array $pairs
     * @return array
     * @throws TradeException
     */
    public function info(array $pairs = []): array
    {
        $pair = "";
        $method = Methods::GET;
        if($pairs)
        {
            $pair = implode(",", $pairs);
            $method = Methods::POST;
        }

        return $this->request($method, "info", [
            'pair'=> $pair
        ]);
    }

    /**
     * Price statistics
     *
     * @param array $pairs
     * @return array
     * @throws TradeException
     */
    public function ticker(array $pairs = []): array
    {
        $pair = "";
        $method = Methods::GET;
        if($pairs)
        {
            $pair = implode(",", $pairs);
            $method = Methods::POST;
        }

        return $this->request($method, "ticker", [
            'pair'=> $pair
        ]);
    }

    /**
     * Orders
     *
     * @param array $pairs
     * @return array
     * @throws TradeException
     */
    public function orders(array $pairs): array
    {
        $pair = implode(",", $pairs);
        return $this->request(Methods::POST, "orders", [
            'pair'=> $pair
        ]);
    }

    /**
     * Trades
     *
     * @param array $pairs
     * @return array
     * @throws TradeException
     */
    public function trades(array $pairs): array
    {
        $pair = implode(",", $pairs);
        return $this->request(Methods::POST, "trades", [
            'pair'=> $pair
        ]);
    }

    /**
     * Balance
     *
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function account(User $user): array
    {
        return $this->request(Methods::POST, "account", [
            'ts'=> $this->getTimestamp()
        ], $user);
    }

    /**
     * New order
     *
     * @param array $params
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function orderCreate(array $params, User $user): array
    {
        $params['ts'] = $this->getTimestamp();
        return $this->request(Methods::POST, "order_create", $params, $user);
    }

    /**
     * Order status
     *
     * @param int $orderId
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function orderStatus(int $orderId, User $user): array
    {
        return $this->request(Methods::POST, "order_status", [
            'order_id'=>$orderId,
            'ts' => $this->getTimestamp(),
        ], $user);
    }

    /**
     * Cancel order
     *
     * @param int $orderId
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function orderCancel(int $orderId, User $user): array
    {
        return $this->request(Methods::POST, "order_cancel", [
            'order_id'=>$orderId,
            'ts' => $this->getTimestamp(),
        ], $user);
    }

    /**
     * Cancel orders
     *
     * @param array $params
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function ordersCancel(array $params, User $user): array
    {
        $params['ts'] = $this->getTimestamp();
        return $this->request(Methods::POST, "order_cancel", $params, $user);
    }

    /**
     * My orders
     *
     * @param User $user
     * @param array $pairs
     * @param string|null $action
     * @return array
     * @throws TradeException
     */
    public function ordersMy(User $user, array $pairs = [], string $action = null): array
    {
        $params = [
            'ts' => $this->getTimestamp()
        ];
        if($pairs)
        {
            $params['pair'] = implode(",", $pairs);
        }
        if($action)
        {
            $params['action'] = $action;
        }
        return $this->request(Methods::POST, "my_orders", $params, $user);
    }

    /**
     * My history
     *
     * @param array $params
     * @param User $user
     * @return array
     * @throws TradeException
     */
    public function historyMy(User $user, array $params = []): array
    {
        $params['ts'] = $this->getTimestamp();
        return $this->request(Methods::POST, "my_history", $params, $user);
    }

    /**
     * My trades
     *
     * @param User $user
     * @param array $params
     * @return array
     * @throws TradeException
     */
    public function tradesMy(User $user, array $params = []): array
    {
        $params['ts'] = $this->getTimestamp();
        return $this->request(Methods::POST, "my_trades", $params, $user);
    }
}