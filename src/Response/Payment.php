<?php

namespace Webburza\Payum\Setefi\Response;

use Webburza\Payum\Setefi\Api;

/**
 * Class Payment.
 */
class Payment
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $token;
    /** @var string */
    private $hostedUrl;

    /**
     * @param string $id
     * @param string $token
     * @param string $hostedUrl
     */
    public function __construct($id, $token, $hostedUrl)
    {
        $this->id = $id;
        $this->token = $token;
        $this->hostedUrl = $hostedUrl;
    }

    /**
     * @param array $response
     *
     * @return Payment
     */
    public static function fromSetefiResponse(array $response)
    {
        return new self(
            $response[Api::PROPERTY_PAYMENT_ID],
            $response[Api::PROPERTY_SECURITY_TOKEN],
            $response[Api::PROPERTY_HOSTED_URL]
        );
    }

    /**
     * @param array $array
     *
     * @return Payment
     */
    public static function fromArray(array $array)
    {
        return new self($array['id'], $array['token'], $array['hostedUrl']);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'hostedUrl' => $this->hostedUrl,
        ];
    }

    /**
     * @param PaymentAuthorization $authorization
     *
     * @return bool
     */
    public function isMatchingAuthorization(PaymentAuthorization $authorization)
    {
        return $authorization->isMatching($this->id, $this->token);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return sprintf('%1$s?paymentId=%2$s', $this->hostedUrl, $this->id);
    }
}
