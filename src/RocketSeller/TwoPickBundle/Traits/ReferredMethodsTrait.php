<?php

namespace RocketSeller\TwoPickBundle\Traits;

trait ReferredMethodsTrait
{

    /**
     * Valida si el codigo existe en la tabla de usuarios y retorna el usuario
     * @param string $code codigo de referido
     * @return User
     */
    protected function userValidateCode($code)
    {
        if ($code) {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User');
            $user = $repository->findOneBy(array('code' => $code));
            if ($user) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Valida si el codigo existe en la tabla de referidos y retorna los registros
     * @param type $userId
     * @param type $referredUserId
     * @return type
     */
    protected function referedValidateCode($userId, $referredUserId)
    {
        if ($userId) {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Referred');
            $referencia = $repository->findOneBy(array('userId' => $userId, 'referredUserId' => $referredUserId));
            if ($referencia) {
                return $referencia;
            }
        }
        return false;
    }

}
