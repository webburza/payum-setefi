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
     * @param string $result
     * @param string $code
     * @param string $token
     * @param string $authorization
     */
    public function __construct($payment, $result, $code, $token = null, $authorization = null)
    {
        $this->payment = $payment;
        $this->result = $result;
        $this->code = $code;
        $this->token = $token;
        $this->authorization = $authorization;
    }

    /**
     * @param array $response
     *
     * @return PaymentAuthorization
     */
    public static function fromSetefiResponse(array $response)
    {
        $payment = $response[Api::PROPERTY_PAYMENT_ID];
        $result = $response[Api::PROPERTY_RESULT];
        $code = isset($response[Api::PROPERTY_RESPONSE_CODE]) ? $response[Api::PROPERTY_RESPONSE_CODE] : Api::STATUS_CANCELED;
        $token = isset($response[Api::PROPERTY_SECURITY_TOKEN]) ? $response[Api::PROPERTY_SECURITY_TOKEN] : null;
        $authorization = isset($response[Api::PROPERTY_AUTHORIZATION]) ? $response[Api::PROPERTY_AUTHORIZATION] : null;

        return new self($payment, $result, $code, $token, $authorization);
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
            $array['result'],
            $array['code'],
            $array['token'],
            $array['authorization']
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'payment' => $this->payment,
            'result' => $this->result,
            'code' => $this->code,
            'token' => $this->token,
            'authorization' => $this->authorization,
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

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->code === Api::STATUS_CANCELED;
    }
}
