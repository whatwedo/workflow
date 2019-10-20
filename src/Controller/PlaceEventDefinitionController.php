<?php

namespace whatwedo\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\PlaceEventDefinition;
use whatwedo\WorkflowBundle\Form\PlaceEventDefinitionType;
use whatwedo\WorkflowBundle\Repository\PlaceEventDefinitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wwd/workflow/place/event/definition")
 */
class PlaceEventDefinitionController extends AbstractController
{

    /**
     * @Route("/new/{place}", name="wwd_workflow_place_event_definition_new", methods={"GET","POST"})
     */
    public function new(Request $request, Place $place): Response
    {
        $placeEventDefinition = new PlaceEventDefinition($place);
        $form = $this->createForm(PlaceEventDefinitionType::class, $placeEventDefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($placeEventDefinition);
            $entityManager->flush();

            return $this->redirectToRoute('workflow_place_show', ['id' => $placeEventDefinition->getPlace()->getId()]);
        }

        return $this->render('@whatwedoWorkflow/place_event_definition/new.html.twig', [
            'eventDefinition' => $placeEventDefinition,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wwd_workflow_place_event_definition_show", methods={"GET"})
     */
    public function show(PlaceEventDefinition $placeEventDefinition): Response
    {
        return $this->render('@whatwedoWorkflow/place_event_definition/show.html.twig', [
            'place_event_definition' => $placeEventDefinition,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wwd_workflow_place_event_definition_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, PlaceEventDefinition $placeEventDefinition): Response
    {
        $form = $this->createForm(PlaceEventDefinitionType::class, $placeEventDefinition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('workflow_place_show', ['id' => $placeEventDefinition->getPlace()->getId()]);
        }

        return $this->render('@whatwedoWorkflow/place_event_definition/new.html.twig', [
            'eventDefinition' => $placeEventDefinition,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wwd_workflow_place_event_definition_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PlaceEventDefinition $placeEventDefinition): Response
    {
        if ($this->isCsrfTokenValid('delete'.$placeEventDefinition->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($placeEventDefinition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('workflow_place_show', ['id' => $placeEventDefinition->getPlace()->getId()]);
    }
}
