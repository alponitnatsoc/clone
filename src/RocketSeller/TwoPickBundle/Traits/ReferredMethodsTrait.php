<?php

namespace RocketSeller\TwoPickBundle\Traits;

trait ReferredMethodsTrait
{

    /**
     * Retorna el usuario dueño del codigo de referido
     * @param type $code
     * @return type
     */
    protected function validateCode($code)
    {
        if ($code) {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User');
            $user = $repository->findBy(array('code' => $code));
            if ($user) {
                return $user;
            }
        }
        return false;
    }

}
