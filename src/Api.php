<?php

namespace Webburza\Payum\Setefi;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\HttpClientInterface;
use Webburza\Payum\Setefi\Response\Payment;
use Webburza\Payum\Setefi\Response\PaymentAuthorization;

/**
 * Class Api.
 */
class Api
{
    const CURRENCY_GBP = 'GBP';
    const CURRENCY_EUR = 'EUR';
    const CURRENCY_USD = 'USD';

    const LANGUAGE_ITA = 'ITA';

    const OPERATION_INITIALIZE = 'initialize';

    // Setefi response properties
    const PROPERTY_AUTHORIZATION = 'authorizationcode';
    const PROPERTY_HOSTED_URL = 'hostedpageurl';
    const PROPERTY_PAYMENT_ID = 'paymentid';
    const PROPERTY_RESULT = 'result';
    const PROPERTY_RESPONSE_CODE = 'responsecode';
    const PROPERTY_SECURITY_TOKEN = 'securitytoken';

    const STATUS_SUCCESS = '000';
    const STATUS_CANCELED = '-1';

    private static $currencies = [
        self::CURRENCY_GBP => '826',
        self::CURRENCY_EUR => '978',
        self::CURRENCY_USD => '840',
    ];

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $params
     *
     * @return array
     *
     * @throws \Payum\Core\Exception\Http\HttpException
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    public function createTransaction(array $params)
    {
        $params['operationType'] = static::OPERATION_INITIALIZE;
        $params = $this->preparePayment($params);

        // we get SimpleXML back, convert to a DVO
        $response = (array) $this->doRequest('POST', $params);
        $payment = Payment::fromSetefiResponse($response);
        $params['payment'] = $payment->toArray();

        return $params;
    }

    /**
     * @param array $model
     * @param array $authorizationRequest
     *
     * @return array
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    public function authorizeTransaction(array $model, array $authorizationRequest)
    {
        $payment = Payment::fromArray($model['payment']);
        $authorization = PaymentAuthorization::fromSetefiResponse($authorizationRequest);

        if (true === $authorization->isCanceled()) {
            $model['cancellation'] = $authorization->toArray();

            return $model;
        }

        if (false === $payment->isMatchingAuthorization($authorization)) {
            throw new InvalidArgumentException('Invalid payment authorization');
        }
        $model['authorization'] = $authorization->toArray();

        return $model;
    }

    /**
     * @param array $params
     *
     * @return array
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    protected function preparePayment(array $params)
    {
        $params['currencyCode'] = $this->getCurrency($params['currencyCode']);

        return $this->addGlobalParams($params);
    }

    /**
     * @param array $params
     *
     * @return array
     */
    protected function addGlobalParams(array $params)
    {
        $params['id'] = $this->options['terminal_id'];
        $params['password'] = $this->options['terminal_password'];

        return $params;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Payum\Core\Exception\Http\HttpException
     */
    protected function doRequest($method, array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest(
            $method,
            $this->getApiEndpoint(),
            $headers,
            http_build_query($fields)
        );

        $response = $this->client->send($request);
        if (false === ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $xmlResponse = simplexml_load_string($response->getBody());
        if ($xmlResponse->errorcode) {
            throw new HttpException(
                sprintf('Setefi error (%1$s): %2$s', $xmlResponse->errorcode, $xmlResponse->errormessage)
            );
        }

        return $xmlResponse;
    }

    /**
     * {@inheritdoc}
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://test.monetaonline.it/monetaweb/payment/2/xml' : 'https://www.monetaonline.it/monetaweb/payment/2/xml';
    }

    /**
     * @param $currencyCode
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function getCurrency($currencyCode)
    {
        if (false === array_key_exists($currencyCode, self::$currencies)) {
            throw new InvalidArgumentException(sprintf('Setefi: unknown currency "%1$s"', $currencyCode));
        }

        return self::$currencies[$currencyCode];
    }
}
