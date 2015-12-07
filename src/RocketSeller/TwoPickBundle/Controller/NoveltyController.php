<?php
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Novelty;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;
use RocketSeller\TwoPickBundle\Form\NoveltyForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NoveltyController extends Controller
{

    public function addNoveltyShowAction($idPayroll)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('api_public_post_calculator_submit', array('format'=>'json')))
            ->setMethod('POST')
            ->add('noveltyType', 'entity', array(
                'class' => 'RocketSellerTwoPickBundle:NoveltyType',
                'placeholder' => '',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Tipo de novedad',
                'property_path' => 'noveltyTypeNoveltyType',
            ))
            ->getForm();

        return $this->render('RocketSellerTwoPickBundle:Novelty:addNovelty.html.twig',
            array('form' => $form->createView()));
    }
    public function noveltyTypeFieldsAction($noveltyId)
    {
        $idNoveltyType=$noveltyId;
        $noveltyTypeRepo=$this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:NoveltyType');


        /** @var NoveltyType $novelty */
        $novelty= $noveltyTypeRepo->find($idNoveltyType);
        $requiredDocuments=$novelty->getRequiredDocuments();

        $form = $this->createForm(new NoveltyForm($requiredDocuments));
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:generalFormRender.html.twig',
            array('form' => $form->createView())
        );
    }
}
