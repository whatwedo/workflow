<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 20.05.18
 * Time: 16:19
 */

namespace whatwedo\WorkflowBundle\Command;


use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\WorkflowBundle\Entity\Transition;
use whatwedo\WorkflowBundle\Entity\Place;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class WorkflowGraphCommand extends Command
{
    /** @var RegistryInterface */
    private $doctrine;

    /** @var WorkflowManager */
    private $workflowManager;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface|null $container
     * @required
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param WorkflowManager $workflowManager
     * @required
     */
    public function setWorkflowManager(WorkflowManager $workflowManager): void
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param RegistryInterface $doctrine
     * @required
     */
    public function setDoctrine(RegistryInterface $doctrine): void
    {
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->setName('whatwedo:workflow:graph')
            ->setDescription('draw workflow Graph')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var Workflow $workflow */
        $workflow = $this->doctrine->getRepository(Workflow::class)->find(1);

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

        /** @var Transition $transition */
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
        $o = 0;


    }
}
