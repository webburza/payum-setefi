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
    /** @var string */
    protected $cardCountry;
    /** @var string */
    protected $cardExpiryDate;
    /** @var string */
    protected $cardType;
    /** @var string */
    protected $cardMaskedPan;
    /** @var string */
    protected $card3DSecure;
    /** @var string */
    protected $rrn;
    /** @var string */
    protected $custom;

    /**
     * @param string $payment
     * @param string $result
     * @param string $code
     * @param string $token
     * @param string $authorization
     */
    public function __construct(
        $payment,
        $result,
        $code,
        $token = null,
        $authorization = null,
        $cardCountry = null,
        $cardExpiryDate = null,
        $cardType = null,
        $cardMaskedPan = null,
        $card3DSecure = null,
        $rrn = null,
        $custom = null
    ) {
        $this->payment = $payment;
        $this->result = $result;
        $this->code = $code;
        $this->token = $token;
        $this->authorization = $authorization;
        $this->cardCountry = $cardCountry;
        $this->cardExpiryDate = $cardExpiryDate;
        $this->cardType = $cardType;
        $this->cardMaskedPan = $cardMaskedPan;
        $this->card3DSecure = $card3DSecure;
        $this->rrn = $rrn;
        $this->custom = $custom;
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

        $cardCountry = isset($response[Api::PROPERTY_CARD_COUNTRY]) ? $response[Api::PROPERTY_CARD_COUNTRY] : null;
        $cardExpiryDate = isset($response[Api::PROPERTY_CARD_EXPIRY]) ? $response[Api::PROPERTY_CARD_EXPIRY] : null;
        $cardType = isset($response[Api::PROPERTY_CARD_TYPE]) ? $response[Api::PROPERTY_CARD_TYPE] : null;
        $cardMaskedPan = isset($response[Api::PROPERTY_CARD_MASKED_PAN]) ? $response[Api::PROPERTY_CARD_MASKED_PAN] : null;
        $card3DSecure = isset($response[Api::PROPERTY_CARD_3D_SECURE]) ? $response[Api::PROPERTY_CARD_3D_SECURE] : null;
        $rrn = isset($response[Api::PROPERTY_RRN]) ? $response[Api::PROPERTY_RRN] : null;
        $custom = isset($response[Api::PROPERTY_CUSTOM_FIELD]) ? $response[Api::PROPERTY_CUSTOM_FIELD] : null;

        return new self(
            $payment, $result, $code, $token, $authorization,
            $cardCountry, $cardExpiryDate, $cardType, $cardMaskedPan, $card3DSecure, $rrn, $custom
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
            $array['result'],
            $array['code'],
            $array['token'],
            $array['authorization'],
            $array['cardCountry'],
            $array['cardExpiryDate'],
            $array['cardType'],
            $array['cardMaskedPan'],
            $array['card3DSecure'],
            $array['rrn'],
            $array['custom']
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
            'cardCountry' => $this->cardCountry,
            'cardExpiryDate' => $this->cardExpiryDate,
            'cardType' => $this->cardType,
            'cardMaskedPan' => $this->cardMaskedPan,
            'card3DSecure' => $this->card3DSecure,
            'rrn' => $this->rrn,
            'custom' => $this->custom,
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
