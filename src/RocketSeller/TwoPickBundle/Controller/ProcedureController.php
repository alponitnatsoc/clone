<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParameterBag;
use RocketSeller\TwoPickBundle\Entity\ActionError;
use RocketSeller\TwoPickBundle\Entity\ActionType;
use RocketSeller\TwoPickBundle\Entity\Department;
use RocketSeller\TwoPickBundle\Entity\Log;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployeeHasEntity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEntity;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Action;
use RocketSeller\TwoPickBundle\Entity\RealProcedure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class ProcedureController extends Controller
{
	use EmployeeMethodsTrait;

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function indexAction                                          ║
     * ║ shows all the real procedures and filter it by parameters     ║
     * ║ actually there exist two types of realProcedures:             ║
     * ║ EmployerAndEmployeeRegister               REE                 ║
     * ║ ValidationActions                         VAC                 ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $orderType order type for the switch           ║
     * ║  @param string $order order ASC or DESC                       ║
     * ║  @param string $state                                         ║
     * ║  @param string $document                                      ║
     * ║  @param string $names                                         ║
     * ║  @param $prior                                                ║
     * ║  @param Request $request                                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return Response /backoffice/procedures                      ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function indexAction($orderType = 'none' , $order = 'ASC',$state = 'none',$document = 'none',$names = 'none', $prior = 'none', Request $request)
    {

		$this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        //if no search parameters
        if($state == 'none' and $document == 'none' and $names == 'none' and $prior == 'none') {
            /** @var QueryBuilder $query */
            $query = $em->createQueryBuilder();
            switch ($orderType) {
                case 'name'://query ordered by name
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->join('RocketSellerTwoPickBundle:Employer', 'em', 'WITH', 'p.employerEmployer = em.idEmployer')
                        ->join('RocketSellerTwoPickBundle:Person', 'pe', 'WITH', 'em.personPerson = pe.idPerson')
                        ->orderBy('pe.names', $order)
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->addOrderBy('pe.lastName1', $order)
                        ->addOrderBy('pe.lastName2', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'document'://query ordered by document
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->join('RocketSellerTwoPickBundle:Employer', 'em', 'WITH', 'p.employerEmployer = em.idEmployer')
                        ->join('RocketSellerTwoPickBundle:Person', 'pe', 'WITH', 'em.personPerson = pe.idPerson')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('pe.document', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'datein'://query ordered by backoffice date
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->orderBy('p.backOfficeDate', $order)
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'dateout'://query ordered by backoffice finished
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('p.finishedAt', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'none'://default query ordered by procedure id
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('p.idProcedure', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'id'://query ordered by procedure id
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('p.idProcedure', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'type'://query ordered by procedure type
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('p.procedureTypeProcedureType', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
                case 'state'://query ordered by procedure state
                    $query
                        ->add('select', 'p')
                        ->from('RocketSellerTwoPickBundle:RealProcedure', 'p')
                        ->where('p.procedureStatus != ?1')
                        ->andWhere('p.procedureStatus!= ?2')
                        ->andWhere('p.procedureStatus!= ?3')
                        ->orderBy('p.procedureStatus', $order)
                        ->setParameter('1', $this->getStatusByStatusCode('DIS'))
                        ->setParameter('2', $this->getStatusByStatusCode('DCPE'))
                        ->setParameter('3', $this->getStatusByStatusCode('FIN'));
                    break;
            }
            $procedures = $query->getQuery()->getResult();//getting the procedures matching the querybuilder
        }else{//if search parameters
            $docNum = false;//flag for document number search
            $name = false;//flag for name search
            $status = false;//flag for status search
            $pr = false;
            if ($document!= 'none') {//if documents parameter different to none docnum search was done
                $docNum = true;
            }
            if ($names != 'none') {//if names parameter different to none name search was done
                $name = true;
            }
            if ($state != 'none'){//if state parameter different to none status search was done
                $status = true;
            }
            if ($prior != 'none'){//if state parameter different to none status search was done
                $pr = true;
            }

            /** @var QueryBuilder $query2 */
            if($state =='ALL'){
                $query2 = $em->createQueryBuilder();//creating the query based on search parameters
                $query2->select('p');
                $query2->from('RocketSellerTwoPickBundle:RealProcedure','p')->join('p.procedureStatus','es')->join('p.employerEmployer','em')->join('em.personPerson','p2')->join('em.employerHasEmployees','ehe')->join('ehe.employeeEmployee','ee')->join('ee.personPerson','pe');
            }else{
                $query2 = $em->createQueryBuilder();//creating the query based on search parameters
                $query2->select('p');
                $query2->from('RocketSellerTwoPickBundle:RealProcedure','p')->join('p.action','ac')->join('ac.personPerson','pe')->join('p.procedureStatus','es')->join('p.employerEmployer','em')->join('em.personPerson','p2');
            }

            if($docNum){//if docnum parameter searching for employer persons and employees persons matching document number
                $query2
                    ->andWhere($query2->expr()->orX(
                        $query2->expr()->eq('pe.document', '?1'),
                        $query2->expr()->like('pe.document', '?1'),
                        $query2->expr()->eq('p2.document', '?1'),
                        $query2->expr()->like('p2.document', '?1')))
                    ->setParameter('1', '%' . $document . '%');
            }
            if($name){//if name parameter searching for employers persons and employees persons matching person names, lastName1 and lastName2
                $tempStrs = explode(' ',$names);
                $strCount = count($tempStrs);
                if ($strCount>1){//if name parameter has more than 1 word
                    if($strCount==2){//creating query matching two words
                        $query2
                            ->andWhere($query2->expr()->orX(
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?2'),$query2->expr()->like('pe.lastName1', '?3')),
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?2'),$query2->expr()->like('pe.lastName2', '?3')),
                                $query2->expr()->like('pe.names', '?4 '),
                                $query2->expr()->eq('pe.names', '?4 '),
                                $query2->expr()->andX($query2->expr()->like('pe.lastName1', '?2'),$query2->expr()->like('pe.lastName2', '?3')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?2'),$query2->expr()->like('p2.lastName1', '?3')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?2'),$query2->expr()->like('p2.lastName2', '?3')),
                                $query2->expr()->like('p2.names', '?4 '),
                                $query2->expr()->eq('p2.names', '?4 '),
                                $query2->expr()->andX($query2->expr()->like('p2.lastName1', '?2'),$query2->expr()->like('p2.lastName2', '?3'))
                            ))
                            ->setParameter('2', '%' . $tempStrs[0] . '%')
                            ->setParameter('3', '%' . $tempStrs[1] . '%')
                            ->setParameter('4', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%');
                    }elseif($strCount==3){//creating query matching three words
                        $query2
                            ->andWhere($query2->expr()->orX(
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?5'),$query2->expr()->like('pe.lastName1', '?6'),$query2->expr()->like('pe.lastName2', '?7')),
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?8'),$query2->expr()->like('pe.lastName1', '?7')),
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?8'),$query2->expr()->like('pe.lastName2', '?7')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?5'),$query2->expr()->like('p2.lastName1', '?6'),$query2->expr()->like('p2.lastName2', '?7')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?8'),$query2->expr()->like('p2.lastName1', '?7')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?8'),$query2->expr()->like('p2.lastName2', '?7'))
                            ))
                            ->setParameter('5', '%' . $tempStrs[0] . '%')
                            ->setParameter('6', '%' . $tempStrs[1] . '%')
                            ->setParameter('7', '%' . $tempStrs[2] . '%')
                            ->setParameter('8', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%');
                    }elseif($strCount==4){//creating query matching four words
                        $query2
                            ->andWhere($query2->expr()->orX(
                                $query2->expr()->andX($query2->expr()->like('pe.names', '?9'),$query2->expr()->like('pe.lastName1', '?10'),$query2->expr()->like('pe.lastName2', '?11')),
                                $query2->expr()->andX($query2->expr()->eq('pe.names', '?9'),$query2->expr()->eq('pe.lastName1', '?10'),$query2->expr()->eq('pe.lastName2', '?11')),
                                $query2->expr()->andX($query2->expr()->like('p2.names', '?9'),$query2->expr()->like('p2.lastName1', '?10'),$query2->expr()->like('p2.lastName2', '?11')),
                                $query2->expr()->andX($query2->expr()->eq('p2.names', '?9'),$query2->expr()->eq('p2.lastName1', '?10'),$query2->expr()->eq('p2.lastName2', '?11'))
                            ))
                            ->setParameter('9', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%')
                            ->setParameter('10', '%' . $tempStrs[2] . '%')
                            ->setParameter('11', '%' . $tempStrs[3] . '%');
                    }
                }else{//names has only one word
                    $query2
                        ->andWhere($query2->expr()->orX(
                            $query2->expr()->eq('pe.names', '?12'),
                            $query2->expr()->like('pe.names', '?12 '),
                            $query2->expr()->eq('pe.lastName1', '?12'),
                            $query2->expr()->like('pe.lastName1', '?12 '),
                            $query2->expr()->eq('pe.lastName2', '?12'),
                            $query2->expr()->like('pe.lastName2', '?12 '),
                            $query2->expr()->eq('p2.names', '?12'),
                            $query2->expr()->like('p2.names', '?12 '),
                            $query2->expr()->eq('p2.lastName1', '?12'),
                            $query2->expr()->like('p2.lastName1', '?12 '),
                            $query2->expr()->eq('p2.lastName2', '?12'),
                            $query2->expr()->like('p2.lastName2', '?12 ')
                        ))
                        ->setParameter('12', '%' . $names . '%');
                }

            }
            if($status){//if status parameter searching for procedures matching procedure status
                if($state!='ALL'){//if status is all searching all procedures
                    $query2
                        ->andWhere($query2->expr()->eq('p.procedureStatus','?13'))
                        ->setParameter('13',$this->getStatusByStatusCode($state));
                }
            }
            if($pr){
                if(intval($prior)<3){
                    $query2
                        ->andWhere($query2->expr()->eq('p.priority','?14'))
                        ->setParameter('14',intval($prior)-1);
                }else{
                    $query2
                        ->andWhere($query2->expr()->gte('p.priority','?15'))
                        ->setParameter('15',2);
                }
            }
            if(!$name and !$docNum and !$status and !$pr){//if search parameters not found searching for procedures with status different to disabled and docs pending
                $query2
                    ->where('p.procedureStatus != ?14')
                    ->andWhere('p.procedureStatus!= ?15')
                    ->andWhere('p.procedureStatus!= ?16')
                    ->setParameter('14',$this->getStatusByStatusCode('DIS'))
                    ->setParameter('15',$this->getStatusByStatusCode('DCPE'))
                    ->setParameter('16', $this->getStatusByStatusCode('FIN'));
            }
            switch($orderType) {//if order parameter send after search
                case 'name'://adding name ordering
                    $query2
                        ->orderBy('p2.names', $order)
                        ->addOrderBy('p2.lastName1', $order)
                        ->addOrderBy('p2.lastName2', $order)
                        ->addOrderBy('pe.names', $order)
                        ->addOrderBy('pe.lastName1', $order)
                        ->addOrderBy('pe.lastName2', $order);
                    break;
                case 'document'://adding document ordering
                    $query2
                        ->addOrderBy('p2.document', $order)
                        ->addOrderBy('p2.document', $order);
                    break;
                case 'datein'://adding backoffice date ordering
                    $query2
                        ->addOrderBy('p.backOfficeDate', $order);
                    break;
                case 'dateout'://adding backofice finished ordering
                    $query2
                        ->addOrderBy('p.finishedAt', $order);
                    break;
                case 'none'://if no order set default order by procedure id
                    $query2
                        ->addOrderBy('p.idProcedure', $order);
                    break;
                case 'type'://adding order by procedure type
                    $query2
                        ->addOrderBy('p.procedureTypeProcedureType', $order);
                    break;
                case 'state'://adding order by procedure state
                    $query2
                        ->addOrderBy('p.procedureStatus', $order);
                    break;
                case 'id'://adding order by id
                    $query2
                        ->addOrderBy('p.idProcedure', $order);
                    break;
            }
            $procedures = $query2->getQuery()->getResult();
        }

        $form =$this->get('form.factory')->createNamedBuilder('formFilter')
            ->add('documento','text',array('label'=>'Numero de documento:','required'=>false,'attr'=>array('class'=>'documentNumberInput'),'label_attr'=>array('class'=>'documenNumberLabel')))
            ->add('nombre','text',array('label'=>'Nombre(s) o apellido(s) de empleador o empleado:','required'=>false,'attr'=>array('class'=>'nameInput'),'label_attr'=>array('class'=>'nameLabel')))
            ->add('estado','choice', array('label'=>'Estado:','expanded'=>false,'multiple'=>false,'placeholder' => 'Seleccionar estado','required'=>false,
                'choices' => array(
                    'NEW' => 'Nuevo',
                    'STRT' => 'En tramite',
                    'ERRO'=> 'Error',
                    'CORT' => 'Corregido',
                    'FIN' => 'Terminado',
                    'CTPE' => 'Contrato Pendiente',
                    'CTVA' => 'Contrato Validado',
                    'DCPE' => 'Documentos Pendientes',
                    'ALL' => 'Todos',
                )))
            ->add('prioridad','choice', array('label'=>'Prioridad:','expanded'=>false,'multiple'=>false,'placeholder' => 'Prioridad','required'=>false,
                'choices' => array(
                    '1' => 'Baja',
                    '2' => 'Media',
                    '3'=> 'Alta',
                )))
            ->add('buscar', 'submit', array('label' => 'Buscar'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid() and $form->isSubmitted()) {
            $docNum = false;
            $name = false;
            $status = false;
            $pr = false;
            if ($form->get('documento')->getData()) {
                $docNum = true;
            }
            if ($form->get('nombre')->getData()) {
                $name = true;
            }
            if ($form->get('estado')->getData()){
                $status = true;
            }
            if ($form->get('prioridad')->getData()){
                $pr = true;
            }
            /** @var QueryBuilder $query2 */
            if($form->get('estado')->getData() =='ALL'){
                $query2 = $em->createQueryBuilder();//creating the query based on search parameters
                $query2->select('p');
                $query2->from('RocketSellerTwoPickBundle:RealProcedure','p')->join('p.procedureStatus','es')->join('p.employerEmployer','em')->join('em.personPerson','p2')->join('em.employerHasEmployees','ehe')->join('ehe.employeeEmployee','ee')->join('ee.personPerson','pe');
            }else{
                $query2 = $em->createQueryBuilder();//creating the query based on search parameters
                $query2->select('p');
                $query2->from('RocketSellerTwoPickBundle:RealProcedure','p')->join('p.action','ac')->join('ac.personPerson','pe')->join('p.procedureStatus','es')->join('p.employerEmployer','em')->join('em.personPerson','p2');
            }

            try {
                if($docNum){
                    $query2
                        ->andWhere($query2->expr()->orX(
                            $query2->expr()->eq('pe.document', '?1'),
                            $query2->expr()->like('pe.document', '?1'),
                            $query2->expr()->eq('p2.document', '?1'),
                            $query2->expr()->like('p2.document', '?1')))
                        ->setParameter('1', '%' . $form->get('documento')->getData() . '%');
                }
                if($name){
                    $tempStrs = explode(' ',$form->get('nombre')->getData());
                    $strCount = count($tempStrs);
                    if ($strCount>1){
                        if($strCount==2){
                            $query2
                                ->andWhere($query2->expr()->orX(
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?2'),$query2->expr()->like('pe.lastName1', '?3')),
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?2'),$query2->expr()->like('pe.lastName2', '?3')),
                                    $query2->expr()->like('pe.names', '?4 '),
                                    $query2->expr()->eq('pe.names', '?4 '),
                                    $query2->expr()->andX($query2->expr()->like('pe.lastName1', '?2'),$query2->expr()->like('pe.lastName2', '?3')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?2'),$query2->expr()->like('p2.lastName1', '?3')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?2'),$query2->expr()->like('p2.lastName2', '?3')),
                                    $query2->expr()->like('p2.names', '?4 '),
                                    $query2->expr()->eq('p2.names', '?4 '),
                                    $query2->expr()->andX($query2->expr()->like('p2.lastName1', '?2'),$query2->expr()->like('p2.lastName2', '?3'))
                                ))
                                ->setParameter('2', '%' . $tempStrs[0] . '%')
                                ->setParameter('3', '%' . $tempStrs[1] . '%')
                                ->setParameter('4', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%');
                        }elseif($strCount==3){
                            $query2
                                ->andWhere($query2->expr()->orX(
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?5'),$query2->expr()->like('pe.lastName1', '?6'),$query2->expr()->like('pe.lastName2', '?7')),
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?8'),$query2->expr()->like('pe.lastName1', '?7')),
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?8'),$query2->expr()->like('pe.lastName2', '?7')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?5'),$query2->expr()->like('p2.lastName1', '?6'),$query2->expr()->like('p2.lastName2', '?7')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?8'),$query2->expr()->like('p2.lastName1', '?7')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?8'),$query2->expr()->like('p2.lastName2', '?7'))
                                ))
                                ->setParameter('5', '%' . $tempStrs[0] . '%')
                                ->setParameter('6', '%' . $tempStrs[1] . '%')
                                ->setParameter('7', '%' . $tempStrs[2] . '%')
                                ->setParameter('8', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%');
                        }elseif($strCount==4){
                            $query2
                                ->andWhere($query2->expr()->orX(
                                    $query2->expr()->andX($query2->expr()->like('pe.names', '?9'),$query2->expr()->like('pe.lastName1', '?10'),$query2->expr()->like('pe.lastName2', '?11')),
                                    $query2->expr()->andX($query2->expr()->eq('pe.names', '?9'),$query2->expr()->eq('pe.lastName1', '?10'),$query2->expr()->eq('pe.lastName2', '?11')),
                                    $query2->expr()->andX($query2->expr()->like('p2.names', '?9'),$query2->expr()->like('p2.lastName1', '?10'),$query2->expr()->like('p2.lastName2', '?11')),
                                    $query2->expr()->andX($query2->expr()->eq('p2.names', '?9'),$query2->expr()->eq('p2.lastName1', '?10'),$query2->expr()->eq('p2.lastName2', '?11'))
                                ))
                                ->setParameter('9', '%' . $tempStrs[0] . ' ' . $tempStrs[1] . '%')
                                ->setParameter('10', '%' . $tempStrs[2] . '%')
                                ->setParameter('11', '%' . $tempStrs[3] . '%');
                        }
                    }else{
                        $query2
                            ->andWhere($query2->expr()->orX(
                                $query2->expr()->eq('pe.names', '?12'),
                                $query2->expr()->like('pe.names', '?12 '),
                                $query2->expr()->eq('pe.lastName1', '?12'),
                                $query2->expr()->like('pe.lastName1', '?12 '),
                                $query2->expr()->eq('pe.lastName2', '?12'),
                                $query2->expr()->like('pe.lastName2', '?12 '),
                                $query2->expr()->eq('p2.names', '?12'),
                                $query2->expr()->like('p2.names', '?12 '),
                                $query2->expr()->eq('p2.lastName1', '?12'),
                                $query2->expr()->like('p2.lastName1', '?12 '),
                                $query2->expr()->eq('p2.lastName2', '?12'),
                                $query2->expr()->like('p2.lastName2', '?12 ')
                            ))
                            ->setParameter('12', '%' . $form->get('nombre')->getData() . '%');
                    }

                }
                if($status){
                    if($form->get('estado')->getData()!='ALL'){
                        $query2
                            ->andWhere($query2->expr()->eq('p.procedureStatus','?13'))
                            ->setParameter('13',$this->getStatusByStatusCode($form->get('estado')->getData()));
                    }
                }
                if($pr){
                    if($form->get('prioridad')->getData()<3){
                        $query2
                            ->andWhere($query2->expr()->eq('p.priority','?14'))
                            ->setParameter('14',$form->get('prioridad')->getData()-1);
                    }else{
                        $query2
                            ->andWhere($query2->expr()->gte('p.priority','?15'))
                            ->setParameter('15',2);
                    }
                }
                if(!$name and !$docNum and !$status and !$pr){
                    $query2
                        ->where('p.procedureStatus != ?14')
                        ->andWhere('p.procedureStatus!= ?15')
                        ->andWhere('p.procedureStatus!= ?16')
                        ->setParameter('14',$this->getStatusByStatusCode('DIS'))
                        ->setParameter('15',$this->getStatusByStatusCode('DCPE'))
                        ->setParameter('16',$this->getStatusByStatusCode('FIN'));

                    $state = 'none';
                    $document = 'none';
                    $names = 'none';
                    $prior = 'none';
                }
                $query2
                    ->orderBy('p.idProcedure',$order)
                    ->addOrderBy('pe.names', $order)
                    ->addOrderBy('pe.lastName1', $order)
                    ->addOrderBy('pe.lastName2', $order)
                    ->addOrderBy('p.procedureStatus','ASC');
                $procedures = $query2->getQuery()->getResult();
                $change = false;
                $priorityChange = false;
                /** @var RealProcedure $procedure */
                foreach ($procedures as $procedure) {
                    if($procedure->getBackOfficeDate()==null and $procedure->getProcedureStatusCode()=='NEW'){
                        $this->allDocumentsReady($procedure->getUserUser());
                    }
                    if($this->calculateProcedureStatus($procedure)==1){
                        $em->persist($procedure);
                        $change = true;
                    }
                    if($this->calculateProcedurePriority($procedure)==1){
                        $em->persist($procedure);
                        $priorityChange = true;
                    }

                }
                if($change or $priorityChange)$em->flush();

            } catch (Exception $e) {
                //todo error redirect
            }

            return $this->render('@RocketSellerTwoPick/BackOffice/procedures.html.twig',array('procedures'=>$procedures,'order'=>$order,'state'=>$state,'names'=>$names,'document'=>$document,'prior'=>$prior,'form' => $form->createView()));
        }
        $change = false;
        $priorityChange = false;
        foreach ($procedures as $procedure) {
            if($procedure->getBackOfficeDate()==null and $procedure->getProcedureStatusCode()=='NEW'){
                $this->allDocumentsReady($procedure->getUserUser());
            }
            if($this->calculateProcedureStatus($procedure)==1){
                $em->persist($procedure);
                $change = true;
            }
            if($this->calculateProcedurePriority($procedure)==1){
                $em->persist($procedure);
                $priorityChange = true;
            }
        }
        if($change or $priorityChange)$em->flush();
		return $this->render(
            '@RocketSellerTwoPick/BackOffice/procedures.html.twig',array('procedures'=>$procedures,'order'=>$order,'state'=>$state,'names'=>$names,'document'=>$document,'prior'=>$prior,'form' => $form->createView())
        );
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function procedureByIdAction                                  ║
     * ║ Shows all the actions for the realProcedure passed            ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $procedureId                                  ║
     * ║  @param Request $request                                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return Response /backoffice/procedure/{procedureId}         ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
	public function procedureByIdAction($procedureId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $today = new DateTime();
        //getting the realProcedure
        /** @var RealProcedure $procedure */
    	$procedure = $this->loadClassById($procedureId,'RealProcedure');
        $this->calculateProcedureStatus($procedure);
        $em->persist($procedure);
        $em->flush();
        //calculating generalStatus for employer and all employees
        $generalStatus = array();
        $generalStatus['employerInfo']=$this->checkEmployerInfoActions($procedure);
        $generalStatus['employeesInfo']=$this->checkEmployeeInfoActions($procedure);

        $this->calculateProcedurePriority($procedure);

        /** @var Action $action */
        foreach ($procedure->getAction() as $action) {
            $action->setPriority($procedure->getPriority());
            $em->persist($action);
        }
        $em->flush();

        /** @var Employer $employer */
    	$employer = $procedure->getEmployerEmployer();
    	$employerHasEmployees =  $employer->getActiveEmployerHasEmployees();
        $actionTypes = $this->getAllProcedureActionTypes();

        //Settign the employer notifications;
        $employerNotifications = array();
        if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()))){
            $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
        }else{
            $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode($employer->getPersonPerson()->getDocumentType()));
        }
        $employerNotifications['notCC']=$notification;
        if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'))){
            $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
        }else{
            $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('RUT'));
        }
        $employerNotifications['notRUT']=$notification;
        if($this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'))){
            $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
        }else{
            $notification = $this->createNotificationByDocType($employer->getPersonPerson(),$employer->getPersonPerson(),$this->getDocumentTypeByCode('MAND'));
        }
        $employerNotifications['notMAND']=$notification;

        $employeesNotifications = array();
        if($employer->getAllDocsReadyAt()==null){
            $employer->setDocumentStatus($this->getDocumentStatusByCode('ALLDCP'));
            $em->persist($employer);
        }elseif($employer->getInfoValidatedAt()!= null){
            $employer->setDocumentStatus($this->getDocumentStatusByCode('ALDCVA'));
            $employer->setDashboardMessage($today);
            $em->persist($employer);
        }elseif($employer->getInfoErrorAt()!=null){
            $employer->setDocumentStatus($this->getDocumentStatusByCode('ALLDCE'));
            $employer->setDashboardMessage($today);
            $em->persist($employer);
        }else{
            $employer->setDocumentStatus($this->getDocumentStatusByCode('ALDCIV'));
            $employer->setDashboardMessage($today);
            $em->persist($employer);
        }
        $em->flush();
        //Creating form for EmployerInfo
        if($procedure->getActionsByActionType($actionTypes['VER'])->first()->getActionStatusCode()!='FIN'){
            $formDocument = $this->createFormBuilder()
                ->add('document','text',array('label'=>'Numero de documento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('documentType','choice', array('label'=>'Tipo de Documento:','expanded'=>false,'disabled'=>true,'multiple'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>false,
                    'choices' => array(
                        'CC' => 'Cédula de ciudadania',
                        'CE' => 'Cédula de Extranjería',
                        'PASAPORTE' => 'Pasaporte'
                    )))
                ->add('name','text',array('label'=>'Nombre Completo:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('lastName1','text',array('label'=>'Primer Apellido:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('lastName2','text',array('label'=>'Segundo Apellido:','required'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('expeditionDate', 'date', array('label'=>'Fecha de expedición:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                    'placeholder' => array(
                        'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                    ),
                    'years' => range(intval($today->format("Y")),1900)
                ))
                ->add('birthDate', 'date', array('label'=>'Fecha de nacimiento:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                    'placeholder' => array(
                        'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                    ),
                    'years' => range(intval($today->format("Y")),1900)
                ))
                ->add('email','text',array('label'=>'Correo:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('phone','text',array('label'=>'Telefono/Celular:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                ->getForm();
        }else{
            $formDocument = $this->createFormBuilder()
                ->add('document','text',array('label'=>'Numero de documento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('documentType','choice', array('label'=>'Tipo de Documento:','expanded'=>false,'multiple'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>false,
                    'choices' => array(
                        'CC' => 'Cédula de ciudadania',
                        'CE' => 'Cédula de Extranjería',
                        'PAS' => 'Pasaporte'
                    )))
                ->add('name','text',array('label'=>'Nombre Completo:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('lastName1','text',array('label'=>'Primer Apellido:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('lastName2','text',array('label'=>'Segundo Apellido:','required'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('expeditionDate', 'date', array('label'=>'Fecha de expedición:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                    'placeholder' => array(
                        'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                    ),
                    'years' => range(intval($today->format("Y")),1900)
                ))
                ->add('birthDate', 'date', array('label'=>'Fecha de nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                    'placeholder' => array(
                        'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                    ),
                    'years' => range(intval($today->format("Y")),1900)
                ))
                ->add('email','text',array('label'=>'Correo:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('phone','text',array('label'=>'Telefono/Celular:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->getForm();
        }


        $formDocument['document']->setData($employer->getPersonPerson()->getDocument());
        $formDocument['documentType']->setData($employer->getPersonPerson()->getDocumentType());
        $formDocument['name']->setData($employer->getPersonPerson()->getNames());
        $formDocument['lastName1']->setData($employer->getPersonPerson()->getLastName1());
        $formDocument['lastName2']->setData($employer->getPersonPerson()->getLastName2());
        $formDocument['expeditionDate']->setData($employer->getPersonPerson()->getDocumentExpeditionDate());
        $formDocument['birthDate']->setData($employer->getPersonPerson()->getBirthDate());
        $formDocument['email']->setData($procedure->getUserUser()->getEmail());
        $formDocument['phone']->setData($procedure->getUserUser()->getPersonPerson()->getPhones()->first()->getPhoneNumber());

        //Creating form for employer Entities
        $formsEntities = array();
        $formsEntitiesViews = array();
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($employer->getEntities() as $employerHasEntity) {
            $action = $procedure->getActionByEmployerHasEntity($employerHasEntity)->first();
            if($action->getActionStatusCode()!='FIN'){
                if($action->getEmployerEntity()->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=='PARAFISCAL'){
                    /** @var Department $department */
                    $id = $action->getEmployerEntity()->getEntityEntity()->getDepartments()->first()->getIdDepartment();
                    $formEmployerEntities = $this->createFormBuilder()
                        ->add('actionId','text',array('required'=>true,'disabled'=>true,'attr'=>array('style'=>'display:none'),'label_attr'=>array('style'=>'display:none')))
                        ->add('name','entity',array(
                            'class'=>'RocketSeller\TwoPickBundle\Entity\Entity',
                            'query_builder'=>function (EntityRepository $er) use($id){
                                $query = $er->createQueryBuilder('p');
                                return $query
                                    ->join('RocketSellerTwoPickBundle:EntityType', 'et', 'WITH', 'p.entityTypeEntityType = et.idEntityType')
//                                ->join('p.departments','de')
                                    ->where($query->expr()->eq('et.payroll_code','?1'))
//                                ->andWhere($query->expr()->eq('de.idDepartment','?2'))
                                    ->setParameter('1','PARAFISCAL');
//                                ->setParameter('2',$id);
                            },
                            'choice_label'=>'name',
                            'label'=>'Nombre Entidad:','required'=>true,'disabled'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')

                        ))
                        ->add('actionType','choice', array('label'=>'Acción:','expanded'=>false,'multiple'=>false,'disabled'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>true,
                            'choices' => array(
                                0 => 'Validar entidad',
                                1 => 'Inscribir Entidad',
                            )))
                        ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                        ->getForm();
                    $formEmployerEntities['name']->setData($action->getEmployerEntity()->getEntityEntity());
                }else{
                    $type = $action->getEmployerEntity()->getEntityEntity()->getEntityTypeEntityType();
                    $formEmployerEntities = $this->createFormBuilder()
                        ->add('actionId','text',array('required'=>true,'disabled'=>true,'attr'=>array('style'=>'display:none'),'label_attr'=>array('style'=>'display:none')))
                        ->add('name','entity',array(
                            'class'=>'RocketSeller\TwoPickBundle\Entity\Entity',
                            'query_builder'=>function (EntityRepository $er) use($type){
                                $query = $er->createQueryBuilder('p');
                                return $query
                                    ->where($query->expr()->eq('p.entityTypeEntityType','?1'))
                                    ->setParameter('1',$type);
                            },
                            'choice_label'=>'name',
                            'label'=>'Nombre Entidad:','required'=>true,'disabled'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')

                        ))
                        ->add('actionType','choice', array('label'=>'Acción:','expanded'=>false,'multiple'=>false,'disabled'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>true,
                            'choices' => array(
                                0 => 'Validar entidad',
                                1 => 'Inscribir Entidad',
                            )))
                        ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                        ->getForm();
                    $formEmployerEntities['name']->setData($action->getEmployerEntity()->getEntityEntity());
                }
            }else{
                if($action->getEmployerEntity()->getEntityEntity()->getEntityTypeEntityType()->getPayrollCode()=='PARAFISCAL'){
                    /** @var Department $department */
                    $id = $action->getEmployerEntity()->getEntityEntity()->getDepartments()->first()->getIdDepartment();
                    $formEmployerEntities = $this->createFormBuilder()
                        ->add('actionId','text',array('required'=>true,'disabled'=>true,'attr'=>array('style'=>'display:none'),'label_attr'=>array('style'=>'display:none')))
                        ->add('name','entity',array(
                            'class'=>'RocketSeller\TwoPickBundle\Entity\Entity',
                            'query_builder'=>function (EntityRepository $er) use($id){
                                $query = $er->createQueryBuilder('p');
                                return $query
                                    ->join('RocketSellerTwoPickBundle:EntityType', 'et', 'WITH', 'p.entityTypeEntityType = et.idEntityType')
//                                ->join('p.departments','de')
                                    ->where($query->expr()->eq('et.payroll_code','?1'))
//                                ->andWhere($query->expr()->eq('de.idDepartment','?2'))
                                    ->setParameter('1','PARAFISCAL');
//                                ->setParameter('2',$id);
                            },
                            'choice_label'=>'name',
                            'label'=>'Nombre Entidad:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')

                        ))
                        ->add('actionType','choice', array('label'=>'Acción:','expanded'=>false,'multiple'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>true,
                            'choices' => array(
                                0 => 'Validar entidad',
                                1 => 'Inscribir Entidad',
                            )))
                        ->getForm();
                    $formEmployerEntities['name']->setData($action->getEmployerEntity()->getEntityEntity());
                }else{
                    $type = $action->getEmployerEntity()->getEntityEntity()->getEntityTypeEntityType();
                    $formEmployerEntities = $this->createFormBuilder()
                        ->add('actionId','text',array('required'=>true,'disabled'=>true,'attr'=>array('style'=>'display:none'),'label_attr'=>array('style'=>'display:none')))
                        ->add('name','entity',array(
                            'class'=>'RocketSeller\TwoPickBundle\Entity\Entity',
                            'query_builder'=>function (EntityRepository $er) use($type){
                                $query = $er->createQueryBuilder('p');
                                return $query
                                    ->where($query->expr()->eq('p.entityTypeEntityType','?1'))
                                    ->setParameter('1',$type);
                            },
                            'choice_label'=>'name',
                            'label'=>'Nombre Entidad:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')

                        ))
                        ->add('actionType','choice', array('label'=>'Acción:','expanded'=>false,'multiple'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>true,
                            'choices' => array(
                                0 => 'Validar entidad',
                                1 => 'Inscribir Entidad',
                            )))
                        ->getForm();
                    $formEmployerEntities['name']->setData($action->getEmployerEntity()->getEntityEntity());
                }
            }
            $formEmployerEntities['actionId']->setData($action->getIdAction());
            $formEmployerEntities['actionType']->setData($action->getEmployerEntity()->getState());
            $formsEntities[]=$formEmployerEntities;
            $formsEntitiesViews[]=$formEmployerEntities->createView();
        }

        //Creating form for employer workplaces
        $formsWorkPlaces = array();
        $formsWorkPlacesViews = array();
        $formCount=0;
        /** @var Workplace $workplace */
        foreach ($employer->getWorkplaces() as $workplace) {
            $formEmployerWorkPlace= $this->get('form.factory')->createNamedBuilder('formWorkplace'.$formCount)
                ->add('workplaceId','text',array('required'=>true,'disabled'=>true,'attr'=>array('style'=>'display:none'),'label_attr'=>array('style'=>'display:none')))
                ->add('addressName','text',array('label'=>'Nombre:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('mainAddress','text',array('label'=>'Dirección:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
//                ->add('country','text',array('label'=>'Dirección:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                ->add('save', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                ->getForm();
            $formEmployerWorkPlace['workplaceId']->setData($workplace->getIdWorkplace());
            $formEmployerWorkPlace['addressName']->setData($workplace->getName());
            $formEmployerWorkPlace['mainAddress']->setData($workplace->getMainAddress());
            $formsWorkPlaces[] = $formEmployerWorkPlace;
            $formsWorkPlacesViews[] = $formEmployerWorkPlace->createView();
            $formCount++;
        }

        $atLeastOne = false;
        $formsInfoEmployees = array();
        $viewsInfoEmployees = array();
        $formEmployeesWorkplaces = array();
        $viewsEmployeesWorkplaces = array();
        $formEmployeesEntities = array();
        $viewsEmployeesEntities = array();
        $formEmployeesStartDates = array();
        $formEmployeesEndDates = array();
        $viewsEmployeesStartDates = array();
        $viewsEmployeesEndDates = array();
        /** @var EmployerHasEmployee $ehe */
        foreach ($employerHasEmployees as $ehe){
            if($ehe->getExistentSQL()==1){
                $atLeastOne = true;
                $ehe->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                if($ehe->getLegalFF()==null)$ehe->setLegalFF(0);
                if($ehe->getDateDocumentsUploaded()==null)$ehe->setDateDocumentsUploaded($today);
                if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                if($ehe->getAllDocsValidatedMessageAt()==null)$ehe->setAllDocsValidatedMessageAt($today);
                if($ehe->getDateRegisterToSQL()==null)$ehe->setDateRegisterToSQL($today);
                if($ehe->getInfoValidatedAt()==null)$ehe->setInfoValidatedAt($today);
                if($ehe->getDateFinished()==null)$ehe->setDateFinished($today);
                if($ehe->getAllEmployeeDocsReadyAt()==null)$ehe->setAllEmployeeDocsReadyAt($today);
                if($ehe->getState()<4)$ehe->setState(4);
                $em->persist($ehe);
                $em->flush();
            }
            if ($ehe->getDateDocumentsUploaded() == null) {
                if ($employer->getAllDocsReadyAt() == null and $ehe->getAllEmployeeDocsReadyAt() == null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCP'));
                    $em->persist($ehe);
                } elseif ($employer->getAllDocsReadyAt() == null and $ehe->getAllEmployeeDocsReadyAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCPE'));
                    $em->persist($ehe);
                } elseif ($employer->getAllDocsReadyAt() != null and $ehe->getAllEmployeeDocsReadyAt() == null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCPE'));
                    $em->persist($ehe);
                }elseif ($employer->getAllDocsReadyAt() != null and $ehe->getAllEmployeeDocsReadyAt() != null) {
                    if($ehe->getDateDocumentsUploaded()==null)$ehe->setDateDocumentsUploaded($today);
                    $em->persist($ehe);
                    if($ehe->getInfoValidatedAt() != null) {
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCVM'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEVERE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } else {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCVA'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        }
                    }elseif($ehe->getInfoErrorAt() != null) {
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERVEEE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } else {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        }
                    } else {
                        if ($employer->getInfoValidatedAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCVA'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } elseif ($employer->getInfoErrorAt() != null) {
                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCE'));
                            if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                            $em->persist($ehe);
                        } else {

                            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCIV'));
                            $em->persist($ehe);
                        }
                    }
                }
            }elseif($ehe->getInfoValidatedAt() != null) {
                if($ehe->getAllDocsReadyMessageAt()==null){
                    $ehe->setAllDocsReadyMessageAt($today);
                }
                if ($employer->getInfoValidatedAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCVM'));
                    $em->persist($ehe);
                } elseif ($employer->getInfoErrorAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEVERE'));
                    $em->persist($ehe);
                } else {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCVA'));
                    $em->persist($ehe);
                }
            }elseif($ehe->getInfoErrorAt() != null) {
                if($ehe->getAllDocsReadyMessageAt()==null){
                    $ehe->setAllDocsReadyMessageAt($today);
                }
                if ($employer->getInfoValidatedAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERVEEE'));
                    $em->persist($ehe);
                } elseif ($employer->getInfoErrorAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCE'));
                    $em->persist($ehe);
                } else {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('EEDCE'));
                    $em->persist($ehe);
                }
            } else {
                if ($employer->getInfoValidatedAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCVA'));
                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                    $em->persist($ehe);
                } elseif ($employer->getInfoErrorAt() != null) {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ERDCE'));
                    if($ehe->getAllDocsReadyMessageAt()==null)$ehe->setAllDocsReadyMessageAt($today);
                    $em->persist($ehe);
                } else {
                    $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCIV'));
                    $em->persist($ehe);
                }
            }

            $employeesNotifications[$ehe->getIdEmployerHasEmployee()]=array();
            if($this->getNotificationByPersonAndOwnerAndDocumentType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType()))){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType()));
            }else{
                $notification = $this->createNotificationByDocType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType()));
            }
            $employeesNotifications[$ehe->getIdEmployerHasEmployee()]['notCC'] = $notification;
            if($this->getNotificationByPersonAndOwnerAndDocumentType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CAS'))){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CAS'));
            }else{
                $notification = $this->createNotificationByDocType($ehe->getEmployerEmployer()->getPersonPerson(),$ehe->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CAS'));
            }
            $employeesNotifications[$ehe->getIdEmployerHasEmployee()]['notCAS'] = $notification;
            if ($ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first() != null){
                /** @var Action $eeAction */
                $eeAction = $ehe->getEmployeeEmployee()->getPersonPerson()->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first();
                if($eeAction->getActionStatusCode()!= 'FIN' and $eeAction->getRealProcedureRealProcedure() == $procedure){
                    $form = $this->get('form.factory')->createNamedBuilder('formInfoEmployee'.$ehe->getIdEmployerHasEmployee())
                        ->add('document','text',array('label'=>'Numero de documento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('documentType','choice', array('label'=>'Tipo de Documento:','expanded'=>false,'disabled'=>true,'multiple'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>false,
                            'choices' => array(
                                'CC' => 'Cédula de ciudadania',
                                'CE' => 'Cédula de Extranjería',
                                'PAS' => 'Pasaporte'
                            )))
                        ->add('name','text',array('label'=>'Nombre Completo:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('lastName1','text',array('label'=>'Primer Apellido:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('lastName2','text',array('label'=>'Segundo Apellido:','required'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('expeditionDate', 'date', array('label'=>'Fecha de expedición:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y")),1900)
                        ))
                        ->add('documentExpeditionPlace', 'text', array('attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'label' => 'Lugar de expedición:',
                            'required' => true
                        ))
                        ->add('birthDate', 'date', array('label'=>'Fecha de nacimiento:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y")),1900)
                        ))
                        ->add('birthCountry','text',array('label'=>'País de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('birthDepartment','text',array('label'=>'Dpto. de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('birthCity','text',array('label'=>'Ciudad de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('email','text',array('label'=>'Correo:','required'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('phone','text',array('label'=>'Telefono/Celular:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('civilStatus', 'choice', array('label' => 'Estado civil:', 'placeholder' => 'Seleccionar una opción','required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'choices' => array(
                                'soltero'   => 'Soltero(a)',
                                'casado' => 'Casado(a)',
                                'unionLibre' => 'Union Libre',
                                'viudo' => 'Viudo(a)'
                            ),
                            'multiple' => false,
                            'expanded' => false
                        ))

                        ->add('gender', 'choice', array('multiple' => false, 'expanded' => false, 'label' => 'Género:', 'placeholder' => 'Seleccionar una opción', 'required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'choices' => array(
                                'MAS'   => 'Masculino',
                                'FEM' => 'Femenino'
                            ),
                        ))
                        ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                        ->getForm();
                }else{
                    $form = $this->get('form.factory')->createNamedBuilder('formInfoEmployee'.$ehe->getIdEmployerHasEmployee())
                        ->add('document','text',array('label'=>'Numero de documento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('documentType','choice', array('label'=>'Tipo de Documento:','disabled'=>true,'expanded'=>false,'multiple'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>false,
                            'choices' => array(
                                'CC' => 'Cédula de ciudadania',
                                'CE' => 'Cédula de Extranjería',
                                'PAS' => 'Pasaporte'
                            )))
                        ->add('name','text',array('label'=>'Nombre Completo:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('lastName1','text',array('label'=>'Primer Apellido:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('lastName2','text',array('label'=>'Segundo Apellido:','required'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('expeditionDate', 'date', array('label'=>'Fecha de expedición:','disabled'=>true,'required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y")),1900)
                        ))
                        ->add('documentExpeditionPlace', 'text', array('attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'label' => 'Lugar de expedición:',
                            'required' => true,'disabled'=>true,
                        ))
                        ->add('birthDate', 'date', array('label'=>'Fecha de nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y")),1900)
                        ))
                        ->add('birthCountry','text',array('label'=>'País de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('birthDepartment','text',array('label'=>'Dpto. de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('birthCity','text',array('label'=>'Ciudad de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('email','text',array('label'=>'Correo:','required'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('phone','text',array('label'=>'Telefono/Celular:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                        ->add('civilStatus', 'choice', array('label' => 'Estado civil:', 'placeholder' => 'Seleccionar una opción','disabled'=>true,'required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'choices' => array(
                                'soltero'   => 'Soltero(a)',
                                'casado' => 'Casado(a)',
                                'unionLibre' => 'Union Libre',
                                'viudo' => 'Viudo(a)'
                            ),
                            'multiple' => false,
                            'expanded' => false
                        ))
                        ->add('gender', 'choice', array('multiple' => false, 'expanded' => false,'disabled'=>true, 'label' => 'Género:', 'placeholder' => 'Seleccionar una opción', 'required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                            'choices' => array(
                                'MAS'   => 'Masculino',
                                'FEM' => 'Femenino'
                            ),
                        ))
                        ->getForm();
                }
                $form->get('document')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocument());
                $form->get('documentType')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType());
                $form->get('name')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getNames());
                $form->get('lastName1')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                $form->get('lastName2')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getLastName2());
                $form->get('expeditionDate')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentExpeditionDate());
                $form->get('documentExpeditionPlace')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentExpeditionPlace());
                $form->get('birthDate')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthDate());
                $form->get('birthCountry')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthCountry());
                $form->get('birthDepartment')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthDepartment());
                $form->get('birthCity')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthCity());
                $form->get('email')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getEmail());
                $form->get('phone')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getPhones()->first()->getPhoneNumber());
                $form->get('civilStatus')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getCivilStatus());
                $form->get('gender')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getGender());
                $formsInfoEmployees[$ehe->getIdEmployerHasEmployee().''] = $form;
                $viewsInfoEmployees[$ehe->getIdEmployerHasEmployee().''] = $form->createView();
            }else{
                $form = $this->get('form.factory')->createNamedBuilder('formInfoEmployee'.$ehe->getIdEmployerHasEmployee())
                    ->add('document','text',array('label'=>'Numero de documento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('documentType','choice', array('label'=>'Tipo de Documento:','disabled'=>true,'expanded'=>false,'multiple'=>false,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),'placeholder' => 'seleccionar opción','required'=>false,
                        'choices' => array(
                            'CC' => 'Cédula de ciudadania',
                            'CE' => 'Cédula de Extranjería',
                            'PAS' => 'Pasaporte'
                        )))
                    ->add('name','text',array('label'=>'Nombre Completo:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('lastName1','text',array('label'=>'Primer Apellido:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('lastName2','text',array('label'=>'Segundo Apellido:','required'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('expeditionDate', 'date', array('label'=>'Fecha de expedición:','required'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),1900)
                    ))
                    ->add('documentExpeditionPlace', 'text', array('attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                        'label' => 'Lugar de expedición:',
                        'required' => true,'disabled'=>true,
                    ))
                    ->add('birthDate', 'date', array('label'=>'Fecha de nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),1900)
                    ))
                    ->add('birthCountry','text',array('label'=>'País de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('birthDepartment','text',array('label'=>'Dpto. de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('birthCity','text',array('label'=>'Ciudad de Nacimiento:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('email','text',array('label'=>'Correo:','required'=>false,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('phone','text',array('label'=>'Telefono/Celular:','required'=>true,'disabled'=>true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title')))
                    ->add('civilStatus', 'choice', array('label' => 'Estado civil:', 'placeholder' => 'Seleccionar una opción','disabled'=>true,'required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                        'choices' => array(
                            'soltero'   => 'Soltero(a)',
                            'casado' => 'Casado(a)',
                            'unionLibre' => 'Union Libre',
                            'viudo' => 'Viudo(a)'
                        ),
                        'multiple' => false,
                        'expanded' => false
                    ))

                    ->add('gender', 'choice', array('multiple' => false, 'expanded' => false,'disabled'=>true, 'label' => 'Género:', 'placeholder' => 'Seleccionar una opción', 'required' => true,'attr'=>array('class'=>'value-content'),'label_attr'=>array('class'=>'value-title'),
                        'choices' => array(
                            'MAS'   => 'Masculino',
                            'FEM' => 'Femenino'
                        ),
                    ))
                    ->getForm();
                $form->get('document')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocument());
                $form->get('documentType')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentType());
                $form->get('name')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getNames());
                $form->get('lastName1')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getLastName1());
                $form->get('lastName2')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getLastName2());
                $form->get('expeditionDate')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentExpeditionDate());
                $form->get('documentExpeditionPlace')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getDocumentExpeditionPlace());
                $form->get('birthDate')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthDate());
                $form->get('birthCountry')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthCountry());
                $form->get('birthDepartment')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthDepartment());
                $form->get('birthCity')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getBirthCity());
                $form->get('email')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getEmail());
                $form->get('phone')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getPhones()->first()->getPhoneNumber());
                $form->get('civilStatus')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getCivilStatus());
                $form->get('gender')->setData($ehe->getEmployeeEmployee()->getPersonPerson()->getGender());
                $formsInfoEmployees[$ehe->getIdEmployerHasEmployee().''] = $form;
                $viewsInfoEmployees[$ehe->getIdEmployerHasEmployee().''] = $form->createView();
            }
            if($ehe->getExistentSQL()!=1){
                $formEW = $this->get('form.factory')->createNamedBuilder('formWorkplaceEmployee'.$ehe->getIdEmployerHasEmployee())
                    ->add('workplace', 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:Workplace',
                        'choices' => $employer->getWorkplaces(),
                        'property' => 'name',
                        'multiple' => false,
                        'expanded' => false,
                        'property_path' => 'workplaceWorkplace',
                        'label'=>'Lugar de Trabajo',
                        'placeholder' => 'Seleccionar una opción',
                        'required' => true
                    ))
                    ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                    ->getForm();
                $formSD = $this->get('form.factory')->createNamedBuilder('formEmployeeStartDate'.$ehe->getIdEmployerHasEmployee())
                    ->add('startDate', 'date', array('label'=>'Fecha de inicio del contrato:','required'=>true,'disabled'=>false,
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y"))+2,intval($ehe->getActiveContract()->getStartDate()->format("Y")-2))
                    ))
                    ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                    ->getForm();
                if($ehe->getActiveContract()->getEndDate()!= null){
                    $formED = $this->get('form.factory')->createNamedBuilder('formEmployeeEndDate'.$ehe->getIdEmployerHasEmployee())
                        ->add('endDate', 'date', array('label'=>'Fecha de fin del contrato:','required'=>true,'disabled'=>false,
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y"))+3,intval($ehe->getActiveContract()->getEndDate()->format("Y")))
                        ))
                        ->add('edit', 'submit', array('label' => 'Guardar','attr'=>array('class'=>'form-button')))
                        ->getForm();
                }
            }else{
                $formEW = $this->get('form.factory')->createNamedBuilder('formWorkplaceEmployee'.$ehe->getIdEmployerHasEmployee())
                    ->add('workplace', 'entity', array(
                        'class' => 'RocketSellerTwoPickBundle:Workplace',
                        'disabled'=>true,
                        'choices' => $employer->getWorkplaces(),
                        'property' => 'name',
                        'multiple' => false,
                        'expanded' => false,
                        'property_path' => 'workplaceWorkplace',
                        'label'=>'Lugar de Trabajo',
                        'placeholder' => 'Seleccionar una opción',
                        'required' => true
                    ))
                    ->getForm();
                $formSD = $this->get('form.factory')->createNamedBuilder('formEmployeeStartDate'.$ehe->getIdEmployerHasEmployee())
                    ->add('startDate', 'date', array('label'=>'Fecha de inicio del contrato:','required'=>true,'disabled'=>true,
                        'placeholder' => array(
                            'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                        ),
                        'years' => range(intval($today->format("Y")),intval($ehe->getActiveContract()->getStartDate()->format("Y"))+1)
                    ))
                    ->getForm();
                if($ehe->getActiveContract()->getEndDate()!= null){
                    $formED = $this->get('form.factory')->createNamedBuilder('formEmployeeEndDate'.$ehe->getIdEmployerHasEmployee())
                        ->add('endDate', 'date', array('label'=>'Fecha de fin del contrato:','required'=>true,'disabled'=>false,
                            'placeholder' => array(
                                'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                            ),
                            'years' => range(intval($today->format("Y"))+3,intval($ehe->getActiveContract()->getEndDate()->format("Y")))
                        ))
                        ->getForm();
                }
            }

            $formEW->get('workplace')->setData($ehe->getActiveContract()->getWorkplaceWorkplace());
            $formEmployeesWorkplaces[$ehe->getIdEmployerHasEmployee().''] = $formEW;
            $viewsEmployeesWorkplaces[$ehe->getIdEmployerHasEmployee().''] = $formEW->createView();
            $formSD->get('startDate')->setData($ehe->getActiveContract()->getStartDate());
            $formEmployeesStartDates[$ehe->getIdEmployerHasEmployee().''] = $formSD;
            $viewsEmployeesStartDates[$ehe->getIdEmployerHasEmployee().''] = $formSD->createView();
            if($ehe->getActiveContract()->getEndDate()!= null){
                $formED->get('endDate')->setData($ehe->getActiveContract()->getEndDate());
                $formEmployeesEndDates[$ehe->getIdEmployerHasEmployee().''] = $formED;
                $viewsEmployeesEndDates[$ehe->getIdEmployerHasEmployee().''] = $formED->createView();
            }

            /** @var EmployeeHasEntity $employeeHasEntity */
            foreach ($ehe->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
                if($employeeHasEntity->getState()!=-1){
                    $action = $ehe->getEmployeeEmployee()->getPersonPerson()->getActionByEmployeeHasEntity($employeeHasEntity)->first();
                    if ($action->getActionStatusCode() != 'FIN') {
                        $type = $action->getEmployeeEntity()->getEntityEntity()->getEntityTypeEntityType();
                        $formEmployeeEntity = $this->get('form.factory')->createNamedBuilder('formEmployeeEntity' . $type . $ehe->getIdEmployerHasEmployee())
                            ->add('actionId', 'text', array('required' => true, 'disabled' => true, 'attr' => array('style' => 'display:none'), 'label_attr' => array('style' => 'display:none')))
                            ->add('name', 'entity', array(
                                'class' => 'RocketSeller\TwoPickBundle\Entity\Entity',
                                'query_builder' => function (EntityRepository $er) use ($type) {
                                    $query = $er->createQueryBuilder('p');
                                    return $query
                                        ->where($query->expr()->eq('p.entityTypeEntityType', '?1'))
                                        ->setParameter('1', $type);
                                },
                                'choice_label' => 'name',
                                'label' => 'Nombre Entidad:', 'required' => true, 'disabled' => false, 'attr' => array('class' => 'value-content'), 'label_attr' => array('class' => 'value-title')

                            ))
                            ->add('actionType', 'choice', array('label' => 'Acción:', 'expanded' => false, 'multiple' => false, 'disabled' => false, 'attr' => array('class' => 'value-content'), 'label_attr' => array('class' => 'value-title'), 'placeholder' => 'seleccionar opción', 'required' => true,
                                'choices' => array(
                                    0 => 'Validar entidad',
                                    1 => 'Inscribir Entidad',
                                )))
                            ->add('edit', 'submit', array('label' => 'Editar', 'attr' => array('class' => 'form-button')))
                            ->getForm();
                        $formEmployeeEntity['name']->setData($action->getEmployeeEntity()->getEntityEntity());
                    } else {
                        $type = $action->getEmployeeEntity()->getEntityEntity()->getEntityTypeEntityType();
                        $formEmployeeEntity = $this->get('form.factory')->createNamedBuilder('formEmployeeEntity' . $type . $ehe->getIdEmployerHasEmployee())
                            ->add('actionId', 'text', array('required' => true, 'disabled' => true, 'attr' => array('style' => 'display:none'), 'label_attr' => array('style' => 'display:none')))
                            ->add('name', 'entity', array(
                                'class' => 'RocketSeller\TwoPickBundle\Entity\Entity',
                                'query_builder' => function (EntityRepository $er) use ($type) {
                                    $query = $er->createQueryBuilder('p');
                                    return $query
                                        ->where($query->expr()->eq('p.entityTypeEntityType', '?1'))
                                        ->setParameter('1', $type);
                                },
                                'choice_label' => 'name',
                                'label' => 'Nombre Entidad:', 'required' => true, 'disabled' => true, 'attr' => array('class' => 'value-content'), 'label_attr' => array('class' => 'value-title')

                            ))
                            ->add('actionType', 'choice', array('label' => 'Acción:', 'expanded' => false, 'multiple' => false, 'disabled' => true, 'attr' => array('class' => 'value-content'), 'label_attr' => array('class' => 'value-title'), 'placeholder' => 'seleccionar opción', 'required' => true,
                                'choices' => array(
                                    0 => 'Validar entidad',
                                    1 => 'Inscribir Entidad',
                                )))
                            ->getForm();
                        $formEmployeeEntity['name']->setData($action->getEmployeeEntity()->getEntityEntity());
                    }
                    $formEmployeeEntity['actionId']->setData($action->getIdAction());
                    $formEmployeeEntity['actionType']->setData($action->getEmployeeEntity()->getState());
                    $formEmployeesEntities[$ehe->getIdEmployerHasEmployee() . ''][] = $formEmployeeEntity;
                    $viewsEmployeesEntities[$ehe->getIdEmployerHasEmployee() . ''][] = $formEmployeeEntity->createView();
                }
            }
        }

        if($atLeastOne){
            $employer->setDocumentStatus($this->getDocumentStatusByCode('BOFFFF'));
            $em->persist($employer);
            $em->flush();
        }
        $formDocument->handleRequest($request);

        if ($formDocument->isValid() and $formDocument->isSubmitted()) {
            $ePerson = $employer->getPersonPerson();
            if($ePerson->getDocumentType()!= $formDocument->get("documentType")->getData()){
                $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($ePerson,$ePerson,$this->getDocumentTypeByCode($ePerson->getDocumentType()));
                $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$ePerson->getIdPerson(),'docCode'=>$ePerson->getDocumentType()));
                $notification->setRelatedLink($url);
                $log = new Log($this->getUser(),"Person","DocumentType",$ePerson->getIdPerson(),$ePerson->getDocumentType(),$formDocument->get("documentType")->getData(),"backoffice cambio el typo de documento de una persona");
                $employer->getPersonPerson()->setDocumentType($formDocument->get("documentType")->getData());
                $em->persist($log);
                $em->persist($notification);
            }
            if($ePerson->getNames()!= $formDocument->get("name")->getData()){
                $log = new Log($this->getUser(),"Person","Names",$ePerson->getIdPerson(),$ePerson->getNames(),$formDocument->get("name")->getData(),"backoffice cambió el nombre de una persona");
                $employer->getPersonPerson()->setNames($formDocument->get("name")->getData());
                $em->persist($log);
            }
            if($ePerson->getLastName1()!= $formDocument->get("lastName1")->getData()){
                $log = new Log($this->getUser(),"Person","LastName1",$ePerson->getIdPerson(),$ePerson->getLastName1(),$formDocument->get("lastName1")->getData(),"backoffice cambió el primer apellido de una persona");
                $employer->getPersonPerson()->setLastName1($formDocument->get("lastName1")->getData());
                $em->persist($log);
            }
            if($ePerson->getLastName2()!= $formDocument->get("lastName2")->getData()){
                $log = new Log($this->getUser(),"Person","LastName2",$ePerson->getIdPerson(),$ePerson->getLastName2(),$formDocument->get("lastName2")->getData(),"backoffice cambió el segundo apellido de una persona");
                $employer->getPersonPerson()->setLastName2($formDocument->get("lastName2")->getData());
                $em->persist($log);
            }
            if($ePerson->getDocumentExpeditionDate()!= $formDocument->get("expeditionDate")->getData()){
                $log = new Log($this->getUser(),"Person","DocumentExpeditionDate",$ePerson->getIdPerson(),$ePerson->getDocumentExpeditionDate()->format("Y-m-d H:i:s"),$formDocument->get("expeditionDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambió la fecha de expedición del documento de una persona");
                $employer->getPersonPerson()->setDocumentExpeditionDate($formDocument->get("expeditionDate")->getData());
                $em->persist($log);
            }
            if($ePerson->getBirthDate()!= $formDocument->get("birthDate")->getData()){
                $log = new Log($this->getUser(),"Person","BirthDate",$ePerson->getIdPerson(),$ePerson->getBirthDate()->format("Y-m-d H:i:s"),$formDocument->get("birthDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambió la fecha de nacimiento de una persona");
                $employer->getPersonPerson()->setBirthDate($formDocument->get("birthDate")->getData());
                $em->persist($log);
            }
            $em->persist($ePerson);
            $em->flush();
            return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',array(
                'procedure'=>$procedure,
                'employerHasEmployees'=>$employerHasEmployees,
                'employer'=>$employer,
                'actionTypes'=>$actionTypes,
                'formDocument' => $formDocument->createView(),
                'employerNotifications'=>$employerNotifications,
                'employeesNotifications'=>$employeesNotifications,
                'formEmployerEntities'=>$formsEntitiesViews,
                'formEmployerWorkplaces'=>$formsWorkPlacesViews,
                'formsInfoEmployees'=>$viewsInfoEmployees,
                'generalStatus'=>$generalStatus,
                'formEmployeesWorkplaces'=>$viewsEmployeesWorkplaces,
                'formEmployeesEntities'=>$viewsEmployeesEntities,
                'formEmployeesStartDates'=>$viewsEmployeesStartDates,
                'formEmployeesEndDates'=>$viewsEmployeesEndDates,
                'atLeastOne'=>$atLeastOne,
            ));
        }

        $formCount = 0;
        foreach ($formsEntities as $formsEntity) {
            $formsEntity->handleRequest($request);
            $formsEntitiesViews[$formCount] = $formsEntity->createView();
            $formCount++;
            if ($formsEntity->isValid() and $formsEntity->isSubmitted()) {
                /** @var Action $tempAction */
                $tempAction = $procedure->getActionById($formsEntity->get('actionId')->getData());
                /** @var RealProcedure $tempProcedure */
                $tempProcedure = $employer->getRealProcedureByProcedureTypeType($this->getProcedureTypeByCode('VAC'))->first();
                if($tempProcedure->getActionByEmployerHasEntity($tempAction->getEmployerEntity())->first()){
                    $action = $tempProcedure->getActionByEmployerHasEntity($tempAction->getEmployerEntity())->first();
                    if($action->getEmployerEntity()->getState()!= $formsEntity->get('actionType')->getData()){
                        if($formsEntity->get('actionType')->getData()==1){
                            $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                            $em->persist($action);
                        }else{
                            $action->setActionStatus($this->getStatusByStatusCode('DIS'));
                            $em->persist($action);
                        }
                    }
                }else{
                    $action = new Action();
                    $tempProcedure->addAction($action);//adding the action to the procedure
                    $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                    $tempProcedure->getUserUser()->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                    $action->setEmployerEntity($tempAction->getEmployerEntity());
                    if($formsEntity->get('actionType')->getData()==1){
                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to disable
                    }
                    $action->setUpdatedAt();//setting the action updatedAt Date
                    $action->setCreatedAt($today);//setting the Action createrAt Date
                    $em->persist($action);
                }
                if($tempAction->getEmployerEntity()->getState()!=$formsEntity->get('actionType')->getData()){
                    $log = new Log($this->getUser(),'EmployerHasEntity','State',$tempAction->getEmployerEntity()->getIdEmployerHasEntity(),$tempAction->getEmployerEntity()->getState(),$formsEntity->get('actionType')->getData(),'backoffice cambió la acción de la entidad del empleador');
                    $tempAction->getEmployerEntity()->setState($formsEntity->get('actionType')->getData());
                    $em->persist($log);
                }
                if($tempAction->getEmployerEntity()->getEntityEntity() != $formsEntity->get('name')->getData()){
                    $log = new Log($this->getUser(),'EmployerHasEntity','EntityEntity',$tempAction->getEmployerEntity()->getIdEmployerHasEntity(),$tempAction->getEmployerEntity()->getEntityEntity()->getIdEntity(),$formsEntity->get('name')->getData()->getIdEntity(),'backoffice cambió una entidad del empleador');
                    $tempAction->getEmployerEntity()->setEntityEntity($formsEntity->get('name')->getData());
                    $em->persist($log);
                }
                $em->persist($tempAction);
                $em->flush();
            }
        }


        $formWorkPlacesCount = 0;
        /** @var Form $formWorkPlace */
        foreach ($formsWorkPlaces as $formWorkPlace) {
            $formWorkPlace->handleRequest($request);
            $formsWorkPlacesViews[$formWorkPlacesCount] = $formWorkPlace->createView();
            $formWorkPlacesCount++;
            $id = $formWorkPlace->get('workplaceId')->getData();
            if($formWorkPlace->isValid() and $formWorkPlace->isSubmitted()){
                /** @var Workplace $tempWorkplace */
                $tempWorkplace = $employer->getWorkplaceById($id);
                if($tempWorkplace->getName()!=$formWorkPlace->get('addressName')->getData()){
                    $log = new Log($this->getUser(),'Workplace','name',$tempWorkplace->getIdWorkplace(),$tempWorkplace->getName(),$formWorkPlace->get('addressName')->getData(),'backoffice cambió el nombre de un lugar de trabajo');
                    $tempWorkplace->setName($formWorkPlace->get('addressName')->getData());
                    $em->persist($log);
                }
                if($tempWorkplace->getMainAddress()!=$formWorkPlace->get('mainAddress')->getData()){
                    $log = new Log($this->getUser(),'Workplace','mainAddress',$tempWorkplace->getIdWorkplace(),$tempWorkplace->getMainAddress(),$formWorkPlace->get('mainAddress')->getData(),'backoffice cambió la dirección de un lugar de trabajo');
                    $tempWorkplace->setMainAddress($formWorkPlace->get('mainAddress')->getData());
                    $em->persist($log);
                }
                $em->persist($tempWorkplace);
                $em->flush();
            }
        }

        foreach ($employerHasEmployees as $ehe) {
            $eeForm = $formsInfoEmployees[$ehe->getIdEmployerHasEmployee()];
            $eeForm->handleRequest($request);

            if($eeForm->isValid() and $eeForm->isSubmitted()){
                /** @var Person $eePerson */
                $eePerson = $ehe->getEmployeeEmployee()->getPersonPerson();
                if($eePerson->getDocument()!= $eeForm->get("document")->getData()){
                    $log = new Log($this->getUser(),"Person","document",$eePerson->getIdPerson(),$eePerson->getDocument(),$eeForm->get("document")->getData(),"backoffice cambio el documento de un empleado");
                    $eePerson->setDocument($eeForm->get("document")->getData());
                    $em->persist($log);
                }
                if($eePerson->getDocumentType()!= $eeForm->get("documentType")->getData()){
                    $notification = $this->getNotificationByPersonAndOwnerAndDocumentType($ehe->getEmployerEmployer()->getPersonPerson(),$eePerson,$this->getDocumentTypeByCode($eePerson->getDocumentType()));
                    $url = $this->generateUrl("documentos_employee", array('entityType'=>'Person','entityId'=>$eePerson->getIdPerson(),'docCode'=>$eePerson->getDocumentType()));
                    $notification->setRelatedLink($url);
                    $log = new Log($this->getUser(),"Person","documentType",$eePerson->getIdPerson(),$eePerson->getDocumentType(),$eeForm->get("documentType")->getData(),"backoffice cambio el tipo de documento de un empleado");
                    $eePerson->setDocumentType($eeForm->get("documentType")->getData());
                    $em->persist($log);
                    $em->persist($notification);
                }
                if($eePerson->getNames()!= $eeForm->get("name")->getData()){
                    $log = new Log($this->getUser(),"Person","names",$eePerson->getIdPerson(),$eePerson->getNames(),$eeForm->get("name")->getData(),"backoffice cambio el nombre de un empleado");
                    $eePerson->setNames($eeForm->get("name")->getData());
                    $em->persist($log);
                }
                if($eePerson->getLastName1()!= $eeForm->get("lastName1")->getData()){
                    $log = new Log($this->getUser(),"Person","lastName1",$eePerson->getIdPerson(),$eePerson->getLastName1(),$eeForm->get("lastName1")->getData(),"backoffice cambio el primer apellido de un empleado");
                    $eePerson->setLastName1($eeForm->get("lastName1")->getData());
                    $em->persist($log);
                }
                if($eePerson->getlastName2()!= $eeForm->get("lastName2")->getData()){
                    $log = new Log($this->getUser(),"Person","lastName2",$eePerson->getIdPerson(),$eePerson->getlastName2(),$eeForm->get("lastName2")->getData(),"backoffice cambio el segundo apellido de un empleado");
                    $eePerson->setLastName2($eeForm->get("lastName2")->getData());
                    $em->persist($log);
                }
                if($eePerson->getDocumentExpeditionDate()!= $eeForm->get("expeditionDate")->getData()){
                    $log = new Log($this->getUser(),"Person","DocumentExpeditionDate",$eePerson->getIdPerson(),$eePerson->getDocumentExpeditionDate()->format("Y-m-d H:i:s"),$eeForm->get("expeditionDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambio la fecha de expedición del documento de un empleado");
                    $eePerson->setDocumentExpeditionDate($eeForm->get("expeditionDate")->getData());
                    $em->persist($log);
                }
                if($eePerson->getDocumentExpeditionPlace()!= $eeForm->get("documentExpeditionPlace")->getData()){
                    $log = new Log($this->getUser(),"Person","documentExpeditionPlace",$eePerson->getIdPerson(),$eePerson->getDocumentExpeditionPlace(),$eeForm->get("documentExpeditionPlace")->getData(),"backoffice cambio el lugar de expedición del documento de un empleado");
                    $eePerson->setDocumentExpeditionPlace($eeForm->get("documentExpeditionPlace")->getData());
                    $em->persist($log);
                }
                if($eePerson->getBirthDate()!= $eeForm->get("birthDate")->getData()){
                    $log = new Log($this->getUser(),"Person","birthDate",$eePerson->getIdPerson(),$eePerson->getBirthDate()->format("Y-m-d H:i:s"),$eeForm->get("birthDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambio la fecha de nacimiento de un empleado");
                    $eePerson->setBirthDate($eeForm->get("birthDate")->getData());
                    $em->persist($log);
                }
                if($eePerson->getGender()!= $eeForm->get("gender")->getData()){
                    $log = new Log($this->getUser(),"Person","gender",$eePerson->getIdPerson(),$eePerson->getGender(),$eeForm->get("gender")->getData(),"backoffice cambio el genero de un empleado");
                    $eePerson->setGender($eeForm->get("gender")->getData());
                    $em->persist($log);
                }
                if($eePerson->getCivilStatus()!= $eeForm->get("civilStatus")->getData()){
                    $log = new Log($this->getUser(),"Person","civilStatus",$eePerson->getIdPerson(),$eePerson->getCivilStatus(),$eeForm->get("civilStatus")->getData(),"backoffice cambio el estado civil de un empleado");
                    $eePerson->setCivilStatus($eeForm->get("civilStatus")->getData());
                    $em->persist($log);
                }
                if($eePerson->getEmail()!= $eeForm->get("email")->getData()){
                    $log = new Log($this->getUser(),"Person","email",$eePerson->getIdPerson(),$eePerson->getEmail(),$eeForm->get("email")->getData(),"backoffice cambio el email civil de un empleado");
                    $eePerson->setEmail($eeForm->get("email")->getData());
                    $em->persist($log);
                }
                if($eePerson->getPhones()->first()->getPhoneNumber()!= $eeForm->get("phone")->getData()){
                    $log = new Log($this->getUser(),"Phone","phoneNumber",$eePerson->getPhones()->first()->getIdPhone(),$eePerson->getPhones()->first()->getPhoneNumber(),$eeForm->get("phone")->getData(),"backoffice cambio el celular de un empleado");
                    $phone = $em->getRepository("RocketSellerTwoPickBundle:Phone")->find($eePerson->getPhones()->first()->getIdPhone());
                    $phone->setPhoneNumber($eeForm->get("phone")->getData());
                    $em->persist($phone);
                    $em->persist($log);
                }
                $em->persist($eePerson);
                $em->flush();
                $viewsInfoEmployees[$ehe->getIdEmployerHasEmployee()]=$eeForm->createView();
                return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',array(
                    'procedure'=>$procedure,
                    'employerHasEmployees'=>$employerHasEmployees,
                    'employer'=>$employer,
                    'actionTypes'=>$actionTypes,
                    'formDocument' => $formDocument->createView(),
                    'employerNotifications'=>$employerNotifications,
                    'employeesNotifications'=>$employeesNotifications,
                    'formEmployerEntities'=>$formsEntitiesViews,
                    'formEmployerWorkplaces'=>$formsWorkPlacesViews,
                    'formsInfoEmployees'=>$viewsInfoEmployees,
                    'generalStatus'=>$generalStatus,
                    'formEmployeesWorkplaces'=>$viewsEmployeesWorkplaces,
                    'formEmployeesEntities'=>$viewsEmployeesEntities,
                    'formEmployeesStartDates'=>$viewsEmployeesStartDates,
                    'formEmployeesEndDates'=>$viewsEmployeesEndDates,
                    'atLeastOne'=>$atLeastOne,
                ));
            }

            $esdForm = $formEmployeesStartDates[$ehe->getIdEmployerHasEmployee()];
            $esdForm->handleRequest($request);
            if($esdForm->isValid() and $esdForm->isSubmitted()){
                if($ehe->getActiveContract()->getStartDate()!= $esdForm->get("startDate")->getData()){
                    $log = new Log($this->getUser(),"Contract","StartDate",$ehe->getActiveContract()->getIdContract(),$ehe->getActiveContract()->getStartDate()->format("Y-m-d H:i:s"),$esdForm->get("startDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambio la fecha de inicio de un contrato");
                    $ehe->getActiveContract()->setStartDate($esdForm->get("startDate")->getData());
                    $em->persist($log);
                }
                $em->persist($ehe);
                $em->flush();
                $viewsEmployeesStartDates[$ehe->getIdEmployerHasEmployee()]=$esdForm->createView();
            }

            if($ehe->getActiveContract()->getEndDate()!= null){
                $eedForm = $formEmployeesEndDates[$ehe->getIdEmployerHasEmployee()];
                $eedForm->handleRequest($request);
                if($eedForm->isValid() and $eedForm->isSubmitted()){
                    if($ehe->getActiveContract()->getEndDate()!= $eedForm->get("endDate")->getData()){
                        $log = new Log($this->getUser(),"Contract","EndDate",$ehe->getActiveContract()->getIdContract(),$ehe->getActiveContract()->getEndDate()->format("Y-m-d H:i:s"),$eedForm->get("endDate")->getData()->format("Y-m-d H:i:s"),"backoffice cambio la fecha de fin de un contrato");
                        $ehe->getActiveContract()->setEndDate($eedForm->get("endDate")->getData());
                        $em->persist($log);
                    }
                    $em->persist($ehe);
                    $em->flush();
                    $viewsEmployeesEndDates[$ehe->getIdEmployerHasEmployee()]=$eedForm->createView();
                }
            }

            $eewForm = $formEmployeesWorkplaces[$ehe->getIdEmployerHasEmployee()];
            $eewForm->handleRequest($request);
            if($eewForm->isValid() and $eewForm->isSubmitted()){
                if($ehe->getActiveContract()->getWorkplaceWorkplace()!= $eewForm->get("workplace")->getData()){
                    $log = new Log($this->getUser(),"Contract","WorkplaceWorkplace",$ehe->getActiveContract()->getIdContract(),$ehe->getActiveContract()->getWorkplaceWorkplace()->getIdWorkplace(),$eewForm->get("workplace")->getData()->getIdWorkplace(),"backoffice cambio el lugar de trabajo de un empleado");
                    $ehe->getActiveContract()->setWorkplaceWorkplace($eewForm->get('workplace')->getData());
                    $em->persist($log);
                }
                $em->persist($ehe);
                $em->flush();
                $viewsEmployeesWorkplaces[$ehe->getIdEmployerHasEmployee()]=$eewForm->createView();
                return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',array(
                    'procedure'=>$procedure,
                    'employerHasEmployees'=>$employerHasEmployees,
                    'employer'=>$employer,
                    'actionTypes'=>$actionTypes,
                    'formDocument' => $formDocument->createView(),
                    'employerNotifications'=>$employerNotifications,
                    'employeesNotifications'=>$employeesNotifications,
                    'formEmployerEntities'=>$formsEntitiesViews,
                    'formEmployerWorkplaces'=>$formsWorkPlacesViews,
                    'formsInfoEmployees'=>$viewsInfoEmployees,
                    'generalStatus'=>$generalStatus,
                    'formEmployeesWorkplaces'=>$viewsEmployeesWorkplaces,
                    'formEmployeesEntities'=>$viewsEmployeesEntities,
                    'formEmployeesStartDates'=>$viewsEmployeesStartDates,
                    'formEmployeesEndDates'=>$viewsEmployeesEndDates,
                    'atLeastOne'=>$atLeastOne,
                ));
            }

            $formEmployeeCount = 0;
            if(count($ehe->getEmployeeEmployee()->getEntities())>0){
                foreach ($formEmployeesEntities[$ehe->getIdEmployerHasEmployee()] as $formEntity) {
                    $formEntity->handleRequest($request);
                    $viewsEmployeesEntities[$ehe->getIdEmployerHasEmployee()][$formEmployeeCount] = $formEntity->createView();
                    $formEmployeeCount++;
                    if ($formEntity->isValid() and $formEntity->isSubmitted()) {
                        /** @var Action $tempAction */
                        $tempAction = $ehe->getEmployeeEmployee()->getPersonPerson()->getActionById($formEntity->get('actionId')->getData());
                        /** @var RealProcedure $vacProcedure */
                        $vacProcedure = $tempAction->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
                        if($vacProcedure->getActionByEmployeeHasEntity($tempAction->getEmployeeEntity())->first()){
                            $action = $vacProcedure->getActionByEmployeeHasEntity($tempAction->getEmployeeEntity())->first();
                            if($action->getEmployeeEntity()->getState()!= $formEntity->get('actionType')->getData()){
                                if($formEntity->get('actionType')->getData()==1){
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                    $em->persist($action);
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DIS'));
                                    $em->persist($action);
                                }
                            }
                        }else{
                            $action = new Action();
                            $vacProcedure->addAction($action);//adding the action to the procedure
                            $ehe->getEmployeeEmployee()->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                            $vacProcedure->getUserUser()->addAction($action);//adding the action to the user
                            $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                            $action->setEmployeeEntity($tempAction->getEmployeeEntity());
                            if($formEntity->get('actionType')->getData()==1){
                                $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                            }else{
                                $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                            }
                            $action->setUpdatedAt();//setting the action updatedAt Date
                            $action->setCreatedAt($today);//setting the Action createrAt Date
                            $em->persist($action);

                        }
                        if($tempAction->getEmployeeEntity()->getState()!=$formEntity->get('actionType')->getData()){
                            $log = new Log($this->getUser(),'EmployeeHasEntity','State',$tempAction->getEmployeeEntity()->getIdEmployeeHasEntity(),$tempAction->getEmployeeEntity()->getState(),$formEntity->get('actionType')->getData(),'backoffice cambió la acción de la entidad del empleado');
                            $tempAction->getEmployeeEntity()->setState($formEntity->get('actionType')->getData());
                            $em->persist($log);
                        }
                        if($tempAction->getEmployeeEntity()->getEntityEntity() != $formEntity->get('name')->getData()){
                            $log = new Log($this->getUser(),'EmployeeHasEntity','EntityEntity',$tempAction->getEmployeeEntity()->getIdEmployeeHasEntity(),$tempAction->getEmployeeEntity()->getEntityEntity()->getIdEntity(),$formEntity->get('name')->getData()->getIdEntity(),'backoffice cambió una entidad del empleado');
                            $tempAction->getEmployeeEntity()->setEntityEntity($formEntity->get('name')->getData());
                            $em->persist($log);
                        }
                        $em->persist($tempAction);
                        $em->flush();
                    }
                }
            }


        }

    	return $this->render('RocketSellerTwoPickBundle:BackOffice:procedure.html.twig',array(
    	    'procedure'=>$procedure,
            'employerHasEmployees'=>$employerHasEmployees,
            'employer'=>$employer,
            'actionTypes'=>$actionTypes,
            'formDocument' => $formDocument->createView(),
            'employerNotifications'=>$employerNotifications,
            'employeesNotifications'=>$employeesNotifications,
            'formEmployerEntities'=>$formsEntitiesViews,
            'formEmployerWorkplaces'=>$formsWorkPlacesViews,
            'formsInfoEmployees'=>$viewsInfoEmployees,
            'generalStatus'=>$generalStatus,
            'formEmployeesWorkplaces'=>$viewsEmployeesWorkplaces,
            'formEmployeesEntities'=>$viewsEmployeesEntities,
            'formEmployeesStartDates'=>$viewsEmployeesStartDates,
            'formEmployeesEndDates'=>$viewsEmployeesEndDates,
            'atLeastOne'=>$atLeastOne,
        ));

    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function sendEmployerInfoFinishedAction                       ║
     * ║ Send the employerInfoConfirmationEmail                        ║
     * ║  return true if email was sent                                ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $procedureId                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function sendEmployerInfoFinishedAction($procedureId)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $today = new DateTime();
        /** @var RealProcedure $procedure */
        $procedure = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->find($procedureId);
        if ($procedure->getEmployerEmployer()->getValidatedEmailSentAt() == null){
            $user = $procedure->getUserUser();
            $log = new Log($this->getUser(),'Employer','ValidatedEmailSentAt',$procedure->getEmployerEmployer()->getIdEmployer(),null,$today->format('d-m-Y H:i:s'),'Backoffice envio un correo de validacion de información del empleador');
            $context = array(
                'emailType'=>'docsValidated',
                'toEmail'=>$user->getEmail(),
                'userName'=>$user->getPersonPerson()->getNames(),
            );
            $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            if($send){
                $procedure->getEmployerEmployer()->setValidatedEmailSentAt($today);
                $em->persist($log);
                $em->persist($procedure);
                $em->flush();
            }
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedure->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function sendEmployeeInfoFinishedAction                       ║
     * ║ Send the employerInfoConfirmationEmail                        ║
     * ║  return true if email was sent                                ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $procedureId                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function sendEmployeeInfoFinishedAction($procedureId,$eheId)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $today = new DateTime();
        /** @var RealProcedure $procedure */
        $procedure = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->find($procedureId);
        /** @var EmployerHasEmployee $ehe */
        $ehe = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->find($eheId);
        if ($ehe->getValidatedEmailSentAt() == null){
            $user = $procedure->getUserUser();
            $log = new Log($this->getUser(),'EmployerHasEmployee','ValidatedEmailSentAt',$ehe->getIdEmployerHasEmployee(),null,$today->format('d-m-Y H:i:s'),'Backoffice envio un correo de validacion de información del empleado');
            $context = array(
                'emailType'=>'employeeDocsValidated',
                'toEmail'=>$user->getEmail(),
                'userName'=>$user->getPersonPerson()->getNames(),
                'employeeName'=>$ehe->getEmployeeEmployee()->getPersonPerson()->getNames(),
            );
            $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            if($send){
                $ehe->setValidatedEmailSentAt($today);
                $em->persist($log);
                $em->persist($procedure);
                $em->flush();
            }
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedure->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function sendEmployerInfoErrorAction                          ║
     * ║ Send the employerInfoConfirmationEmail                        ║
     * ║  return true if email was sent                                ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $procedureId                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function sendEmployerInfoErrorAction($procedureId)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $today = new DateTime();
        /** @var RealProcedure $procedure */
        $procedure = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->find($procedureId);
        if ($procedure->getEmployerEmployer()->getErrorEmailSentAt() == null){
            $user = $procedure->getUserUser();
            $errors = array();
            $log = new Log($this->getUser(),'Employer','ErrorEmailSentAt',$procedure->getEmployerEmployer()->getIdEmployer(),null,$today->format('d-m-Y H:i:s'),'Backoffice envio un correo de reporte de errores del empleador');
            /** @var Action $action */
            foreach ($this->getInfoEmployerActions($procedure) as $action){
                if($action->getActionStatusCode()=='ERRO'){
                    switch ($action->getActionTypeCode()){
                        case 'VDDE':
                            $errors[] = $action->getPersonPerson()->getDocumentType();
                            break;
                        case 'VRTE':
                            $errors[] = 'RUT';
                            break;
                        case 'VM';
                            $errors[] = 'MAND';
                            break;
                    }
                }
            }
            $context = array(
                'emailType'=>'docsError',
                'toEmail'=>$user->getEmail(),
                'errors'=>$errors,
                'userName'=>$user->getPersonPerson()->getNames(),
            );
            $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            if($send){
                $procedure->getEmployerEmployer()->setErrorEmailSentAt($today);
                $em->persist($log);
                $em->persist($procedure);
                $em->flush();
            }
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedure->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function sendEmployerInfoErrorAction                          ║
     * ║ Send the employerInfoConfirmationEmail                        ║
     * ║  return true if email was sent                                ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $procedureId                                  ║
     * ║  @param integer $eheId                                        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function sendEmployeeInfoErrorAction($procedureId,$eheId)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();
        $today = new DateTime();
        /** @var RealProcedure $procedure */
        $procedure = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->find($procedureId);
        /** @var EmployerHasEmployee $ehe */
        $ehe = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->find($eheId);
        if ($ehe->getErrorEmailSentAt() == null){
            $user = $procedure->getUserUser();
            $errors = array();
            $log = new Log($this->getUser(),'EmployerHasEmployee','ErrorEmailSentAt',$ehe->getIdEmployerHasEmployee(),null,$today->format('d-m-Y H:i:s'),'Backoffice envio un correo de reporte de errores del empleado');
            /** @var Action $action */
            foreach ($this->getInfoEmployeeActions($procedure,$ehe) as $action){
                if($action->getActionStatusCode()=='ERRO'){
                    switch ($action->getActionTypeCode()){
                        case 'VDD':
                            $errors[] = $action->getPersonPerson()->getDocumentType();
                            break;
                        case 'VRT':
                            $errors[] = 'RUT';
                            break;
                        case 'VCAT';
                            $errors[] = 'CAS';
                            break;
                    }
                }
            }
            $context = array(
                'emailType'=>'employeeDocsError',
                'employeeName'=>$ehe->getEmployeeEmployee()->getPersonPerson()->getNames(),
                'toEmail'=>$user->getEmail(),
                'errors'=>$errors,
                'userName'=>$user->getPersonPerson()->getNames(),
            );
            $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);
            if($send){
                $ehe->setErrorEmailSentAt($today);
                $em->persist($log);
                $em->persist($ehe);
                $em->flush();
            }
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedure->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function finishAction                                         ║
     * ║ Function that changes the action status to finish and         ║
     * ║ sets all the states in procedure and employer                 ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $actionId                                     ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function finishAction($actionId){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository("RocketSellerTwoPickBundle:Action")->find($actionId);
        if($action){
            if($action->getActionTypeCode()=='VENE' or $action->getActionTypeCode()=='INE'){
                if($action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first()->getActionByEmployerHasEntity($action->getEmployerEntity())->first()!=null){
                    /** @var Action $tempAction */
                    $tempAction = $action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first()->getActionByEmployerHasEntity($action->getEmployerEntity())->first();
                    if($tempAction->getActionStatusCode()=='DIS' and $action->getEmployerEntity()->getState()==1){
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $em->persist($tempAction);
                    }else{
                        $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                        $em->persist($tempAction);
                    }
                }else{
                    /** @var RealProcedure $vac */
                    $vac = $action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
                    $tempAction = new Action();
                    $vac->addAction($tempAction);//adding the action to the procedure
                    $action->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                    $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                    $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                    $tempAction->setEmployerEntity($action->getEmployerEntity());

                    if($action->getEmployerEntity()->getState()==1){
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                    }else{
                        $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                    }
                    $tempAction->setUpdatedAt();//setting the action updatedAt Date
                    $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                    $em->persist($tempAction);
                }
            }
            if($action->getActionTypeCode()=='VEN' or $action->getActionTypeCode()=='IN' ){
                if($action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first()->getActionByEmployeeHasEntity($action->getEmployeeEntity())->first()!=null){
                    /** @var Action $tempAction */
                    $tempAction = $action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first()->getActionByEmployeeHasEntity($action->getEmployeeEntity())->first();
                    if($tempAction->getActionStatusCode()=='DIS' and $action->getEmployeeEntity()->getState()==1){
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                        $em->persist($tempAction);
                    }else{
                        $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                        $em->persist($tempAction);
                    }
                }else{
                    /** @var RealProcedure $vac */
                    $vac = $action->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
                    $tempAction = new Action();
                    $vac->addAction($tempAction);//adding the action to the procedure
                    $action->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                    $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                    $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                    $tempAction->setEmployeeEntity($action->getEmployeeEntity());
                    if($action->getEmployeeEntity()->getState()==1){
                        $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                    }else{
                        $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                    }
                    $tempAction->setUpdatedAt();//setting the action updatedAt Date
                    $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                    $em->persist($tempAction);
                }
            }
            $log = new Log($this->getUser(),"Action",'ActionStatus',$action->getIdAction(),$action->getActionStatus(),$this->getStatusByStatusCode('FIN'),"backoffice finalizó una acción");
            $action->setUpdatedAt();
            $action->setActionStatus($this->getStatusByStatusCode('FIN'));
            /** @var ActionError $error */
            foreach ($action->getActionErrorActionError() as $error) {
                $error->setStatus("solved");
            }
            $em->persist($action);
            $em->persist($log);
            $em->flush();
        }else{
            $this->createNotFoundException("No se encontro una acción con id: ".$actionId);
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function activateAction                                       ║
     * ║ Function that changes the action status to NEW and            ║
     * ║ sets all the states in procedure and employer                 ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $actionId                                     ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function activateAction($actionId){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository("RocketSellerTwoPickBundle:Action")->find($actionId);
        if($action){
            $log = new Log($this->getUser(),"Action",'ActionStatus',$action->getIdAction(),$action->getActionStatus(),$this->getStatusByStatusCode('NEW'),"backoffice reactivó una acción");
            $action->setUpdatedAt();
            $action->setActionStatus($this->getStatusByStatusCode('NEW'));
            $em->persist($action);
            $em->persist($log);
            $em->flush();
        }else{
            $this->createNotFoundException("No se encontro una acción con id: ".$actionId);
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function errorDocumentAction                                  ║
     * ║ Function that changes the action status to error and          ║
     * ║ creates the action error and log                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $actionId                                     ║
     * ║  @param integer $notificationId                               ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function errorDocumentAction($actionId,$notificationId){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository("RocketSellerTwoPickBundle:Action")->find($actionId);
        /** @var Notification $notification */
        $notification = $em->getRepository("RocketSellerTwoPickBundle:Notification")->find($notificationId);
        if($action and $notification){
            $log = new Log($this->getUser(),"Action",'ActionStatus',$action->getIdAction(),$action->getActionStatus(),$this->getStatusByStatusCode('ERRO'),"backoffice reportó una error en una acción");
            $action->setUpdatedAt();
            $action->setActionStatus($this->getStatusByStatusCode('ERRO'));
            $actionError = new ActionError();
            $action->addActionErrorActionError($actionError);
            $actionError->setStatus("unresolved");
            $actionError->setDescription("Se encontro un error en el documento");
            $notification->activate();
            $em->persist($notification);
            $em->persist($actionError);
            $em->persist($action);
            $em->persist($log);
            $em->flush();
        }else{
            $this->createNotFoundException("No se encontro el elemento");
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function errorAction                                          ║
     * ║ Function that changes the action status to error and          ║
     * ║ creates the action error and log                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $actionId                                     ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function errorAction($actionId){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository("RocketSellerTwoPickBundle:Action")->find($actionId);
        if($action){
            $log = new Log($this->getUser(),"Action",'ActionStatus',$action->getIdAction(),$action->getActionStatus(),$this->getStatusByStatusCode('ERRO'),"backoffice reportó una error en una acción");
            $action->setUpdatedAt();
            $action->setActionStatus($this->getStatusByStatusCode('ERRO'));
            $actionError = new ActionError();
            $action->addActionErrorActionError($actionError);
            $actionError->setStatus("unresolved");
            $actionError->setDescription("Se encontro un error en la acción");
            $em->persist($actionError);
            $em->persist($action);
            $em->persist($log);
            $em->flush();
        }else{
            $this->createNotFoundException("No se encontro el elemento");
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function activateNotificationAction                           ║
     * ║ Function that activates the notification with the id send     ║
     * ║ by parameter                                                  ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param integer $notificationId                               ║
     * ║  @param integer $actionId                                     ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return boolean                                              ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function activateNotificationAction($notificationId,$actionId){
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        /** @var Action $action */
        $action = $em->getRepository("RocketSellerTwoPickBundle:Action")->find($actionId);
        /** @var Notification $notification */
        $notification = $em->getRepository("RocketSellerTwoPickBundle:Notification")->find($notificationId);
        if($notification){
            $log = new Log($this->getUser(),"Notification",'status',$notificationId,$notification->getStatus(),1,"backoffice reactivó una notificación");
            $notification->activate();
            $em->persist($notification);
            $em->persist($log);
            $em->flush();
        }else{
            $this->createNotFoundException("No se encontro el elemento");
        }
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$action->getRealProcedureRealProcedure()->getIdProcedure()), 301);
    }

    public function generateContractAction($idEmployerHasEmployee,$procedureId){
        $em = $this->getDoctrine()->getManager();
        /** @var RealProcedure $procedure */
        $procedure = $em->getRepository('RocketSellerTwoPickBundle:RealProcedure')->find($procedureId);
        /** @var EmployerHasEmployee $employerHasEmployee */
        $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,"EmployerHasEmployee");
        if($this->getNotificationByPersonAndOwnerAndDocumentType($procedure->getUserUser()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'))!= null){
            /** @var Notification $notification */
            $notification=$this->getNotificationByPersonAndOwnerAndDocumentType($procedure->getUserUser()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'));
            if($notification->getAccion()=='Ver') {
                /** @var EmployerHasEmployee $ehe */
                $ehe = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->find(intval(explode('/', $notification->getRelatedLink())[3]));
                if ($ehe != null and $ehe == $employerHasEmployee) {
                    if ($ehe->getExistentSQL() == 1) {
                        /** @var Person $person */
                        $person=$ehe->getEmployeeEmployee()->getPersonPerson();
                        $contract = $ehe->getActiveContract();
                        $flag = false;
                        if ($ehe->getLegalFF() == 1) {
                            $configurations = $ehe->getEmployeeEmployee()->getPersonPerson()->getConfigurations();
                            /** @var Configuration $config */
                            foreach ($configurations as $config) {
                                if ($config->getValue() == "PreLegal-SignedContract") {
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                        $utils = $this->get('app.symplifica_utils');
                        $notification->setAccion('Subir');
                        if (!$flag) {
                            $notification->setDownloadAction("Bajar");
                            $notification->setDownloadLink($this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf')));
                        }
                        $notification->setDescription("Subir copia del contrato de " . $utils->mb_capitalize(explode(" ", $person->getNames())[0] . " " . $person->getLastName1()));
                        $notification->setRelatedLink($this->generateUrl("documentos_employee", array('entityType' => 'Contract', 'entityId' => $contract->getIdContract(), 'docCode' => 'CTR')));
                    }
                }
            }
        }else{
            $notification = $this->createNotificationByDocType($employerHasEmployee->getEmployerEmployer()->getPersonPerson(),$employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getDocumentTypeByCode('CTR'));
        }
        if($employerHasEmployee->getActiveContract()->getDocumentDocument()){
            if($employerHasEmployee->getActiveContract()->getDocumentDocument()->getMediaMedia()){
                $notification->disable();
                $this->addFlash("employee_contract_successfully", 'El empleado ya habia subido el contrato');
            }else{
                $notification->activate();
                $this->addFlash("employee_contract_successfully", 'Éxito al generar la notificación del contrato');
            }
        }else{
            $notification->activate();
            $this->addFlash("employee_contract_successfully", 'Éxito al generar la notificación del contrato');
        }
        /** @var RealProcedure $vac */
        $vac=$procedure->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
        $vac->setProcedureStatus($this->getStatusByStatusCode('NEW'));
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($procedure->getEmployerEmployer()->getEntities() as $employerHasEntity) {
            if(!$vac->getActionByEmployerHasEntity($employerHasEntity)->first()){
                $tempAction = new Action();
                $vac->addAction($tempAction);//adding the action to the procedure
                $procedure->getUserUser()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                $tempAction->setEmployerEntity($employerHasEntity);
                if($employerHasEntity->getState()==1){
                    $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                }else{
                    $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $tempAction->setUpdatedAt();//setting the action updatedAt Date
                $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                $em->persist($tempAction);
                $em->flush();
            }else{
                $tempAction = $vac->getActionByEmployerHasEntity($employerHasEntity)->first();
                if($employerHasEntity->getState()==1){
                    $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                }else{
                    $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $tempAction->setUpdatedAt();//setting the action updatedAt Date
                $em->persist($tempAction);
                $em->flush();
            }
        }
        /** @var EmployeeHasEntity $employeeEntity */
        foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeEntity) {
            if(!$vac->getActionByEmployeeHasEntity($employeeEntity)->first()){
                $tempAction = new Action();
                $vac->addAction($tempAction);//adding the action to the procedure
                $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
                $vac->getUserUser()->addAction($tempAction);//adding the action to the user
                $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                $tempAction->setEmployeeEntity($employeeEntity);
                if($employerHasEntity->getState()==1){
                    $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                }else{
                    $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $tempAction->setUpdatedAt();//setting the action updatedAt Date
                $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
                $em->persist($tempAction);
                $em->flush();
            }else{
                $tempAction = $vac->getActionByEmployeeHasEntity($employeeEntity)->first();
                if($employerHasEntity->getState()==1){
                    $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
                }else{
                    $tempAction->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $tempAction->setUpdatedAt();//setting the action updatedAt Date
                $em->persist($tempAction);
                $em->flush();
            }
        }
        if(!$vac->getActionsByPersonAndActionType($employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VC'))->first()){
            $tempAction = new Action();
            $vac->addAction($tempAction);//adding the action to the procedure
            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($tempAction);//adding the action to the employerPerson
            $vac->getUserUser()->addAction($tempAction);//adding the action to the user
            $tempAction->setActionTypeActionType($this->getActionTypeByActionTypeCode('vc'));//setting actionType to validate entity
            $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
            $tempAction->setUpdatedAt();//setting the action updatedAt Date
            $tempAction->setCreatedAt(new DateTime());//setting the Action createrAt Date
            $em->persist($tempAction);
        }else{
            $tempAction = $vac->getActionsByPersonAndActionType($employerHasEmployee->getEmployeeEmployee()->getPersonPerson(),$this->getActionTypeByActionTypeCode('VC'))->first();
            $tempAction->setActionStatus($this->getStatusByStatusCode('NEW'));
            $tempAction->setUpdatedAt();//setting the action updatedAt Date
            $em->persist($tempAction);
            $em->flush();
        }
        $em->persist($notification);
        $em->flush();
        return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }

    // --------------------Controller Functions-------------------------

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateProcedureStatus                             ║
     * ║ Calculates de procedure Status if needed                      ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ║  @param bool $force                                           ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer 0 if noting change 1 if something change     ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateProcedureStatus($procedure,$force=false)
    {
        if($procedure->getStatusUpdatedAt()==null or $procedure->getActionChangedAt()==null or $procedure->getStatusUpdatedAt()<$procedure->getActionChangedAt() or $force){
            $today = new DateTime();
            $type = $procedure->getProcedureTypeProcedureType()->getCode();
            if($procedure->getActionChangedAt()==null){
                $procedure->setActionChangedAt($today);
            }
            switch ($type){
                case 'REE':
                    /** @var Employer $emmployer */
                    $emmployer = $procedure->getEmployerEmployer();
                    if($emmployer->getIdSqlSociety()==null){
                        $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                        break;
                    }
                    //if employer have at least one active employerHasEmployee
                    if(count($emmployer->getActiveEmployerHasEmployees())>0 and count($procedure->getAction())>0){
                        $error=false;
                        $corrected = false;
                        $begin = false;
                        $finish = true;
                        $dcpe = false;
                        /** @var Action $actionError */
                        $actionError = null;
                        /** @var Action $actionCorrected */
                        $actionCorrected = null;
                        /** @var Action $action */
                        foreach ($this->getInfoEmployerActions($procedure) as $action) {
                            if($action->getActionStatusCode()=='DCPE'){
                                $dcpe = true;
                            }
                        }
                        if($dcpe){
                            $procedure->setProcedureStatus($this->getStatusByStatusCode('DCPE'));
                            $procedure->setStatusUpdatedAt($today);
                            break;
                        }
                        $atLeastOne = false;
                        $ehes = $procedure->getEmployerEmployer()->getEmployerHasEmployees();
                        /** @var Action $action */
                        /** @var EmployerHasEmployee $ehe */
                        foreach ($ehes as $ehe) {
                            $dcpe = false;
                            foreach ($procedure->getActionsByEmployerHasEmployee($ehe) as $action) {
                                if($action->getActionStatusCode()=='DCPE'){
                                    $dcpe = true;
                                    break;
                                }
                            }
                            if(!$dcpe){
                                $atLeastOne = true;
                                break;
                            }
                            if($ehe->getExistentSQL()!=1){
                                $finish=false;
                            }
                        }
                        if($atLeastOne){
                            foreach ($procedure->getAction() as $action) {
                                if($action->getActionStatusCode()=='ERRO'){
                                    $error = true;
                                    if($actionError==null or $action->getErrorAt()<$actionError->getErrorAt()){
                                        $actionError = $action;
                                    }
                                }
                                if($action->getActionStatusCode()=='CORT'){
                                    $corrected = true;
                                    if($actionCorrected==null or $action->getCorrectedAt()<$actionCorrected->getCorrectedAt()){
                                        $actionCorrected=$action;
                                    }
                                }
                                if($action->getActionStatusCode()=='FIN' and !$begin){
                                    $begin = true;
                                }
                                if($action->getActionStatusCode()!='FIN' and $finish){
                                    $finish = false;
                                }
                            }
                            if($error and !$corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getProcedureStatusCode()!='ERRO'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('ERRO'));
                                }
                            }
                            if($error and $corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('CORT'));
                                }
                            }
                            if($corrected and !$error){
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('CORT'));
                                }
                            }
                            if(!$corrected and !$error){
                                if($begin and !$finish){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('STRT'));
                                }elseif($finish and $procedure->getProcedureStatusCode()!='FIN'){
                                    $finishDate = null;
                                    if($procedure->getFinishedAt()!= null)
                                        $finishDate = $procedure->getFinishedAt();
                                    if($procedure->getProcedureStatus()->getCode()!='FIN'){
                                        $procedure->setProcedureStatus($this->getStatusByStatusCode('FIN'));
                                        if($finishDate!=null)
                                            $procedure->setFinishedAt($finishDate);
                                    }
                                    foreach ($procedure->getEmployerEmployer()->getEmployerHasEmployees() as $ehe){
                                        $ehe->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                                        $ehe->setAllEmployeeDocsReadyAt(new DateTime());
                                        $ehe->setDateFinished(new DateTime());
                                        $this->getDoctrine()->getManager()->persist($ehe);
                                    }
                                }else{
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('NEW'));
                                }
                            }
                            $procedure->setStatusUpdatedAt($today);
                        }else{
                            $procedure->setProcedureStatus($this->getStatusByStatusCode('DCPE'));
                            $procedure->setStatusUpdatedAt($today);
                        }
                    }else{
                        $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                    }
                    break;
                case 'PPL':
                    break;
                case 'VAC':
                    /** @var Employer $emmployer */
                    $emmployer = $procedure->getEmployerEmployer();
                    $oneFinished = false;
                    /** @var EmployerHasEmployee $ehe */
                    foreach ($emmployer->getActiveEmployerHasEmployees() as $ehe) {
                        if($ehe->getExistentSQL()==1){
                            $oneFinished = true;
                            break;
                        }
                    }
                    if(!$oneFinished){
                        $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                        break;
                    }
                    //if employer have at least one active employerHasEmployee
                    if(count($emmployer->getActiveEmployerHasEmployees())>0 and count($procedure->getAction())>0){
                        $error=false;
                        /** @var Action $actionError */
                        $actionError = null;
                        $corrected = false;
                        /** @var Action $actionCorrected */
                        $actionCorrected = null;
                        $begin = false;
                        $finish = true;
                        $dcpe = false;
                        /** @var Action $action */
                        foreach ($procedure->getAction() as $action) {
                            if($action->getActionStatusCode()=='CTPE'){
                                $procedure->setProcedureStatus($this->getStatusByStatusCode('CTPE'));
                                $procedure->setStatusUpdatedAt($today);
                                $dcpe = true;
                                break;
                            }
                            if($action->getActionStatusCode()=='ERRO'){
                                $error = true;
                                if($actionError==null or $action->getErrorAt()<$actionError->getErrorAt()){
                                    $actionError = $action;
                                }
                            }
                            if($action->getActionStatusCode()=='CORT'){
                                $corrected = true;
                                if($actionCorrected==null or $action->getCorrectedAt()<$actionCorrected->getCorrectedAt()){
                                    $actionCorrected=$action;
                                }
                            }
                            if($action->getActionStatusCode()=='FIN' and !$begin){
                                $begin = true;
                            }
                            if($action->getActionStatusCode()!='FIN' and $finish){
                                $finish = false;
                            }

                        }
                        if(!$dcpe){
                            if($error and !$corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getProcedureStatusCode()!='ERRO'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('ERRO'));
                                }
                            }
                            if($error and $corrected){
                                if($procedure->getErrorAt()!=$actionError->getErrorAt()){
                                    $procedure->setErrorAt($actionError->getErrorAt());
                                }
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('CORT'));
                                }
                            }
                            if($corrected and !$error){
                                if($procedure->getCorrectedAt()!=$actionCorrected->getCorrectedAt()){
                                    $procedure->setCorrectedAt($actionCorrected->getCorrectedAt());
                                }
                                if($procedure->getProcedureStatusCode()!='CORT'){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('CORT'));
                                }
                            }
                            if(!$corrected and !$error){
                                if($begin and !$finish){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('STRT'));
                                }elseif($finish){
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('CTVA'));
                                }else{
                                    $procedure->setProcedureStatus($this->getStatusByStatusCode('NEW'));
                                }
                            }
                            $procedure->setStatusUpdatedAt($today);
                        }
                    }else{
                        $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));
                        $procedure->setStatusUpdatedAt($today);
                    }
                    break;
                case 'SPL':

                    break;
            }
            return 1;
        }
        return 0;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateProcedurePriority                           ║
     * ║ Calculates de procedure priority if needed                    ║
     * ║ return 1 if procedure changed 0 if not                        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param RealProcedure $procedure                              ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return integer                                              ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateProcedurePriority($procedure)
    {
        if($procedure->getPriorityUpdatedAt()<$procedure->getActionChangedAt() or $procedure->getPriorityUpdatedAt() == null){
            $today = new DateTime();
            //if procedure already reached maxtime priority is 3
            if($procedure->getMaxTimeReached()==1){
                $priority = 3;
            }else{
                $toMuchTime = false;
                switch($procedure->getProcedureTypeProcedureType()->getCode()){
                    case 'REE':
                        //checking the first error for the procedure
                        if($procedure->getFirstErrorAt()!=null and $procedure->getProcedureStatusCode()!= 'DCPE'){
                            //calculating the time between procedure creation and procedure first error
                            $tempo = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFirstErrorAt()->format("Y-m-d")), array('_format' => 'json'));
                            if ($tempo->getStatusCode() == 200) {
                                $days = json_decode($tempo->getContent(),true)["days"];
                                //if time in days > 3 time is exceeded and all the actions will have priority 2
                                if(intval($days) >= 3){
                                    //flag to much time to notice time was exceeded
                                    $toMuchTime = true;
                                }
                            }
                        }
                        //if time was not exceeded
                        if(!$toMuchTime){
                            $code = $procedure->getProcedureStatusCode();
                            switch ($code){
                                case 'DIS': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'DCPE': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'ERRO': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CORT'://if status is corrected
                                    //dateStart is correctedAt
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'NEW'://if status is new
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'STRT'://if status is newstarted
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        if($procedure->getBackOfficeDate()==null){
                                            $procedure->setBackOfficeDate($today);
                                            $this->getDoctrine()->getManager()->persist($procedure);
                                            $this->getDoctrine()->getManager()->flush();
                                        }
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;

                                case 'FIN'://is status is finished
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getBackOfficeDate()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                            }
                            if ($response->getStatusCode() == 200) {
                                $days = json_decode($response->getContent(),true)["days"];
                                if($days == 0){
                                    $priority = 0;
                                }elseif ($days==1 or $days ==2){
                                    $priority = 1;
                                }elseif ($days==3){
                                    $priority = 2;
                                }elseif ($days >3){
                                    $priority = 3;
                                }
                            }
                        }else{
                            //setting permanently max priority
                            $priority = 3;
                        }
                        break;
                    case 'PPL':
                        break;
                    case 'VAC':
                        /** @var RealProcedure $REEProcedure */
                        $REEProcedure = $procedure->getUserUser()->getProceduresByType($this->getProcedureTypeByCode('REE'))->first();
                        if($REEProcedure->getFinishedAt()!= null){
                            $stardate = $REEProcedure->getFinishedAt();
                            if($procedure->getBackOfficeDate()!=$stardate){
                                $procedure->setBackOfficeDate($stardate);
                            }
                        }else{
                            $stardate = $today;
                        }
                        //checking the first error for the procedure
                        if($procedure->getFirstErrorAt()!=null and $procedure->getProcedureStatusCode()!= 'CTPE'){
                            if($stardate<$procedure->getFirstErrorAt()){
                                //calculating the time between procedure creation and procedure first error
                                $tempo = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFirstErrorAt()->format("Y-m-d")), array('_format' => 'json'));
                                if ($tempo->getStatusCode() == 200) {
                                    $days = json_decode($tempo->getContent(),true)["days"];
                                    //if time in days > 3 time is exceeded and all the actions will have priority 2
                                    if($days = 5){
                                        //flag to much time to notice time was exceeded
                                        $toMuchTime = true;
                                    }
                                }
                            }
                        }
                        //if time was not exceeded
                        if(!$toMuchTime){
                            $code = $procedure->getProcedureStatusCode();
                            switch ($code){
                                case 'DIS': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CTPE': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'ERRO': //if status is Error, Disabled or DocsPending
                                    //dateStart is today
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$today->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'CORT'://if status is corrected
                                    //dateStart is correctedAt
                                    $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    break;
                                case 'NEW'://if status is new
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'STRT'://if status is started
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$today->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                                case 'CTVA'://is status is finished
                                    //checking the first error for the procedure
                                    if($procedure->getFirstErrorAt()!=null){//if procedure got at least one error
                                        //checking maxtime was never exceeded
                                        if($procedure->getMaxTimeReached() == 0){//if not
                                            //if correctedAt is not null and greater than procedure errorAt startdate is correctedAt
                                            if($procedure->getCorrectedAt()!=null and $procedure->getErrorAt()->diff($procedure->getCorrectedAt())->invert == 0){
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getCorrectedAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }else{//if corrected at is null or errorAt is greater than correctedAt must be an error startdate is errorAt
                                                $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$procedure->getErrorAt()->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                            }
                                        }else{//maxtime was reached startdate is backoffice date
                                            $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                        }
                                    }else{//if no errors
                                        //if no errors datestart is backofficeDate
                                        $response = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysBetweenDates',array('dateStart'=>$stardate->format("Y-m-d"),'dateEnd'=>$procedure->getFinishedAt()->format("Y-m-d")), array('_format' => 'json'));
                                    }
                                    break;
                            }
                            if ($response->getStatusCode() == 200) {
                                $days = json_decode($response->getContent(),true)["days"];
                                if($days == 0){
                                    $priority = 0;
                                }elseif ($days==1 or $days ==2){
                                    $priority = 1;
                                }elseif ($days==3){
                                    $priority = 2;
                                }elseif ($days >3){
                                    $priority = 3;
                                }
                            }
                        }else{
                            //setting permanently max priority
                            $priority = 3;
                        }
                        break;
                    case 'SPL':
                        break;
                }
            }
            if($procedure->getPriority()!=$priority){
                $procedure->setPriority($priority);
            }
            $procedure->setPriorityUpdatedAt($today);
            return 1;
        }
        return 0;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function calculateActionPriority                              ║
     * ║ Calculates de action priority if needed                       ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Action $action                                        ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function calculateActionPriority($action)
    {
        if ($action->getUpdatedAt()>$action->getCalculatedAt()){
            $em = $this->getDoctrine()->getManager();
            /** @var Person $person */
            $person = $action->getPersonPerson();
            /** @var RealProcedure $procedure */
            $procedure = $action->getRealProcedureRealProcedure();
            $code = $action->getActionTypeCode();
            switch($code){
                case 'VER':

                    break;
                case 'VDDE':

                    break;
                case 'VRTE':

                    break;
                case 'VRCE':
                    //todo
                    break;
                case 'VM':

                    break;
                case 'VENE':

                    break;

            }


        }
        return;
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function getAllProceudreActionTypes                           ║
     * ║ Returns the array whit all the action types for the procedure ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param string $code                                          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return array                                                ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    protected function getAllProcedureActionTypes()
    {
        return array(
            'VER'=>$this->getActionTypeByActionTypeCode('VER'),
            'VDDE'=>$this->getActionTypeByActionTypeCode('VDDE'),
            'VRTE'=>$this->getActionTypeByActionTypeCode('VRTE'),
            'VM'=>$this->getActionTypeByActionTypeCode('VM'),
            'VENE'=>$this->getActionTypeByActionTypeCode('VENE'),
            'INE'=>$this->getActionTypeByActionTypeCode('INE'),
            'VEE'=>$this->getActionTypeByActionTypeCode('VEE'),
            'VDD'=>$this->getActionTypeByActionTypeCode('VDD'),
            'VCAT'=>$this->getActionTypeByActionTypeCode('VCAT'),
            'VEN'=>$this->getActionTypeByActionTypeCode('VEN'),
            'VIN'=>$this->getActionTypeByActionTypeCode('IN')
        );
    }

    public function DateString(DateTime $date)
    {
        return $date->format("Y-m-d H:i:s");
    }


    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function completeEmployeeAction                               ║
     * ║ Ends the employerHasEmployee Backoffice validation then send  ║
     * ║ an email to the employee user and activates the pods          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Integer $procedureId                                  ║
     * ║  @param Integer $idEmployerHasEmployee                        ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return \Symfony\Component\HttpFoundation\RedirectResponse   ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function completeEmployeeAction($procedureId, $idEmployerHasEmployee)
    {
        $this->denyAccessUnlessGranted('ROLE_BACK_OFFICE', null, 'Unable to access this page!');
    	try {
            $em = $this->getDoctrine()->getManager();
            /** @var EmployerHasEmployee $employerHasEmployee */
            $employerHasEmployee = $this->loadClassById($idEmployerHasEmployee,'EmployerHasEmployee');
            $procedure = $this->loadClassById($procedureId,'RealProcedure');
            if($this->checkActionCompletion($employerHasEmployee,$procedure)){
                $employerHasEmployee->setState(4);
                $employerHasEmployee->setDocumentStatusType($this->getDocumentStatusByCode('BOFFFF'));
                $employerHasEmployee->setDateFinished(new DateTime());
                $em->persist($employerHasEmployee);
                $em->flush();
                $smailer = $this->get('symplifica.mailer.twig_swift');
                $smailer->sendBackValidatedMessage($procedure->getUserUser(),$employerHasEmployee);
                $this->addFlash("employee_ended_successfully", 'Éxito al dar de alta al empleado');
                $contracts = $employerHasEmployee->getContracts();
                /** @var Contract $contract */
                foreach ($contracts as $contract) {
                    if($contract->getState()==1){
                        //we update the payroll
                        $activeP = $contract->getActivePayroll();
                        $dateNow=new DateTime();
                        if($contract->getStartDate()>$dateNow){
                            $realMonth=$contract->getStartDate()->format("m");
                            $realYear=$contract->getStartDate()->format("Y");
                            $realPeriod=intval($contract->getStartDate()->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                        }else{
                            $realMonth=$dateNow->format("m");
                            $realYear=$dateNow->format("Y");
                            $realPeriod=intval($dateNow->format("d"))<=15&&$contract->getFrequencyFrequency()->getPayrollCode()=="Q"?2:4;
                        }
                        $activeP->setMonth($realMonth);
                        $activeP->setYear($realYear);
                        $activeP->setPeriod($realPeriod);
                        $em->persist($activeP);
                        $em->flush();
                        break;
                    }
                }
                return $this->redirectToRoute('show_procedure',array('procedureId'=>$procedureId));
            }else{
                $this->addFlash("employee_ended_faild", 'No se han terminado todos los tramites para este empleado.');
            }
        }catch(Exeption $e){
            $this->addFlash("employee_ended_faild", 'Ocurrio un error terminando el empleado: '. $e);
            return $this->redirectToRoute('show_procedure',array('procedureId'=>$procedureId));
        }
    }

    /**
     * ╔═══════════════════════════════════════════════════════════════╗
     * ║ Function procedureAction                                      ║
     * ║ Creates all real procedures and actions for the user          ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @param Integer $userId                                       ║
     * ╠═══════════════════════════════════════════════════════════════╣
     * ║  @return bool                                                 ║
     * ╚═══════════════════════════════════════════════════════════════╝
     */
    public function procedureAction($userId)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $em->getRepository("RocketSellerTwoPickBundle:User")->find($userId);
        $employer = $user->getPersonPerson()->getEmployer();
        $employer->setDocumentStatus($this->getDocumentStatusByCode('ALLDCP'));
        $em->persist($employer);
        /** @var EmployerHasEmployee $ehe */
        foreach ($employer->getActiveEmployerHasEmployees() as $ehe) {
            $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALLDCP'));
            $em->persist($ehe);
        }
        $today = new DateTime();
        if($user->getRealProcedure()->isEmpty()){
            //se crea el procedure
            $procedure = new RealProcedure();
            $procedure->setProcedureTypeProcedureType($this->getProcedureTypeByCode('REE'));//setting the procedure type
            $employer->addRealProcedure($procedure);//adding the realProcedure to the employer
            $procedure->setCreatedAt($today);//setting the createAt Date
            $procedure->setProcedureStatus($this->getStatusByStatusCode('DCPE'));//setting the initial status Disable
            $procedure->setBackOfficeDate(null);//setting the backofice start Date
            $procedure->setFinishedAt(null);
            $procedure->setPriority(0);//setting the default priority
            $user->addRealProcedure($procedure);//adding the realProcedure to the user
            $em->persist($procedure);
            $em->flush();
            $ree = $procedure;
            //se crea el procedure
            $procedure = new RealProcedure();
            $procedure->setProcedureTypeProcedureType($this->getProcedureTypeByCode('VAC'));//setting the procedure type
            $employer->addRealProcedure($procedure);//adding the realProcedure to the employer
            $procedure->setCreatedAt($today);//setting the createAt Date
            $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));//setting the initial status Disable
            $procedure->setBackOfficeDate(null);//setting the backofice start Date
            $procedure->setFinishedAt(null);
            $procedure->setPriority(0);//setting the default priority
            $user->addRealProcedure($procedure);//adding the realProcedure to the user
            $em->persist($procedure);
            $em->flush();
            $vac = $procedure;
        }else{
            if($user->getProceduresByType($this->getProcedureTypeByCode('REE'))->count()==1){
                $ree = $user->getProceduresByType($this->getProcedureTypeByCode('REE'))->first();
            }elseif($user->getProceduresByType($this->getProcedureTypeByCode('REE'))->count()>1){
                return false;
            }else{
                $procedure = new RealProcedure();
                $procedure->setProcedureTypeProcedureType($this->getProcedureTypeByCode('REE'));//setting the procedure type
                $employer->addRealProcedure($procedure);//adding the realProcedure to the employer
                $procedure->setCreatedAt($today);//setting the createAt Date
                $procedure->setProcedureStatus($this->getStatusByStatusCode('DCPE'));//setting the initial status Disable
                $procedure->setBackOfficeDate(null);//setting the backofice start Date
                $procedure->setFinishedAt(null);
                $procedure->setPriority(0);//setting the default priority
                $user->addRealProcedure($procedure);//adding the realProcedure to the user
                $em->persist($procedure);
                $em->flush();
                $ree = $procedure;
            }
            if($user->getProceduresByType($this->getProcedureTypeByCode('VAC'))->count()==1){
                $vac = $user->getProceduresByType($this->getProcedureTypeByCode('VAC'))->first();
            }elseif($user->getProceduresByType($this->getProcedureTypeByCode('VAC'))->count()>1){
                return false;
            }else{
                $procedure = new RealProcedure();
                $procedure->setProcedureTypeProcedureType($this->getProcedureTypeByCode('VAC'));//setting the procedure type
                $employer->addRealProcedure($procedure);//adding the realProcedure to the employer
                $procedure->setCreatedAt($today);//setting the createAt Date
                $procedure->setProcedureStatus($this->getStatusByStatusCode('DIS'));//setting the initial status Disable
                $procedure->setBackOfficeDate(null);//setting the backofice start Date
                $procedure->setFinishedAt(null);
                $procedure->setPriority(0);//setting the default priority
                $user->addRealProcedure($procedure);//adding the realProcedure to the user
                $em->persist($procedure);
                $em->flush();
                $vac = $procedure;
            }
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer Info                    ║
         * ╚══════════════════════════════════════════════════╝
         */
        if($ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VER'))->first()){
            $action = $ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VER'))->first();
        }else{
            if ($user->getPersonPerson()->getEmployee()) {//if user is also a employee
                $person = $user->getPersonPerson();
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VER'));//setting the actionType validate employer info
                if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first()){
                    if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first()->getActionStatusCode()=='FIN'){
                        $action->setActionStatus($this->getStatusByStatusCode('FIN'));//setting the initial state disable
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('CON'));//setting the initial state disable
                    }
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the Action Status to NEW
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            } else {
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VER'));//setting the actionType validate employer info
                $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the Action Status to NEW
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            }
            $em->flush();
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer Document                ║
         * ╚══════════════════════════════════════════════════╝
         */
        if($ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VDDE'))->first()){
            $action = $ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VDDE'))->first();
        }else{
            if ($user->getPersonPerson()->getEmployee()) {//if user is also a employee
                $person = $user->getPersonPerson();
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VDDE'));//setting the actionType validate employer Document
                if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->first()){
                    if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->first()->getActionStatusCode()=='FIN'){
                        $action->setActionStatus($this->getStatusByStatusCode('FIN'));//setting the initial state disable
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('CON'));//setting the initial state disable
                    }
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));//setting the Action Status to NEW
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            } else {
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VDDE'));//setting the actionType validate employer Document
                if($employer->getPersonPerson()->getDocumentDocument()){
                    if($employer->getPersonPerson()->getDocumentDocument()->getMediaMedia()){
                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                    }
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            }
            $em->flush();
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer RUT                     ║
         * ╚══════════════════════════════════════════════════╝
         */
        if($ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VRTE'))->first()){
            $action = $ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VRTE'))->first();
        }else{
            if ($user->getPersonPerson()->getEmployee()) {//if user is also a employee
                $person = $user->getPersonPerson();
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VRTE'));//setting the actionType validate employer RUt
                if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VRT'))->first()){
                    if($person->getActionsByActionType($this->getActionTypeByActionTypeCode('VRT'))->first()->getActionStatusCode()=='FIN'){
                        $action->setActionStatus($this->getStatusByStatusCode('FIN'));//setting the initial state disable
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('CON'));//setting the initial state disable
                    }
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));//setting the Action Status to NEW
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            } else {
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VRTE'));//setting the actionType validate employer RUT
                if($employer->getPersonPerson()->getRutDocument()){
                    if($employer->getPersonPerson()->getRutDocument()->getMediaMedia()){
                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                    }
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            }
            $em->flush();
        }
        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Action validate employer mandatory               ║
         * ╚══════════════════════════════════════════════════╝
         */
        if($ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VM'))->first()){
            $action = $ree->getActionsByPersonAndActionType($user->getPersonPerson(),$this->getActionTypeByActionTypeCode('VM'))->first();
        }else{
            $action = new Action();
            $ree->addAction($action);//adding the action to the procedure
            $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
            $user->addAction($action);//adding the action to the user
            $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VM'));//setting the actionType validate employer mandatory
            if($employer->getMandatoryDocument()){
                if($employer->getMandatoryDocument()->getMediaMedia()){
                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                }
            }else{
                $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
            }
            $action->setUpdatedAt();//setting the action updatedAt Date
            $action->setCreatedAt($today);//setting the Action createrAt Date
            $em->persist($action);
            $em->flush();
        }

        /**
         * ╔══════════════════════════════════════════════════╗
         * ║ Employer Entities Actions                        ║
         * ╚══════════════════════════════════════════════════╝
         */
        /** @var EmployerHasEntity $employerHasEntity */
        foreach ($employer->getEntities() as $employerHasEntity) {//crossing employerHasEntities to crreate actions for each one
            if($ree->getActionByEmployerHasEntity($employerHasEntity)->first()){
                $action = $ree->getActionByEmployerHasEntity($employerHasEntity)->first();
            }else{
                $action = new Action();
                $ree->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                if ($employerHasEntity->getState() == 0) {//validate entity
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VENE'));//setting actionType to validate entity
                } elseif ($employerHasEntity->getState() == 1) {//subscribe entity
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('INE'));//setting actionType to validate entity
                }
                $action->setEmployerEntity($employerHasEntity);
                $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            }
            if($vac->getActionByEmployerHasEntity($employerHasEntity)->first()){
                $action = $vac->getActionByEmployerHasEntity($employerHasEntity)->first();
                $action->setEmployerEntity($employerHasEntity);
                if ($employerHasEntity->getState() == 1) {//validate entity
                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $em->persist($action);
            }else{
                $action = new Action();
                $vac->addAction($action);//adding the action to the procedure
                $employer->getPersonPerson()->addAction($action);//adding the action to the employerPerson
                $user->addAction($action);//adding the action to the user
                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                $action->setEmployerEntity($employerHasEntity);
                if ($employerHasEntity->getState() == 1) {//validate entity
                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                }else{
                    $action->setActionStatus($this->getStatusByStatusCode('DIS'));
                }
                $action->setUpdatedAt();//setting the action updatedAt Date
                $action->setCreatedAt($today);//setting the Action createrAt Date
                $em->persist($action);
            }
            $em->flush();
        }
        /** @var EmployerHasEmployee $ehe */
        foreach ($employer->getActiveEmployerHasEmployees() as $ehe){
            if($ehe->getEmployerEmployer()->getPersonPerson() == $ehe->getEmployeeEmployee()->getPersonPerson()){
                $ehe->setState(-2);//setting the state of error
                $em->persist($ehe);
                return false;
            }
            $ePerson = $ehe->getEmployeeEmployee()->getPersonPerson();
            if($ree->getActionsByPerson($ePerson)->count()==0){//action has not been created
                if($ePerson->getAction()->count()>0){//its employee or employer of someone else or actions has been created before
                    $ehes = $em->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")->findBy(array('employeeEmployee'=>$ePerson->getEmployee()));
                    $isEmployeeOf = 0;
                    foreach ($ehes as $count){
                        $isEmployeeOf++;
                    }
                    if($ePerson->getEmployer()){//its also a employer
                        if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->count()==1){
                            $action = $ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first();
                        }elseif($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->count()>1){
                            return false;
                        }elseif($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->count()==0){
                            if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VER'))->count()==1){
                                $action = new Action();
                                $ree->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VEE'));//setting actionType to validate info
                                $action->setActionStatus($this->getStatusByStatusCode('CON'));//setting the action status to new
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                            }else{
                                $action = new Action();
                                $ree->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VEE'));//setting actionType to validate info
                                $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                            }
                            $em->flush();
                        }
                        if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->count()==1){
                            $action = $ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->first();
                        }elseif($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->count()>1){
                            return false;
                        }elseif($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->count()==0){
                            if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDDE'))->count()==1){
                                $action = new Action();
                                $ree->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VDD'));//setting actionType to validate doc
                                $action->setActionStatus($this->getStatusByStatusCode('CON'));//setting the action status to new
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                            }else{
                                $action = new Action();
                                $ree->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VDD'));//setting actionType to validate doc
                                if($ePerson->getDocumentDocument()){
                                    if($ePerson->getDocumentDocument()->getMediaMedia()){
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                    }else{
                                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                    }
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                }
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                            }
                            $em->flush();
                        }
                        if($ree->getActionsByPersonAndActionType($ePerson,$this->getActionTypeByActionTypeCode('VCAT'))->count()==0){
                            $action = new Action();
                            $ree->addAction($action);//adding the action to the procedure
                            $ePerson->addAction($action);//adding the action to the employerPerson
                            $user->addAction($action);//adding the action to the user
                            $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VCAT'));//setting actionType to validate doc
                            if($ehe->getAuthDocument()){
                                if($ehe->getAuthDocument()->getMediaMedia()){
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                }
                            }else{
                                $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                            }
                            $action->setUpdatedAt();//setting the action updatedAt Date
                            $action->setCreatedAt($today);//setting the Action createrAt Date
                            $em->persist($action);
                            $em->flush();
                        }
                        if($vac->getActionsByPersonAndActionType($ePerson,$this->getActionTypeByActionTypeCode('VC'))->count()==0){
                            $action = new Action();
                            $vac->addAction($action);//adding the action to the procedure
                            $ePerson->addAction($action);//adding the action to the employerPerson
                            $user->addAction($action);//adding the action to the user
                            $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VC'));//setting actionType to validate Contract
                            if($ehe->getActiveContract()->getDocumentDocument()){
                                if($ehe->getActiveContract()->getDocumentDocument()->getMediaMedia()){
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                }
                            }else{
                                $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                            }
                            $action->setActionStatus($this->getStatusByStatusCode('DCPE'));//setting the action status to new
                            $action->setUpdatedAt();//setting the action updatedAt Date
                            $action->setCreatedAt($today);//setting the Action createrAt Date
                            $em->persist($action);
                            $em->flush();
                        }
                        /** @var EmployeeHasEntity $employeeHasEntity */
                        foreach ($ehe->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
                            if($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()==1){
                                $action = $ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->first();
                                $action->setEmployeeEntity($employeeHasEntity);
                                if($employeeHasEntity->getState()==1){
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                                }
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $em->persist($action);
                                $em->flush();
                            }elseif($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()==0){
                                if($employeeHasEntity->getState()!=-1){
                                    if($employeeHasEntity->getState()==0){
                                        $action = new Action();
                                        $ree->addAction($action);//adding the action to the procedure
                                        $ePerson->addAction($action);//adding the action to the employerPerson
                                        $user->addAction($action);//adding the action to the user
                                        $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VEN'));//setting actionType to validate entity
                                        $action->setEmployeeEntity($employeeHasEntity);
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                        $action->setUpdatedAt();//setting the action updatedAt Date
                                        $action->setCreatedAt($today);//setting the Action createrAt Date
                                        $em->persist($action);
                                        $action = new Action();
                                        $vac->addAction($action);//adding the action to the procedure
                                        $ePerson->addAction($action);//adding the action to the employerPerson
                                        $user->addAction($action);//adding the action to the user
                                        $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                                        $action->setEmployeeEntity($employeeHasEntity);
                                        $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                                        $action->setUpdatedAt();//setting the action updatedAt Date
                                        $action->setCreatedAt($today);//setting the Action createrAt Date
                                        $em->persist($action);
                                        $em->flush();
                                    }elseif($employeeHasEntity->getState()==1){
                                        $action = new Action();
                                        $ree->addAction($action);//adding the action to the procedure
                                        $ePerson->addAction($action);//adding the action to the employerPerson
                                        $user->addAction($action);//adding the action to the user
                                        $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('IN'));//setting actionType to validate entity
                                        $action->setEmployeeEntity($employeeHasEntity);
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                        $action->setUpdatedAt();//setting the action updatedAt Date
                                        $action->setCreatedAt($today);//setting the Action createrAt Date
                                        $em->persist($action);
                                        $action = new Action();
                                        $vac->addAction($action);//adding the action to the procedure
                                        $ePerson->addAction($action);//adding the action to the employerPerson
                                        $user->addAction($action);//adding the action to the user
                                        $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                                        $action->setEmployeeEntity($employeeHasEntity);
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                        $action->setUpdatedAt();//setting the action updatedAt Date
                                        $action->setCreatedAt($today);//setting the Action createrAt Date
                                        $em->persist($action);
                                        $em->flush();
                                    }

                                }
                            }
                        }
                    }else{
                        if($isEmployeeOf>1){
                            if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->count()==1){
                                $action = $ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VEE'))->first();
                            }else{
                                return false;
                            }
                            if($ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->count()==1){
                                $action = $ePerson->getActionsByActionType($this->getActionTypeByActionTypeCode('VDD'))->first();
                            }else{
                                return false;
                            }
                            if($ree->getActionsByPersonAndActionType($ePerson,$this->getActionTypeByActionTypeCode('VCAT'))->count()==0){
                                $action = new Action();
                                $ree->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VCAT'));//setting actionType to validate doc
                                if($ehe->getAuthDocument()){
                                    if($ehe->getAuthDocument()->getMediaMedia()){
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                    }else{
                                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                    }
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                }
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                                $em->flush();
                            }
                            if($vac->getActionsByPersonAndActionType($ePerson,$this->getActionTypeByActionTypeCode('VC'))->count()==0){
                                $action = new Action();
                                $vac->addAction($action);//adding the action to the procedure
                                $ePerson->addAction($action);//adding the action to the employerPerson
                                $user->addAction($action);//adding the action to the user
                                $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VC'));//setting actionType to validate Contract
                                if($ehe->getActiveContract()->getDocumentDocument()){
                                    if($ehe->getActiveContract()->getDocumentDocument()->getMediaMedia()){
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                                    }else{
                                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                    }
                                }else{
                                    $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                                }
                                $action->setUpdatedAt();//setting the action updatedAt Date
                                $action->setCreatedAt($today);//setting the Action createrAt Date
                                $em->persist($action);
                                $em->flush();
                            }
                            foreach ($ehe->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
                                if($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()==1) {
                                    $action = $ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->first();
                                    $action->setEmployeeEntity($employeeHasEntity);
                                    if($employeeHasEntity->getState()==1){
                                        $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                    }else{
                                        $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                                    }
                                    $action->setUpdatedAt();//setting the action updatedAt Date
                                    $em->persist($action);
                                    $em->flush();
                                }else{
                                    return false;
                                }
                            }
                        }else{
                            return false;
                        }
                    }
                }else{
                    $action = new Action();
                    $ree->addAction($action);//adding the action to the procedure
                    $ePerson->addAction($action);//adding the action to the employerPerson
                    $user->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VEE'));//setting actionType to validate info
                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                    $action->setUpdatedAt();//setting the action updatedAt Date
                    $action->setCreatedAt($today);//setting the Action createrAt Date
                    $em->persist($action);
                    $em->flush();
                    $action = new Action();
                    $ree->addAction($action);//adding the action to the procedure
                    $ePerson->addAction($action);//adding the action to the employerPerson
                    $user->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VDD'));//setting actionType to validate docs
                    if($ePerson->getDocumentDocument()){
                        if($ePerson->getDocumentDocument()->getMediaMedia()){
                            $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                        }else{
                            $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                        }
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                    }
                    $action->setUpdatedAt();//setting the action updatedAt Date
                    $action->setCreatedAt($today);//setting the Action createrAt Date
                    $em->persist($action);
                    $em->flush();
                    $action = new Action();
                    $ree->addAction($action);//adding the action to the procedure
                    $ePerson->addAction($action);//adding the action to the employerPerson
                    $user->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VCAT'));//setting actionType to validate doc
                    if($ehe->getAuthDocument()){
                        if($ehe->getAuthDocument()->getMediaMedia()){
                            $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                        }else{
                            $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                        }
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                    }
                    $action->setUpdatedAt();//setting the action updatedAt Date
                    $action->setCreatedAt($today);//setting the Action createrAt Date
                    $em->persist($action);
                    $em->flush();
                    $action = new Action();
                    $vac->addAction($action);//adding the action to the procedure
                    $ePerson->addAction($action);//adding the action to the employerPerson
                    $user->addAction($action);//adding the action to the user
                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VC'));//setting actionType to validate Contract
                    if($ehe->getActiveContract()->getDocumentDocument()){
                        if($ehe->getActiveContract()->getDocumentDocument()->getMediaMedia()){
                            $action->setActionStatus($this->getStatusByStatusCode('NEW'));
                        }else{
                            $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                        }
                    }else{
                        $action->setActionStatus($this->getStatusByStatusCode('DCPE'));
                    }
                    $action->setUpdatedAt();//setting the action updatedAt Date
                    $action->setCreatedAt($today);//setting the Action createrAt Date
                    $em->persist($action);
                    $em->flush();
                    /** @var EmployeeHasEntity $employeeHasEntity */
                    foreach ($ehe->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
                        if($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()==1){
                            $action = $ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->first();
                            $action->setEmployeeEntity($employeeHasEntity);
                            if($employeeHasEntity->getState()==1){
                                $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                            }else{
                                $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                            }
                            $action->setUpdatedAt();//setting the action updatedAt Date
                            $em->persist($action);
                            $em->flush();
                        }elseif($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()>1){
                            return false;
                        }elseif($ePerson->getActionByEmployeeHasEntity($employeeHasEntity)->count()==0){
                            if($employeeHasEntity->getState()!=-1) {
                                if($employeeHasEntity->getState()==0){
                                    $action = new Action();
                                    $ree->addAction($action);//adding the action to the procedure
                                    $ePerson->addAction($action);//adding the action to the employerPerson
                                    $user->addAction($action);//adding the action to the user
                                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('VEN'));//setting actionType to validate entity
                                    $action->setEmployeeEntity($employeeHasEntity);
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                    $action->setUpdatedAt();//setting the action updatedAt Date
                                    $action->setCreatedAt($today);//setting the Action createrAt Date
                                    $em->persist($action);
                                    $action = new Action();
                                    $vac->addAction($action);//adding the action to the procedure
                                    $ePerson->addAction($action);//adding the action to the employerPerson
                                    $user->addAction($action);//adding the action to the user
                                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                                    $action->setEmployeeEntity($employeeHasEntity);
                                    $action->setActionStatus($this->getStatusByStatusCode('DIS'));//setting the action status to new
                                    $action->setUpdatedAt();//setting the action updatedAt Date
                                    $action->setCreatedAt($today);//setting the Action createrAt Date
                                    $em->persist($action);
                                    $em->flush();
                                }elseif($employeeHasEntity->getState()==1){
                                    $action = new Action();
                                    $ree->addAction($action);//adding the action to the procedure
                                    $ePerson->addAction($action);//adding the action to the employerPerson
                                    $user->addAction($action);//adding the action to the user
                                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('IN'));//setting actionType to validate entity
                                    $action->setEmployeeEntity($employeeHasEntity);
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                    $action->setUpdatedAt();//setting the action updatedAt Date
                                    $action->setCreatedAt($today);//setting the Action createrAt Date
                                    $em->persist($action);
                                    $action = new Action();
                                    $vac->addAction($action);//adding the action to the procedure
                                    $ePerson->addAction($action);//adding the action to the employerPerson
                                    $user->addAction($action);//adding the action to the user
                                    $action->setActionTypeActionType($this->getActionTypeByActionTypeCode('SDE'));//setting actionType to validate entity
                                    $action->setEmployeeEntity($employeeHasEntity);
                                    $action->setActionStatus($this->getStatusByStatusCode('NEW'));//setting the action status to new
                                    $action->setUpdatedAt();//setting the action updatedAt Date
                                    $action->setCreatedAt($today);//setting the Action createrAt Date
                                    $em->persist($action);
                                    $em->flush();
                                }
                            }
                        }
                    }
                }
            }
        }
        $em->persist($ree);
        $em->flush();

