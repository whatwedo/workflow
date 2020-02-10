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
            $places[$place->getId()]->setAttribute('graphviz.shape', 'record');
            $places[$place->getId()]->setAttribute('graphviz.fillcolor', 'black');
            $places[$place->getId()]->setAttribute('graphviz.fontcolor', 'white');
            $places[$place->getId()]->setAttribute('graphviz.color', 'white');
            $places[$place->getId()]->setAttribute('graphviz.style', 'rounded, filled');
            $rawData = '"{';
            $first = false;
            /** @var EventDefinition $eventDefinition */
            foreach ($place->getEventDefinitions(EventDefinition::ENTER) as $eventDefinition) {
                $rawData .= ($first?'|':''). ' ' . strtoupper($eventDefinition->getEventName()) . '\n' . addslashes($eventDefinition->getName());
                $first = true;
            }
            foreach ($place->getEventDefinitions(EventDefinition::ENTERED) as $eventDefinition) {
                $rawData .= ($first?'|':''). ' ' . strtoupper($eventDefinition->getEventName()) . '\n' . addslashes($eventDefinition->getName());
                $first = true;
            }
            $rawData .= ($first?'|':'').'\N';
            foreach ($place->getEventDefinitions(EventDefinition::LEAVE) as $eventDefinition) {
                $rawData .= ($first?'|':'').' ' . strtoupper($eventDefinition->getEventName()) . '\n' . addslashes($eventDefinition->getName());
                $first = true;
            }
            $rawData .= '}"';

            $places[$place->getId()]->setAttribute('graphviz.label', GraphViz::raw($rawData));
        }

        /** @var Vertex[] $transitions */
        $transitions = [];

        /** @var \whatwedo\WorkflowBundle\Entity\Transition $transition */
        foreach ($workflow->getTransitions() as $transition) {
            $hasGuard = false;

            foreach ($transition->getEventDefinitions(EventDefinition::GUARD) as $eventDefinition) {
                $transitions[$transition->getId()]['guard'] = $graph->createVertex($eventDefinition->getName());
                $transitions[$transition->getId()]['guard']->setAttribute('graphviz.shape', 'diamond');
                $hasGuard = true;
            }

            $transitions[$transition->getId()]['vertex'] = $graph->createVertex($transition->getName());
            $transitions[$transition->getId()]['vertex']->setAttribute('graphviz.shape', 'record');
            $rawData = '"{';
            $first = false;
            /** @var EventDefinition $eventDefinition */
            foreach ($transition->getEventDefinitions(EventDefinition::ANNOUNCE) as $eventDefinition) {
                $rawData .= ($first?'|':'').' ' .  strtoupper($eventDefinition->getEventName()) .' '. $eventDefinition->getName() . '';
                $first = true;
            }
            foreach ($transition->getEventDefinitions(EventDefinition::TRANSITION) as $eventDefinition) {
                $rawData .= ($first?'|':'').' ' .  strtoupper($eventDefinition->getEventName()) .' '. $eventDefinition->getName() . '';
                $first = true;
            }
            $rawData .= ($first?'|':'').'\N';
            foreach ($transition->getEventDefinitions(EventDefinition::COMPLETED) as $eventDefinition) {
                $rawData .= ($first?'|':'').' ' .  strtoupper($eventDefinition->getEventName()) .' '. $eventDefinition->getName() . '';
                $first = true;
            }
            $rawData .= '}"';
            $transitions[$transition->getId()]['vertex']->setAttribute('graphviz.label', GraphViz::raw($rawData));



            /** @var \whatwedo\WorkflowBundle\Entity\Place $from */
            foreach ($transition->getFroms() as $from) {
                if (!$hasGuard) {
                    $transitions[$transition->getId()]['edge1'] = $places[$from->getId()]->createEdgeTo($transitions[$transition->getId()]['vertex']);
                } else {
                    $transitions[$transition->getId()]['edge1guard'] = $transitions[$transition->getId()]['guard']->createEdgeTo($transitions[$transition->getId()]['vertex']);
                    $transitions[$transition->getId()]['edge2guard'] = $places[$from->getId()]->createEdgeTo($transitions[$transition->getId()]['guard']);
                }
            }

            /** @var \whatwedo\WorkflowBundle\Entity\Place $to */
            foreach ($transition->getTos() as $to) {
                if ($hasGuard) {
                    $transitions[$transition->getId()]['edge2'] = $transitions[$transition->getId()]['vertex']->createEdgeTo($places[$to->getId()]);
                }

            }


        }

        $graphviz = new \Graphp\GraphViz\GraphViz();

        if (isset($_ENV['DOT_BIN'])) {
            $graphviz->setExecutable($_ENV['DOT_BIN']);
        }
        $graphviz->setFormat('svg');
        try {
            $image = $graphviz->createImageSrc($graph);
        } catch (\Exception $ex) {
            $image = null;
        }

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
