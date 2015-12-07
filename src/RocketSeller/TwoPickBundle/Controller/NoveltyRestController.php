<?php 
namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use DateTime;

class NoveltyRestController extends FOSRestController
{	

    /**
     * Get the fields of the desired novelty.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Bad Request",
     *     404 = "Returned when the Novelty Type id doesn't exists "
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="noveltyId", nullable=false,  requirements="\d+", strict=true, description="the desired novelty fields id.")
     * @return View
     */
    public function noveltyTypeFieldsAction(ParamFetcher $paramFetcher)
    {

        $view = View::create();

        if ( count($cities)!= 0) {
            $view->setData($cities)->setStatusCode(200);
            return $view;
        } else {
            $view->setStatusCode(404)->setHeader("error","Department does't exist");
            return $view;
        }
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }


}
?>