<?php

namespace whatwedo\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Form\PlaceType;
use whatwedo\WorkflowBundle\Repository\PlaceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/wwd/workflow/place")
 */
class PlaceController extends AbstractController
{

    /**
     * @Route("/{workflow}/new", name="wwd_workflow_place_new", methods={"GET","POST"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'), workflow)")
     * @param Request $request
     * @param Workflow $workflow
     * @return Response
     */
    public function new(Request $request, Workflow $workflow): Response
    {
        $place = new Place($workflow);

        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($place);
            $entityManager->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_show', [ 'id' => $place->getWorkflow()->getId() ]);
        }

        return $this->render('@whatwedoWorkflow/place/new.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wwd_workflow_place_edit", methods={"GET","POST"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'), place)")
     */
    public function edit(Request $request, Place $place): Response
    {
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_show', [ 'id' => $place->getWorkflow()->getId() ]);
        }

        return $this->render('@whatwedoWorkflow/place/new.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="wwd_workflow_place_delete", methods={"GET"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'), place)")
     */
    public function delete(Request $request, Place $place): Response
    {
        if ($this->isCsrfTokenValid('delete'.$place->getId(), $request->query->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($place);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wwd_workflow_workflow_show', ['id' => $place->getWorkflow()->getId()]);
    }
}
