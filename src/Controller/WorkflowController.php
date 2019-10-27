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

/**
 * @Route("/wwd/workflow/workflow")
 */
class WorkflowController extends AbstractController
{
    /**
     * @Route("/", name="wwd_workflow_workflow_index", methods={"GET"})
     */
    public function index(WorkflowRepository $workflowRepository): Response
    {
        return $this->render('@whatwedoWorkflow/workflow/index.html.twig', [
            'workflows' => $workflowRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="wwd_workflow_workflow_new", methods={"GET","POST"})
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

        $singleState = true; // true if the subject can be in only one state at a given time
        $property = 'currentState'; // subject property name where the state is stored
//        $marking = new MethodMarkingStore($singleState, $property);
        $marking = new Marking();



        $graph = new \Fhaculty\Graph\Graph();
        $graph->setAttribute('landscape', true);
        $graph->setAttribute('splines', 'curved');

        /** @var Vertex[] $places */
        $places = [];

        foreach ($workflow->getPlaces() as $place) {
            $places[$place->getId()] = $graph->createVertex($place->getName());
            $places[$place->getId()]->setAttribute('graphviz.shape', 'box');
            $places[$place->getId()]->setAttribute('graphviz.fillcolor', 'black');
            $places[$place->getId()]->setAttribute('graphviz.fontcolor', 'white');
            $places[$place->getId()]->setAttribute('graphviz.style', 'rounded, filled');
            $rawData = '<<table cellspacing="0" border="0" cellborder="0">
                  <tr><td><b><u>\N</u></b></td></tr>';
            /** @var EventDefinition $eventDefinition */
            foreach ($place->getEventDefinitions() as $eventDefinition) {
                $rawData .= '<tr><td><sub>' . strtoupper($eventDefinition->getEventName()) . '</sub></td></tr>';
            }
            $rawData .= '</table>>';
            $places[$place->getId()]->setAttribute('graphviz.label', GraphViz::raw($rawData));
        }

        /** @var Vertex[] $transitions */
        $transitions = [];

        /** @var \whatwedo\WorkflowBundle\Entity\Transition $transition */
        foreach ($workflow->getTransitions() as $transition) {
            $transitions[$transition->getId()]['vertex'] = $graph->createVertex($transition->getName());
            $rawData = '<<table cellspacing="0" border="0" cellborder="0">
                <tr><td><b><u>\N</u></b></td></tr>                
                ';
            /** @var EventDefinition $eventDefinition */
            foreach ($transition->getEventDefinitions() as $eventDefinition) {
                $rawData .= '<tr><td><sub>' . strtoupper($eventDefinition->getEventName()) . '</sub></td></tr>';
            }
            $rawData .= '</table>>';
            $transitions[$transition->getId()]['vertex']->setAttribute('graphviz.label', GraphViz::raw($rawData));

            /** @var Place $from */
            foreach ($transition->getFroms() as $from) {
                $transitions[$transition->getId()]['edge1'] = $places[$from->getId()]->createEdgeTo($transitions[$transition->getId()]['vertex']);
            }

            /** @var Place $to */
            foreach ($transition->getTos() as $to) {
                $transitions[$transition->getId()]['edge2'] = $transitions[$transition->getId()]['vertex']->createEdgeTo($places[$to->getId()]);
            }
        }

        $graphviz = new \Graphp\GraphViz\GraphViz();
        $image = $graphviz->createImageSrc($graph);

        return $this->render('@whatwedoWorkflow/workflow/show.html.twig', [
            'workflow' => $workflow,
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="wwd_workflow_workflow_edit", methods={"GET","POST"})
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
