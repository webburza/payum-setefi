# Setefi Payum gateway

A [Payum](https://payum.forma-pro.com/) gateway for [Setefi](http://www.setefi.it/) payment processor.

**Note:** all [user IDs, passwords and secrets listed are documented by Setefi](https://www.servizi.monetaonline.it/documenti/monetaweb/MonetaWeb%202.0%20-%20Technical%20Documentation.pdf) *(section "TEST ENVIRONMENT CREDENTIALS"*) and shared with all other Setefi sandbox users, they're NOT a secret. 

## Sylius Integration

```yaml
# app/config/config.yml
parameters:
    # NOTE:
    # these are documented by Setefi and shared among all sandbox users, they're NOT a secret
    setefi.id: "99999999"
    setefi.password: "99999999"
    setefi.sandbox: true # false in config_prod.yml

payum:
    gateways:
        setefi:
            factory: "setefi"
            payum.http_client: "@sylius.payum.http_client"
            terminal_id: "%setefi.id%"
            terminal_password: "%setefi.password%"
            sandbox: "%setefi.sandbox%"

sylius_payment:
    gateways:
        setefi: Setefi
```

After having done this, enable the payment method in the Sylius admin interface.

*(**Note:** Any testing of the integration **must** be done on a public-accessible URL (ie. by using [ngrok](https://ngrok.com/)) because Setefi backend needs to be able to do valid server-to-server requests for the process to complete successfully.)* 

## Setefi sandbox environment 

Log in to [Setefi's sandbox environment](https://test.monetaonline.it/monetaweb/backoffice):

*(**Note:** these are documented by Setefi and shared among all sandbox users, they're NOT a secret)*

- *Codice Commerciante:* `009999999`
- *Codice Utente:* `009999999`
- *Password:* `Setefi14`

Note that your transactions will be among all other sandbox's users' transactions, you need to find your own to verify it works.

## Technical implementation

* Entrypoint is [`Webburza\Payum\Setefi\SetefiGatewayFactory`](src/SetefiGatewayFactory.php) which registers all known actions, sets up config and creates a new `Api`.
* [`Webburza\Payum\Setefi\Api`](src/Api.php) is a collection of helpers and constants specific to this provider, it gets injected to all actions implementing `Payum\Core\ApiAwareInterface`.
* Every action has a specific task and they declare on which part of the process they work on by implementing `support($request)` from `Payum\Core\Action\ActionInterface`. The action name itself does not matter.  
  For example, `Webburza\Payum\Setefi\Action\CancelAction` declares it can handle `Payum\Core\Request\Cancel` request and it will receive all those types of requests to handle.  
  **Action and the request are two different things**, you can (and do) have much more actions than you do request types (some gateways have no custom request types).
* We can also define some custom request types such as [`Webburza\Payum\Setefi\Request\CreateTransaction`](src/Request/CreateTransaction.php) for situations where domain-specific events need to happen. After having defined them, we register actions to handle them the same way we do native actions.

## Transaction sequence

1. `Webburza\Payum\Setefi\Action\StatusAction` marks the payment request as `new`
2. `Webburza\Payum\Setefi\Action\CaptureAction` triggers `Webburza\Payum\Setefi\Request\CreateTransaction` request
3. a new Setefi transaction is created using `Webburza\Payum\Setefi\Action\CreateTransactionAction` and a server-to-server (S2S) request
4. if successful, the user is redirected to Setefi web interface
5.
    1. if payment successful, Setefi does a S2S request and `Webburza\Payum\Setefi\Action\StatusAction` marks the payment request as `captured`  
    2. if payment not successful, Setefi does a S2S request and `Webburza\Payum\Setefi\Action\StatusAction` marks the payment request as `canceled`
6. response to 5. is a valid redirect URL for Setefi to redirect to user back to
7. process complete

![Transaction sequence](http://www.plantuml.com/plantuml/proxy?src=https://raw.githubusercontent.com/webburza/payum-setefi/master/docs/transaction.puml#3)