//        switch(){
//			// registro empleador y empleados
//            case 1:
//				// se crea la accion para validar la informacion registrada por el empleador
//				$action = new Action();
//				$action->setStatus('Nuevo');
//				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VER'),"ActionType"));
//                $procedure->addAction($action);
//                $employerSearch->getPersonPerson()->addAction($action);
//                $userSearch->addAction($action);
//				$em->persist($action);
//
//				// se crea la accion para validar documentos del empleador
//				$action = new Action();
//				$action->setStatus('Nuevo');
//				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
//                $procedure->addAction($action);
//                $employerSearch->getPersonPerson()->addAction($action);
//                $userSearch->addAction($action);
//				$em->persist($action);
//
//				$action = new Action();
//				$action->setStatus('Nuevo');
//				$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VM'),"ActionType"));
//                $procedure->addAction($action);
//                $employerSearch->getPersonPerson()->addAction($action);
//                $userSearch->addAction($action);
//				$em->persist($action);
//
//				// se obtienen las entidades del empleador
//				/** @var EmployerHasEntity $entities */
//				foreach ($employerSearch->getEntities() as $entities) {
//					if ($entities->getState()>=0) {
//						//se crea la accion para la entidad del empleador
//						$action = new Action();
//						$action->setStatus('Nuevo');
//                        $procedure->addAction($action);
//                        $userSearch->addAction($action);
//                        $employerSearch->getPersonPerson()->addAction($action);
//						$action->setEmployerEntity($entities);
//                        //si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
//                        if ($entities->getState()===0){
//                            $action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
//                            $em->persist($action);
//                            //si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
//                        }elseif($entities->getState()===1){
//                            $action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
//                            $em->persist($action);
//                        }
//                    }
//
//				}
//				//se obtienen todos los emleados del empleador
//				/** @var EmployerHasEmployee $employerHasEmployee */
//				foreach ($employerSearch->getEmployerHasEmployees() as $employerHasEmployee) {
//					if ($employerHasEmployee->getState()>=2){
//						//si el empleado no tiene acciones creadas es decir no es empleado de algun otro empleador
//						if ($employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getAction()->isEmpty()){
//							//se crea la accion para validar la informacion del empleado
//							$action = new Action();
//							$action->setStatus('Nuevo');
//							$procedure->addAction($action);
//                            $userSearch->addAction($action);
//                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEE'),"ActionType"));
//							$em->persist($action);
//
//							//se crea la accion para validar documentos y generar contrato
//							$action = new Action();
//							$action->setStatus('Nuevo');
//                            $procedure->addAction($action);
//                            $userSearch->addAction($action);
//                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
//							$em->persist($action);
//
//							//se obtienen las entidades del empleado
//							/** @var EmployeeHasEntity $employeeHasEntity */
//							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
//								if ($employeeHasEntity->getState()>=0) {
//									//se crea a accion para las entidades del empleado
//									$action = new Action();
//									$action->setStatus('Nuevo');
//                                    $procedure->addAction($action);
//                                    $userSearch->addAction($action);
//                                    $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//									$action->setEmployeeEntity($employeeHasEntity);
//
//									//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
//									if ($employeeHasEntity->getState()===0){
//										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
//										$em->persist($action);
//										//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
//									}elseif($employeeHasEntity->getState()===1){
//										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
//										$em->persist($action);
//									}
//								}
//							}
//							//si el empleado es antiguo (ya inicio labores) se crea el tramite de validar contrato
//							if($employerHasEmployee->getLegalFF()==1){
//								$actionV = new Action();
//								$actionV->setStatus('Nuevo');
//                                $procedure->addAction($actionV);
//                                $userSearch->addAction($actionV);
//                                $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($actionV);
//								$actionV->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
//								$em->persist($actionV);
//								//se agrega la accion al procedimiento
//								$action->getRealProcedureRealProcedure()->addAction($actionV);
//							}
//                        //si el empleado ya es empleado de alguien mas solo se validan las entidades ya existentes
//						}else{
//							//se crea la accion de informacion del empleado validada
//							$action = new Action();
//							$action->setStatus('Completado');
//                            $procedure->addAction($action);
//                            $userSearch->addAction($action);
//                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEE'),"ActionType"));
//							$em->persist($action);
//
//							//se crea la accion para validar documentos y generar contrato
//							$action = new Action();
//							$action->setStatus('Nuevo');
//                            $procedure->addAction($action);
//                            $userSearch->addAction($action);
//                            $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
//							$em->persist($action);
//
//                            //si el empleado ya es empleado de alguien se crean los tramites ya completados
//							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
//								if ($employeeHasEntity->getState()>=0) {
//									//se crea a accion para las entidades del empleado
//									$action = new Action();
//									$action->setStatus('Completado');
//                                    $procedure->addAction($action);
//                                    $userSearch->addAction($action);
//                                    $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//									$action->setEmployeeEntity($employeeHasEntity);
//
//									//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
//									if ($employeeHasEntity->getState()===0){
//										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VEN'),"ActionType"));
//										$em->persist($action);
//										//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
//									}elseif($employeeHasEntity->getState()===1){
//										$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'IN'),"ActionType"));
//										$em->persist($action);
//									}
//									//se agrega la accion al procedimiento
//									$procedure->addAction($action);
//								}
//							}
//							//si el empleado es antiguo (ya inicio labores) se crea el tramite de validar contrato
//							if($employerHasEmployee->getLegalFF()==1){
//								$action = new Action();
//								$action->setStatus('Nuevo');
//                                $procedure->addAction($action);
//                                $userSearch->addAction($action);
//                                $employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->addAction($action);
//								$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
//								$em->persist($action);
//							}
//						}
//					}
//				}
//                $em->flush();
//				$em2->flush();
//                break;
//            case 2:
//
//                break;
//            case 3:
//                break;
//			// registro empleado
//            case 4:
//				/** @var EmployerHasEmployee $employerHasEmployee */
//				foreach ($employerSearch->getEmployerHasEmployees() as $employerHasEmployee) {
//					if ($employerHasEmployee->getState()>2){
//						//si el empleado no tiene acciones creadas es decir no es empleado de algun otro empleador
//						if ($employerHasEmployee->getEmployeeEmployee()->getPersonPerson()->getAction()->isEmpty()) {
//							//se crea la accion para validar la informacion del empleado
//							$action = new Action();
//							$action->setStatus('Nuevo');
//							$action->setRealProcedureRealProcedure($procedure);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'VEE'), "ActionType"));
//							$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
//							$action->setUserUser($userSearch);
//							$em->persist($action);
//							$em->flush();
//							//se agrega la accion al procedimiento
//							$procedure->addAction($action);
//
//							$action = new Action();
//							$action->setStatus('Nuevo');
//							$action->setRealProcedureRealProcedure($procedure);
//							$action->setActionTypeActionType($this->loadClassByArray(array('code'=>'VDC'),"ActionType"));
//							$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
//							$action->setUserUser($userSearch);
//							$em->persist($action);
//							$em->flush();
//							//se agrega la accion al procedimiento
//							$procedure->addAction($action);
//
//							//se obtienen las entidades del empleado
//							/** @var EmployeeHasEntity $employeeHasEntity */
//							foreach ($employerHasEmployee->getEmployeeEmployee()->getEntities() as $employeeHasEntity) {
//								//se crea a accion para las entidades del empleado
//								$action = new Action();
//								$action->setStatus('Nuevo');
//								$action->setRealProcedureRealProcedure($procedure);
//								$action->setEntityEntity($employeeHasEntity->getEntityEntity());
//								//si el usuario ya pertenece a la entidad se asigna el tipo de accion de validar la entidad
//								if ($employeeHasEntity->getState() === 0) {
//									$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'VEN'), "ActionType"));
//									$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
//									$action->setUserUser($userSearch);
//									$em->persist($action);
//									$em->flush();
//									//si el usuario desea inscribirse se asigna el tipo de accion para inscribir entidad
//								} elseif ($employeeHasEntity->getState() === 1) {
//									$action->setActionTypeActionType($this->loadClassByArray(array('code' => 'IN'), "ActionType"));
//									$action->setPersonPerson($employerHasEmployee->getEmployeeEmployee()->getPersonPerson());
//									$action->setUserUser($userSearch);
//									$em->persist($action);
//									$em->flush();
//								}
//								//se agrega la accion al procedimiento
//								$procedure->addAction($action);
//							}
//						}
//					}
//
//				}
//                break;
//            default:
//				$em2->remove($procedure);
//				$em2->flush();
//                break;
//        }
        return true;
    }

    /**
     * estructura de tramite para generar vueltas y tramites
     * @param  $id $id_employer       id del empleador que genera el tramite
     * @param  $id $id_procedure_type id del tipo de tramite a realizar
     * @param  $id $id_user 		   usuario que va a realizar el tramite
     * @param  Array() $employees      arreglo de empleados con:
     *                               ->id_employee
     *                          	 ->id_contrato
     *                               ->Array docs
     *                               ->Array entidades
     *                               		->id_employee_has_entity
     *                                 		->id_action_type
     *                                 		->sort_order
     * @return integer $priority       prioridad del empleador (vip, regular)
     */

    public function validateAction($id_employer, $id_procedure_type, $priority, $id_user, $employees)
    {		  		
		$entityInscription = 9;
		$em = $this->getDoctrine()->getManager();	
		$employerSearch = $this->loadClassById($id_employer,"Employer");
		$userSearch = $this->loadClassById($id_user,"User");
		$procedureTypeSearch = $this->loadClassById($id_procedure_type, "ProcedureType");

		$procedure = new RealProcedure();
		$procedure->setUserUser($userSearch);
		$procedure->setCreatedAt(new \DateTime());
		$procedure->setProcedureTypeProcedureType($procedureTypeSearch);
		$procedure->setEmployerEmployer($employerSearch);
		$em->persist($procedure);
			    											
    		foreach ($employees as $employee) {
    			$entities = array();
    			foreach ($employee["entities"] as $entity) {
    				$actionTypeFound = $this->loadClassById($entity["id_action_type"],"ActionType");
		    		$employeeFound = $this->loadClassById($employee["id_employee"],"Employee");
		    		$entityFound = $this->loadClassById($entity["id_entity"],"Entity");
    				$employeeHasEntityFound = $this->loadClassByArray(array("employeeEmployee" => $employeeFound, "entityEntity"=>$entityFound),"EmployeeHasEntity");				    					    				    				    	
				    	if($employeeHasEntityFound){				    		
				    	}else{				    		
				    		if($this->loadClassByArray(array(
				    			"personPerson" => $employeeFound->getPersonPerson(),
				    			"actionTypeActionType" => $this->loadClassById(
				            	$entityInscription,"ActionType"),
				            	"entityEntity" =>$this->loadClassById($entity["id_entity"],"Entity")
				    		),"Action")){
				    			//se verifica que no hallan actions repetidos de inscripcion
				    		}else{
			    				$action = new Action();
					            $action->setUserUser($userSearch);
					            $action->setStatus('Nuevo');
					            $action->setRealProcedureRealProcedure($procedure);
					            $action->setEntityEntity($this->loadClassById($entity["id_entity"],"Entity"));
					            $action->setActionTypeActionType($this->loadClassById(
					            	$entityInscription,"ActionType"));
					            $action->setPersonPerson($employeeFound->getPersonPerson());
					            $em->persist($action);
					            $em->flush();
				             	$procedure->addAction($action);	
				    		}				    		
				    	}
				    	//se verifica que no hallan actions iguales.
				    	if($this->loadClassByArray(array(
				    			"personPerson" => $employeeFound->getPersonPerson(),
				    			"actionTypeActionType" => $actionTypeFound
				    		),"Action")){

				    	}else{
				    		$action = new Action();
				            $action->setUserUser($userSearch);
				            $action->setStatus('Nuevo');
				            $action->setRealProcedureRealProcedure($procedure);
				            $action->setEntityEntity($this->loadClassById($entity["id_entity"],"Entity"));
				            $action->setActionTypeActionType($actionTypeFound);
				            $action->setPersonPerson($employeeFound->getPersonPerson());
				            $em->persist($action);
				            $em->flush();
				            $procedure->addAction($action);
				    	}	    								
    			}
    		}
    		$em->flush();
        return $procedure;
        
    }


    /**
     * Funcion que cambia el estado de una accion y crea notificaciones al empleador
     * @param Integer $procedureId
     * @param Integer $actionId
     * @param String $status
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeVueltaStateAction($procedureId, $actionId, $status)
    {

    	$em = $this->getDoctrine()->getManager();
		/** @var Action $action */
		$action = $this->loadClassById($actionId,"Action");
		//adding verification to check if the actions is validate documents employee
		if($action->getActionTypeActionType()->getCode()=="VDC" and $status!='Error'){
			$employee=$action->getPersonPerson()->getEmployee();
			if($employee!=null){
				/** @var User $user */
				$user=$action->getRealProcedureRealProcedure()->getUserUser();
				$ehes=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
				$realEhe=null;
				/** @var EmployerHasEmployee $eHE */
				foreach ($ehes as $eHE) {
					if($eHE->getEmployeeEmployee()->getIdEmployee()==$employee->getIdEmployee()){
						$realEhe=$eHE;
					}
				}
				if($realEhe!=null){
					$realContract=null;
					$contracts=$realEhe->getContracts();
					/** @var Contract $contract */
					foreach ($contracts as $contract) {
						if($contract->getState()==1){
							$realContract=$contract;
							break;
						}
					}
					if($contract!=null){
						//first create the notification
						$utils = $this->get('app.symplifica_utils');
						$dAction="Bajar";
						$dUrl = $this->generateUrl("download_documents", array('id' => $contract->getIdContract(), 'ref' => "contrato", 'type' => 'pdf'));
						$msj = "Subir copia del contrato de ". $utils->mb_capitalize(explode(" ",$realEhe->getEmployeeEmployee()->getPersonPerson()->getNames())[0]." ". $realEhe->getEmployeeEmployee()->getPersonPerson()->getLastName1());
						$nAction="Subir";
						/** @var DocumentType $documentType */
						$documentType = $em->getRepository('RocketSellerTwoPickBundle:DocumentType')->findOneBy(array("docCode"=>'CTR'));
						$url = $this->generateUrl("documentos_employee", array('entityType'=>'Contract','entityId' =>$contract->getIdContract(), 'docCode' =>'CTR'));
						$notifications=$realEhe->getEmployerEmployer()->getPersonPerson()->getNotifications();
						$urlToFind=$this->generateUrl("view_document_contract_state", array("idEHE"=>$realEhe->getIdEmployerHasEmployee()));
						//searching the notification of the state of the contract to replace its content
						$notification=null;
						/** @var Notification $not */
						foreach ($notifications as $not ) {
							if($not->getRelatedLink()==$urlToFind){
								$notification=$not;
							}
						}
						if($notification==null)
							$notification = new Notification();

						//se envia emai de validacion 3 días
                        $smailer = $this->get('symplifica.mailer.twig_swift');
                        $smailer->sendDiasHabilesMessage($user,$realEhe);
						//se crea la accion de validar contrato si no habia iniciado labores
						if($realEhe->getLegalFF()!=1){
							$actionV = new Action();
							$actionV->setStatus('Nuevo');
							$actionV->setRealProcedureRealProcedure($action->getRealProcedureRealProcedure());
							$actionV->setActionTypeActionType($this->loadClassByArray(array('code'=>'VC'),"ActionType"));
							$actionV->setPersonPerson($realEhe->getEmployeeEmployee()->getPersonPerson());
							$actionV->setUserUser($user);
							$em->persist($actionV);
							$em->flush();
							//se agrega la accion al procedimiento
							$action->getRealProcedureRealProcedure()->addAction($actionV);
						}


						//then check if changing the start date is necessary
						if($realEhe->getLegalFF()==0){
							//ademas agrego la notificacion

							$notification->setPersonPerson($user->getPersonPerson());
							$notification->setStatus(1);
							$notification->setDocumentTypeDocumentType($documentType);
							$notification->setType('alert');
							$notification->setDescription($msj);
							$notification->setRelatedLink($url);
							$notification->setAccion($nAction);
							$notification->setDownloadAction($dAction);
							$notification->setDownloadLink($dUrl);
							$em->persist($notification);

							$todayPlus = new DateTime();
							$request = $this->container->get('request');
							$request->setMethod("GET");
							$insertionAnswer = $this->forward('RocketSellerTwoPickBundle:NoveltyRest:getWorkableDaysToDate',array('dateStart'=>$todayPlus->format("Y-m-d"),'days'=>3), array('_format' => 'json'));
							if ($insertionAnswer->getStatusCode() != 200) {
								return false;
							}
							$permittedDate=new DateTime(json_decode($insertionAnswer->getContent(),true)['date']);
							if($contract->getStartDate()<$permittedDate){
								$contract->setStartDate($permittedDate);
								$em->persist($contract);
							}
						}
						$em->flush();
					}else{
						$notification2 = new Notification();
						$notification2->setPersonPerson($user->getPersonPerson());
						$notification2->setStatus(1);
						$notification2->setDocumentTypeDocumentType(null);
						$notification2->setType('alert');
						$notification2->setDescription("Tu empleado ".$realEhe->getEmployerEmployer()->getPersonPerson()->getFullName()." a sido correctamente validado");
						$notification2->setRelatedLink("/notifications/change/".$notification2->getId()."/0");
						$notification2->setAccion("Cerrar");
						$notification2->setDownloadAction(null);
						$notification2->setDownloadLink(null);
						$em->persist($notification2);
					}
					$em->flush();
				}

			}
		}
    	$action->setStatus($status);
    	$em->persist($action);
    	$em->flush();
    	return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }


    //todo old function test an remove Andres
    public function changeErrorStatusAction($procedureId,$actionError,$status)
    {	
    	
    	$em = $this->getDoctrine()->getManager();	
    	$actionError = $this->loadClassById($actionError,"ActionError");
    	$actionError->setStatus($status);
    	$em->persist($actionError);
    	$em->flush();
    	if ($status == "Corregido") {
    		$action = $this->loadClassByArray(array('actionErrorActionError'=> $actionError),'Action');
    		$action->setStatus("Completado");
    		$em->persist($action);
    		$em->flush();	    		
    	}
    	return $this->redirectToRoute('show_procedure', array('procedureId'=>$procedureId), 301);
    }



    public function loadClassById($parameter, $entity)
    {
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->find($parameter);
		return $loadedClass;
    }

    public function loadClassByArray($array, $entity)
    {
		$loadedClass = $this->getdoctrine()
		->getRepository('RocketSellerTwoPickBundle:'.$entity)
		->findOneBy($array);
		return $loadedClass;
    }

}
