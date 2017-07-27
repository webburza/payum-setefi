<?php

namespace Webburza\Payum\Setefi;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Webburza\Payum\Setefi\Action\AuthorizeAction;
use Webburza\Payum\Setefi\Action\CaptureAction;
use Webburza\Payum\Setefi\Action\ConvertPaymentAction;
use Webburza\Payum\Setefi\Action\CreateTransactionAction;
use Webburza\Payum\Setefi\Action\RefundAction;
use Webburza\Payum\Setefi\Action\StatusAction;

/**
 * Class SetefiGatewayFactory.
 */
class SetefiGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException
     * @throws \Payum\Core\Exception\LogicException
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(
            [
                'payum.factory_name' => 'setefi',
                'payum.factory_title' => 'Setefi',
                'payum.action.capture' => new CaptureAction(),
                'payum.action.authorize' => new AuthorizeAction(),
                'payum.action.refund' => new RefundAction(),
                'payum.action.status' => new StatusAction(),
                'payum.action.convert_payment' => new ConvertPaymentAction(),

                'payum.action.api.create_transaction' => new CreateTransactionAction(),
            ]
        );

        if (null === $config['payum.api']) {
            $config['payum.default_options'] = [
                'sandbox' => true,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['terminal_id', 'terminal_password', 'sandbox'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validatedKeysSet($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
