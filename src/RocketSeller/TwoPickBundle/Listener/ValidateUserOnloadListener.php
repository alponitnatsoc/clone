<?php
namespace RocketSeller\TwoPickBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Entity\User;

class ValidateUserOnloadListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(UrlGeneratorInterface $router, EntityManager $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * This method is executed for every Http Request. If the content is found
     * in Apc Cache the execution flow is stopped and a Response is returned
     * inmediately, no controller action is executed.
     * @param GetResponseEvent $event
     * @return type
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $securityContext = $this->container->get('security.context', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        if (null !== $securityContext && null !== $securityContext->getToken() && "anon." != $securityContext->getToken()->getUsername()) {
//             $this->em->flush();
//             echo $userId = $securityContext->getToken()->getUser()->getStatus();
//             $user = $this->em->getRepository('RocketSellerTwoPickBundle:User')->find($userId);
//             echo $user->getEmail();

        	/** @var User $user */
        	$user = $securityContext->getToken()->getUser();
//         	$dc = $user->getDateCreated();
//         	$dt = new \DateTime();

//         	$dateDif = date_diff($dt, $dc);
//         	echo "El usuario " . $user->getEmail() . " fue creado hace " . $dateDif->format('%y Year %m Month %d Day %h Hours %i Minute %s Seconds');

            if ($this->router->getContext()->getPathInfo() != "/activar-suscripcion" && $user->getStatus() == 0) {
                echo "Usuario inactivo";
                $url = $this->router->generate('inactive_user');
                $event->setResponse(new RedirectResponse($url));
                $event->stopPropagation();
                return $event;
            }
        }
    }

    /*
     * This method prevent to cache Response when occurs Exceptions
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $event->stopPropagation();
    }
}