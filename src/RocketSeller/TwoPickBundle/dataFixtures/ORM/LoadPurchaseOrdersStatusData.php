<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;

class LoadPurchaseOrdersStatusData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Pendiente');
        $PurchaseOrdersStatus->setDescription('Pendiente');
        $PurchaseOrdersStatus->setIdNovoPay('S1');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Procesando');
        $PurchaseOrdersStatus->setDescription('Procesando');
        $PurchaseOrdersStatus->setIdNovoPay('S2');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Candelada');
        $PurchaseOrdersStatus->setDescription('Candelada');
        $PurchaseOrdersStatus->setIdNovoPay('S3');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Aprobando');
        $PurchaseOrdersStatus->setDescription('Aprobando');
        $PurchaseOrdersStatus->setIdNovoPay('S4');
        $manager->persist($PurchaseOrdersStatus);
        

        $PurchaseOrdersStatus = new PurchaseOrdersStatus();
        $PurchaseOrdersStatus->setName('Pendiente por Pago');
        $PurchaseOrdersStatus->setDescription('Pendiente');
        $PurchaseOrdersStatus->setIdNovoPay('P1');
        $manager->persist($PurchaseOrdersStatus);

        $PurchaseOrdersStatusAprobado = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado->setName('Aprobado');
        $PurchaseOrdersStatusAprobado->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado->setIdNovoPay('00');
        $manager->persist($PurchaseOrdersStatusAprobado);

        $PurchaseOrdersStatusAprobado08 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusAprobado08->setName('Aprobado');
        $PurchaseOrdersStatusAprobado08->setDescription('Aprobado');
        $PurchaseOrdersStatusAprobado08->setIdNovoPay('08');
        $manager->persist($PurchaseOrdersStatusAprobado08);

        $PurchaseOrdersStatusRechazado12 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado12->setName('Rechazado');
        $PurchaseOrdersStatusRechazado12->setDescription('Transaccion Invalida');
        $PurchaseOrdersStatusRechazado12->setIdNovoPay('12');
        $manager->persist($PurchaseOrdersStatusRechazado12);

        $PurchaseOrdersStatusRechazado13 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado13->setName('Rechazado');
        $PurchaseOrdersStatusRechazado13->setDescription('Monto Invalido');
        $PurchaseOrdersStatusRechazado13->setIdNovoPay('13');
        $manager->persist($PurchaseOrdersStatusRechazado13);

        $PurchaseOrdersStatusRechazado14 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado14->setName('Rechazado');
        $PurchaseOrdersStatusRechazado14->setDescription('Tarjeta Invalida');
        $PurchaseOrdersStatusRechazado14->setIdNovoPay('14');
        $manager->persist($PurchaseOrdersStatusRechazado14);

        $PurchaseOrdersStatusRechazado31 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado31->setName('Rechazado');
        $PurchaseOrdersStatusRechazado31->setDescription('Tarjeta No Soportada');
        $PurchaseOrdersStatusRechazado31->setIdNovoPay('31');
        $manager->persist($PurchaseOrdersStatusRechazado31);

        $PurchaseOrdersStatusRechazado51 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado51->setName('Rechazado');
        $PurchaseOrdersStatusRechazado51->setDescription('Fondos Insuficientes');
        $PurchaseOrdersStatusRechazado51->setIdNovoPay('51');
        $manager->persist($PurchaseOrdersStatusRechazado51);

        $PurchaseOrdersStatusRechazado54 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado54->setName('Rechazado');
        $PurchaseOrdersStatusRechazado54->setDescription('Tarjeta Vencida');
        $PurchaseOrdersStatusRechazado54->setIdNovoPay('54');
        $manager->persist($PurchaseOrdersStatusRechazado54);

        $PurchaseOrdersStatusRechazado57 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado57->setName('Rechazado');
        $PurchaseOrdersStatusRechazado57->setDescription('Transacción No Permitida');
        $PurchaseOrdersStatusRechazado57->setIdNovoPay('57');
        $manager->persist($PurchaseOrdersStatusRechazado57);

        $PurchaseOrdersStatusRechazado61 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado61->setName('Rechazado');
        $PurchaseOrdersStatusRechazado61->setDescription('Excede Monto Limite');
        $PurchaseOrdersStatusRechazado61->setIdNovoPay('61');
        $manager->persist($PurchaseOrdersStatusRechazado61);

        $PurchaseOrdersStatusRechazado65 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado65->setName('Rechazado');
        $PurchaseOrdersStatusRechazado65->setDescription('Excede Uso Dia');
        $PurchaseOrdersStatusRechazado65->setIdNovoPay('65');
        $manager->persist($PurchaseOrdersStatusRechazado65);

        $PurchaseOrdersStatusRechazado86 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado86->setName('Rechazado');
        $PurchaseOrdersStatusRechazado86->setDescription('Cuenta Invalida');
        $PurchaseOrdersStatusRechazado86->setIdNovoPay('86');
        $manager->persist($PurchaseOrdersStatusRechazado86);

        $PurchaseOrdersStatusRechazado87 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado87->setName('Rechazado');
        $PurchaseOrdersStatusRechazado87->setDescription('Excede Monto Diario');
        $PurchaseOrdersStatusRechazado87->setIdNovoPay('87');
        $manager->persist($PurchaseOrdersStatusRechazado87);

        $PurchaseOrdersStatusRechazado94 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado94->setName('Rechazado');
        $PurchaseOrdersStatusRechazado94->setDescription('Transacción duplicada');
        $PurchaseOrdersStatusRechazado94->setIdNovoPay('94');
        $manager->persist($PurchaseOrdersStatusRechazado94);

        $PurchaseOrdersStatusRechazado96 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazado96->setName('Rechazado');
        $PurchaseOrdersStatusRechazado96->setDescription('Transacción no puede ser procesada');
        $PurchaseOrdersStatusRechazado96->setIdNovoPay('96');
        $manager->persist($PurchaseOrdersStatusRechazado96);

        $PurchaseOrdersStatusRechazadoB6 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB6->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB6->setDescription('Supera el numero maximo de transacciones diarias');
        $PurchaseOrdersStatusRechazadoB6->setIdNovoPay('B6');
        $manager->persist($PurchaseOrdersStatusRechazadoB6);

        $PurchaseOrdersStatusRechazadoB7 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusRechazadoB7->setName('Rechazado');
        $PurchaseOrdersStatusRechazadoB7->setDescription('Supera el valor maximo de la transaccion');
        $PurchaseOrdersStatusRechazadoB7->setIdNovoPay('B7');
        $manager->persist($PurchaseOrdersStatusRechazadoB7);

        $PurchaseOrdersStatusDispersion1 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusDispersion1->setName('DispersionAprobada');
        $PurchaseOrdersStatusDispersion1->setDescription('La dispersion fue aprobada');
        $PurchaseOrdersStatusDispersion1->setIdNovoPay('-1');
        $manager->persist($PurchaseOrdersStatusDispersion1);

        $PurchaseOrdersStatusDispersion2 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusDispersion2->setName('DispersionRechazada');
        $PurchaseOrdersStatusDispersion2->setDescription('La dispersion fue rechazada');
        $PurchaseOrdersStatusDispersion2->setIdNovoPay('-2');
        $manager->persist($PurchaseOrdersStatusDispersion2);

        $PurchaseOrdersStatusDispersion3 = new PurchaseOrdersStatus();
        $PurchaseOrdersStatusDispersion3->setName('DispersionDevuelta');
        $PurchaseOrdersStatusDispersion3->setDescription('El monto a pagar fue devuelto en su totalidad');
        $PurchaseOrdersStatusDispersion3->setIdNovoPay('-3');
        $manager->persist($PurchaseOrdersStatusDispersion3);
		
	      //Estados para inscripción del empleador en el operador de pila
		    $PurchaseOrdersStatusInscribirOperador1 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusInscribirOperador1->setName('Inscripción no enviada');
		    $PurchaseOrdersStatusInscribirOperador1->setDescription('El servicio generó error al momento de intentar llamar el proceso de inscripción');
		    $PurchaseOrdersStatusInscribirOperador1->setIdNovoPay('InsPil-ErrSer');
	      $manager->persist($PurchaseOrdersStatusInscribirOperador1);
	
		    $PurchaseOrdersStatusInscribirOperador2 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusInscribirOperador2->setName('Inscripción enviada');
		    $PurchaseOrdersStatusInscribirOperador2->setDescription('Ya se solicitó la creación del empleador en el operador');
		    $PurchaseOrdersStatusInscribirOperador2->setIdNovoPay('InsPil-InsEnv');
		    $manager->persist($PurchaseOrdersStatusInscribirOperador2);
	
		    $PurchaseOrdersStatusInscribirOperador3 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusInscribirOperador3->setName('Inscripción rechazada');
		    $PurchaseOrdersStatusInscribirOperador3->setDescription('Falló la inscripción en el operador de pila');
		    $PurchaseOrdersStatusInscribirOperador3->setIdNovoPay('InsPil-InsRec');
		    $manager->persist($PurchaseOrdersStatusInscribirOperador3);
	
		    $PurchaseOrdersStatusInscribirOperador4 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusInscribirOperador4->setName('Inscripción aprobada');
		    $PurchaseOrdersStatusInscribirOperador4->setDescription('El empleador ya está inscrito en el operador');
		    $PurchaseOrdersStatusInscribirOperador4->setIdNovoPay('InsPil-InsOk');
		    $manager->persist($PurchaseOrdersStatusInscribirOperador4);
	
		    //Estados para carga de planilla en el operador de pila
		    $PurchaseOrdersStatusCargaPlanilla1 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla1->setName('Planilla error al enviar');
		    $PurchaseOrdersStatusCargaPlanilla1->setDescription('El servicio generó error al momento de intentar llamar el proceso de carga');
		    $PurchaseOrdersStatusCargaPlanilla1->setIdNovoPay('CarPla-ErrSer');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla1);
	
		    $PurchaseOrdersStatusCargaPlanilla2 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla2->setName('Planilla enviada');
		    $PurchaseOrdersStatusCargaPlanilla2->setDescription('Ya se solicitó la carga de la planilla para el empleador');
		    $PurchaseOrdersStatusCargaPlanilla2->setIdNovoPay('CarPla-PlaEnv');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla2);
	
		    $PurchaseOrdersStatusCargaPlanilla3 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla3->setName('Planilla errores');
		    $PurchaseOrdersStatusCargaPlanilla3->setDescription('La planilla presentó errores al subirla al operador');
		    $PurchaseOrdersStatusCargaPlanilla3->setIdNovoPay('CarPla-PlaErr');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla3);
	
		    $PurchaseOrdersStatusCargaPlanilla4 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla4->setName('Planilla advertencias');
		    $PurchaseOrdersStatusCargaPlanilla4->setDescription('La planilla presentó advertencias al subirla al operador');
		    $PurchaseOrdersStatusCargaPlanilla4->setIdNovoPay('CarPla-PlaWar');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla4);
	    
		    $PurchaseOrdersStatusCargaPlanilla6 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla6->setName('Planilla cargada');
		    $PurchaseOrdersStatusCargaPlanilla6->setDescription('La planilla se cargó correctamente');
		    $PurchaseOrdersStatusCargaPlanilla6->setIdNovoPay('CarPla-PlaOK');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla6);
	
		    $PurchaseOrdersStatusCargaPlanilla7 = new PurchaseOrdersStatus();
		    $PurchaseOrdersStatusCargaPlanilla7->setName('Planilla no enviada, tiene novedades');
		    $PurchaseOrdersStatusCargaPlanilla7->setDescription('Hay novedades entonces no se envia');
		    $PurchaseOrdersStatusCargaPlanilla7->setIdNovoPay('CarPla-ErrNov');
		    $manager->persist($PurchaseOrdersStatusCargaPlanilla7);
	    
	      $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }

}
