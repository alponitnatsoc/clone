<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NotificationEmployerAdminCRUDController extends CRUDController
{

    public function createAction()
    {
        // the key used to lookup the template
        $templateKey = 'edit';

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

//        var_dump($object->getEmployerEmployer());
//        var_dump($this->admin->getSubject()->getEmployerEmployer());
//        var_dump($form->getData());
//        exit();
//        $query = $em->createQuery('SELECT u, a, p, c FROM CmsUser u JOIN u.articles a JOIN u.phonenumbers p JOIN a.comments c');
//        $qb = $em->createQueryBuilder();

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                if (false === $this->admin->isGranted('CREATE', $object)) {
                    throw new AccessDeniedException();
                }

                try {
                    if (!empty($object->getEmployerEmployer())) {

                        $object = $this->admin->create($object);

                        if ($this->isXmlHttpRequest()) {
                            return $this->renderJson(array(
                                        'result' => 'ok',
                                        'objectId' => $this->admin->getNormalizedIdentifier($object),
                            ));
                        }

                        $this->addFlash(
                                'sonata_flash_success', $this->admin->trans(
                                        'flash_create_success', array('%name%' => $this->escapeHtml($this->admin->toString($object))), 'SonataAdminBundle'
                                )
                        );

                        // redirect to edit mode
                        return $this->redirectTo($object);
                    } else {
                        $Memory_usage_before = (memory_get_usage() / 1024);
                        $s = microtime(true);
                        $employers = $this->getdoctrine()
                                ->getRepository('RocketSellerTwoPickBundle:Employer')
                                ->findAll();

                        $em = $this->getDoctrine()->getManager();
                        $batchSize = 20;
                        foreach ($employers as $i => $employer) {
                            $notificacion = clone $object;
                            $notificacion->setEmployerEmployer($employer);
                            $em->persist($notificacion);
                            // flush everything to the database every 20 inserts
//                            if (($i % $batchSize) == 0) {
//                                $em->flush();
//                                $em->clear();
//                            }
                        }
                        $em->flush();
                        $em->clear();

                        $Memory_usage_after = (memory_get_usage() / 1024);
                        $e = microtime(true);
                        if ($this->isXmlHttpRequest()) {
                            return $this->renderJson(array(
                                        'result' => 'ok',
                                        'objectId' => $this->admin->getNormalizedIdentifier($object),
                                        'Memory_usage_before' => $Memory_usage_before,
                                        'Memory_usage_after' => $Memory_usage_after,
                                        'time' => ($e - $s)
                            ));
                        }

                        $this->addFlash(
                                'sonata_flash_success', $this->admin->trans(
                                        'flash_create_success', array('%name%' => $this->escapeHtml($this->admin->toString($object))), 'SonataAdminBundle'
                                )
                        );
                        $this->addFlash('sonata_flash_success', ("Memory usage before: $Memory_usage_before KB, " .
                                "Memory usage after: $Memory_usage_after KB, " .
                                "Inserted " . ($i + 1) . " objects in " . ($e - $s) . " seconds.")
                        );

                        // redirect to list mode
                        $url = $this->admin->generateUrl('list');
                        return new RedirectResponse($url);
                    }
                } catch (ModelManagerException $e) {
                    $this->logModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                            'sonata_flash_error', $this->admin->trans(
                                    'flash_create_error', array('%name%' => $this->escapeHtml($this->admin->toString($object))), 'SonataAdminBundle'
                            )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
                    'action' => 'create',
                    'form' => $view,
                    'object' => $object,
        ));
    }

}
