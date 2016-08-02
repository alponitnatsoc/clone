<?php

namespace RocketSeller\TwoPickBundle\Entity;

/**
 * PurchaseOrdersDescriptionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PurchaseOrdersDescriptionRepository extends \Doctrine\ORM\EntityRepository
{
	public function getPurchaseOrderDescription($orderId) {
		return $this->getEntityManager()
			->createQuery('SELECT po FROM RocketSellerTwoPickBundle:PurchaseOrdersDescription po WHERE po.purchaseOrdersPurchaseOrders=:id')
			->setParameter('id', $orderId)
			->getResult();
	}
}
