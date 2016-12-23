<?php

namespace Webburza\Payum\Setefi\Response;

use Webburza\Payum\Setefi\Api;

/**
 * Class PaymentAuthorization.
 */
class PaymentAuthorization
{
    /** @var string */
    protected $authorization;
    /** @var string */
    protected $payment;
    /** @var string */
    protected $token;
    /** @var string */
    protected $code;
    /** @var string */
    protected $result;

    /**
     * @param string $payment
     * @param string $token
     * @param string $authorization
     * @param string $code
     * @param string $result
     */
    public function __construct($payment, $token, $authorization, $code, $result)
    {
        $this->payment = $payment;
        $this->token = $token;
        $this->authorization = $authorization;
        $this->code = $code;
        $this->result = $result;
    }

    /**
     * @param array $response
     *
     * @return PaymentAuthorization
     */
    public static function fromSetefiResponse(array $response)
    {
        return new self(
            $response[Api::PROPERTY_PAYMENT_ID],
            $response[Api::PROPERTY_SECURITY_TOKEN],
            $response[Api::PROPERTY_AUTHORIZATION],
            $response[Api::PROPERTY_RESPONSE_CODE],
            $response[Api::PROPERTY_RESULT]
        );
    }

    /**
     * @param array $array
     *
     * @return PaymentAuthorization
     */
    public static function fromArray(array $array)
    {
        return new self(
            $array['payment'],
            $array['token'],
            $array['authorization'],
            $array['code'],
            $array['result']
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'payment' => $this->payment,
            'token' => $this->token,
            'authorization' => $this->authorization,
            'code' => $this->code,
            'result' => $this->result,
        ];
    }

    /**
     * @param string $payment
     * @param string $token
     *
     * @return bool
     */
    public function isMatching($payment, $token)
    {
        return $this->payment === $payment && $this->token === $token;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->code === Api::STATUS_SUCCESS;
    }
}
