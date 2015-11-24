<?php
namespace RocketSeller\TwoPickBundle\Listener;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationConfirmListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationConfirm'
        );
    }

    public function onRegistrationConfirm(FormEvent $event)
    {
        $url = $this->router->generate('show_dashboard');

        $event->setResponse(new RedirectResponse($url));
        return $event;
    }
}
 ?>