9) Override the response
------------------------

For certain use-cases it is necessary to override the response of the login.
This can be done by using the ``AssembleResponseEvent``:

__NOTE: this feature is only available for the login route as the logout returns a 204 by default, so no customization is needed right now.__

``` php
use Ma27\ApiKeyAuthenticationBundle\Ma27ApiKeyAuthenticationEvents;
use Ma27\ApiKeyAuthenticationBundle\Event\AssembleResponseEvent;

class CustomResponseListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(Ma27ApiKeyAuthenticationEvents::ASSEMBLE_RESPONSE => 'onResponseCreation');
    }

    public function onResponseCreation(AssembleResponseEvent $event)
    {
        if ($event->isSuccess()) {
            $user = $event->getUser();
            // do sth. with $user

            $event->setResponse(array(/* response data */));
            // propagation must be stopped to avoid calling the
            // default response listener which would override everything.
            $event->stopPropagation();
            return;
        }

        // handle the error event
        $exception = $event->getException();
        $event->setResponse(new JsonResponse(array(/* response data */)));
    }
}
```

Now this subscriber must be registered and tagged as `kernel.event_subscriber` and you can override this response.
