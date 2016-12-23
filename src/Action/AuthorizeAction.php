<?php

namespace Webburza\Payum\Setefi\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\GetHttpRequest;
use Webburza\Payum\Setefi\Api;

/**
 * Class AuthorizeAction.
 *
 * @property Api $api
 */
class AuthorizeAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;
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
     * @param Authorize $request
     *
     * @throws \Payum\Core\Reply\HttpResponse
     * @throws \Payum\Core\Exception\LogicException
     * @throws \Payum\Core\Reply\ReplyInterface
     * @throws \Payum\Core\Exception\InvalidArgumentException
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $authRequest = ArrayObject::ensureArrayObject($httpRequest->request);
        $authRequest->validateNotEmpty(['paymentid', 'securitytoken', 'authorizationcode', 'responsecode', 'result']);

        $details->replace($this->api->authorizeTransaction((array) $details, (array) $authRequest));

        if ($details['authorization']) {
            // TODO: we can communicate back with Setefi, what can we say here? Maybe a different response URL?
            throw new HttpResponse('OK');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
