<?php

namespace Gurezkiy\TradeApi;

class User
{
    /**
     * @var string $apiId
     */
    private string $apiId;

    /**
     * @var string $secret
     */
    private string $secret;

    /**
     * @param string $apiId
     * @param string $secret
     */
    public function __construct(string $apiId, string $secret)
    {
        $this->apiId = $apiId;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getApiId(): string
    {
        return $this->apiId;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
    }




}