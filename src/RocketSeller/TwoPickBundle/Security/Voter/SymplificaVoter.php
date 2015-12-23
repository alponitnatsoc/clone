<?php

namespace RocketSeller\TwoPickBundle\Security\Voter;

use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

use RocketSeller\TwoPickBundle\Entity\Task;

use Doctrine\ORM\EntityManager; 

class SymplificaVoter extends AbstractVoter {

	public function __construct(EntityManager $em)
	{
    	$this->em = $em;
	}

	/**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses() {
    	return array('RocketSeller\TwoPickBundle\Entity\User');
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute.
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes() {
    	$tasks = $this->em->getRepository('RocketSellerTwoPickBundle:Task')->findAll();
    	$retorno = array();
    	foreach($tasks as $task) {
    		$retorno[] = $task->getName();
    	}
		return $retorno;
    }

    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user).
     *
     * @param string               $attribute
     * @param object               $object
     * @param UserInterface|string $user
     *
     * @return bool
     */
    protected function isGranted($attribute, $object, $user = null) {
    	if(!is_object($user)) {
    		return false;
    	}
    	
    	dump($user->getRoles());
    	$repository = $this->em->getRepository('RocketSellerTwoPickBundle:Role');
    	$query = $repository->createQueryBuilder('r');
                $query->andWhere('r.name in (:roles)')
                    ->setParameter('roles', $user->getRoles());
        
        $roles = $query->getQuery()->getResult();
        
        foreach($roles as $role) {
        	dump($role);
        	foreach($role->getRoleHasTask() as $roleHasTask){
        		if($roleHasTask->getTaskTask()->getName()==$attribute)
        			return true;
			}
        }

    	return false;
    }

}