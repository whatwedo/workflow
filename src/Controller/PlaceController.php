<?php

namespace whatwedo\WorkflowBundle\Controller;

use Socius\Controller\AbstractController;
use Socius\Entity\Workflow\Place;
use Socius\Entity\Workflow\Workflow;
use Socius\Form\Workflow\PlaceType;
use Socius\Repository\Workflow\PlaceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/workflow/place")
 */
class PlaceController extends AbstractController
{

    /**
     * @Route("/{workflow}/new", name="workflow_place_new", methods={"GET","POST"})
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

            return $this->redirectToRoute('workflow_workflow_show', [ 'id' => $place->getWorkflow()->getId() ]);
        }

        return $this->render('workflow/place/new.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="workflow_place_show", methods={"GET"})
     */
    public function show(Place $place): Response
    {
        return $this->render('workflow/place/show.html.twig', [
            'place' => $place,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="workflow_place_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Place $place): Response
    {
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('workflow_workflow_show', [ 'id' => $place->getWorkflow()->getId() ]);
        }

        return $this->render('workflow/place/new.html.twig', [
            'place' => $place,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="workflow_place_delete", methods={"GET"})
     */
    public function delete(Request $request, Place $place): Response
    {
        if ($this->isCsrfTokenValid('delete'.$place->getId(), $request->query->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($place);
            $entityManager->flush();
        }

        return $this->redirectToRoute('workflow_workflow_show', ['id' => $place->getWorkflow()->getId()]);
    }
}
