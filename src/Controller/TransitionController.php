<?php

namespace whatwedo\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Form\TransitionType;
use whatwedo\WorkflowBundle\Repository\TransitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wwd/workflow/transition")
 */
class TransitionController extends AbstractController
{

    /**
     * @Route("/new/{workflow}", name="wwd_workflow_transition_new", methods={"GET","POST"})
     */
    public function new(Request $request, Workflow $workflow): Response
    {
        $transition = new Transition($workflow);
        $form = $this->createForm(TransitionType::class, $transition);
        $form->handleRequest($request);
//        $transition->setWorkflow($workflow);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($transition);
            $entityManager->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_show', [ 'id' => $workflow->getId()]);
        }

        return $this->render('@whatwedoWorkflow/transition/new.html.twig', [
            'transition' => $transition,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wwd_workflow_transition_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Transition $transition): Response
    {
        $form = $this->createForm(TransitionType::class, $transition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_show', ['id' => $transition->getWorkflow()->getId()]);
        }

        return $this->render('@whatwedoWorkflow/transition/new.html.twig', [
            'transition' => $transition,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="wwd_workflow_transition_delete", methods={"GET"})
     */
    public function delete(Request $request, Transition $transition): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transition->getId(), $request->query->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transition);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wwd_workflow_workflow_show', [ 'id' => $transition->getWorkflow()->getId()]);
    }
}
