<?php

namespace RocketSeller\TwoPickBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use RocketSeller\TwoPickBundle\DependencyInjection\Security\Factory\WsseFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RocketSellerTwoPickBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new WsseFactory());
    }
}