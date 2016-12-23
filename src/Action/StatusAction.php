<?php

namespace Webburza\Payum\Setefi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Webburza\Payum\Setefi\Response\PaymentAuthorization;

/**
 * Class StatusAction.
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false === isset($model['payment'])) {
            $request->markNew();

            return;
        }

        if (true === isset($model['authorization'])) {
            $authorization = PaymentAuthorization::fromArray($model['authorization']);
            if ($authorization->isValid()) {
                $request->markCaptured();

                return;
            }
            $request->markFailed();

            return;
        }

        if (true === isset($model['payment'])) {
            $request->markAuthorized();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
