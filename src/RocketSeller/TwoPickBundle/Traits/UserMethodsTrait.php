<?php

namespace RocketSeller\TwoPickBundle\Traits;

trait UserMethodsTrait
{

    /**
     * Valida si el codigo existe en la tabla de usuarios y retorna los registros
     * @param type $userId
     * @return type
     */
    protected function getUserById($userId)
    {
        if ($userId) {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:User');
            $user = $repository->findBy(array('id' => $userId));
            if ($user) {
                return $user;
            }
        }
        return false;
    }

}
