<?php

namespace RocketSeller\TwoPickBundle\Traits;

trait InvitationMethodsTrait
{

    /**
     * Retorna la invitacion
     * @param type $email
     * @return type
     */
    protected function findInvitationByEmail($email)
    {
        if ($email) {
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Invitation');
            $invitation = $repository->findBy(array('email' => $email, 'status' => 0));
            if ($invitation) {
                return $invitation;
            }
        }
        return false;
    }

}
