<?php

namespace Webburza\Payum\Setefi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Webburza\Payum\Setefi\Api;
use Webburza\Payum\Setefi\Request\CreateTransaction;

/**
 * Class CaptureAction.
 *
 * @property Api $api
 */
class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

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
     * @param Capture $request
     *
     * @throws \Payum\Core\Reply\HttpPostRedirect
     * @throws \Payum\Core\Reply\ReplyInterface
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $details['payment']) {
            // TODO: hardcoded for now
            $details['language'] = Api::LANGUAGE_ITA;
            if (null === $details['responseToMerchantUrl'] && $request->getToken()) {
                // server to server sync
                $details['responseToMerchantUrl'] = $request->getToken()->getTargetUrl();
            }
            if (null === $details['recoveryUrl'] && $request->getToken()) {
                // if our server to server fails, this way we'll still try to sync
                // otherwise just redirect to success
                $details['recoveryUrl'] = $request->getToken()->getTargetUrl();
            }

            // first pass
            // we're need to create the transaction on Setefi
            // and redirect the user there
            $this->gateway->execute(new CreateTransaction($details));
        } elseif (null === $details['authorization'] && null === $details['cancellation']) {
            // second pass
            // we're authorizing the transaction by a server-to-server request
            // this request is served to Setefi backend, NOT the returning user
            $this->gateway->execute(new Authorize($details));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
