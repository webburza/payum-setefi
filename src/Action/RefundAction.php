<?php

namespace Webburza\Payum\Setefi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;

/**
 * Class RefundAction.
 */
class RefundAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Refund $request
     *
     * @throws \LogicException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
