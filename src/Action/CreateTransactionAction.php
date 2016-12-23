<?php

namespace Webburza\Payum\Setefi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;
use Webburza\Payum\Setefi\Api;
use Webburza\Payum\Setefi\Request\CreateTransaction;
use Webburza\Payum\Setefi\Response\Payment;

/**
 * Class CreateTransactionAction.
 */
class CreateTransactionAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param $request CreateTransaction
     *
     * @throws \Payum\Core\Exception\LogicException
     * @throws \Payum\Core\Exception\InvalidArgumentException
     * @throws \Payum\Core\Reply\HttpRedirect
     * @throws \Payum\Core\Exception\Http\HttpException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['payment']) {
            $payment = Payment::fromArray($details['payment']);
            throw new LogicException(
                sprintf(
                    'The transaction has already been created for this payment. paymentID: %s',
                    $payment->getId()
                )
            );
        }

        $details->validateNotEmpty(
            ['amount', 'currencyCode', 'description', 'responseToMerchantUrl', 'recoveryUrl', 'merchantOrderId']
        );

        $details->replace($this->api->createTransaction((array) $details));

        if ($details['payment']) {
            $payment = Payment::fromArray($details['payment']);
            throw new HttpRedirect($payment->getUrl());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
