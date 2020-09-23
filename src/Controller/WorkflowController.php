<?php

namespace whatwedo\WorkflowBundle\Controller;

use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Form\WorkflowType;
use whatwedo\WorkflowBundle\Repository\WorkflowRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;
use Symfony\Component\Workflow\Dumper\PlantUmlDumper;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/wwd/workflow/workflow")
 */
class WorkflowController extends AbstractController
{
    /**
     * @Route("/", name="wwd_workflow_workflow_index", methods={"GET"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'))")
     */
    public function index(WorkflowRepository $workflowRepository): Response
    {
        return $this->render('@whatwedoWorkflow/workflow/index.html.twig', [
            'workflows' => $workflowRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="wwd_workflow_workflow_new", methods={"GET","POST"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'))")
     */
    public function new(Request $request): Response
    {
        $workflow = new Workflow();
        $form = $this->createForm(WorkflowType::class, $workflow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($workflow);
            $entityManager->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_index');
        }

        return $this->render('@whatwedoWorkflow/workflow/new.html.twig', [
            'workflow' => $workflow,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wwd_workflow_workflow_show", methods={"GET"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_SHOW'), workflow)")
     */
    public function show(Workflow $workflow): Response
    {

        $definitionBuilder = new DefinitionBuilder();

        foreach ($workflow->getPlaces() as $place) {
            $definitionBuilder->addPlace($place->getName());
        }
        foreach ($workflow->getTransitions() as $transition) {
            $tos = [];
            foreach ($transition->getTos() as $to) {
                $tos[] = $to->getName();
            }
            $froms = [];
            foreach ($transition->getFroms() as $from) {
                $froms[] = $from->getName();
            }
            $definitionBuilder->addTransition(new Transition($transition->getName(), $froms, $tos));
        }


        $definition = $definitionBuilder->build();

        $dumper = new PlantUmlDumper(PlantUmlDumper::WORKFLOW_TRANSITION);
        $dump = $dumper->dump($definition);

        $encode = \Jawira\PlantUml\encodep($dump);
        $image = "http://www.plantuml.com/plantuml/png/{$encode}";

        return $this->render('@whatwedoWorkflow/workflow/show.html.twig', [
            'workflow' => $workflow,
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wwd_workflow_workflow_edit", methods={"GET","POST"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'), workflow)")
     */
    public function edit(Request $request, Workflow $workflow): Response
    {
        $form = $this->createForm(WorkflowType::class, $workflow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wwd_workflow_workflow_index');
        }

        return $this->render('@whatwedoWorkflow/workflow/new.html.twig', [
            'workflow' => $workflow,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="wwd_workflow_workflow_delete", methods={"DELETE"})
     * @Security("is_granted(constant('\\whatwedo\\WorkflowBundle\\Security\\Roles::WORKFLOW_ADMIN'), workflow)")
     */
    public function delete(Request $request, Workflow $workflow): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workflow->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($workflow);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wwd_workflow_workflow_index');
    }
}
