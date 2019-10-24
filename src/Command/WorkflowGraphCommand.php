<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 20.05.18
 * Time: 16:19
 */

namespace whatwedo\WorkflowBundle\Command;


use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\WorkflowBundle\Entity\PlaceEventDefinition;
use whatwedo\WorkflowBundle\Entity\Transition;
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

        $places = [];

        foreach ($workflow->getPlaces() as $place) {
            $places[$place->getId()] = $graph->createVertex($place->getName());
            $places[$place->getId()]->setAttribute('graphviz.color', 'blue');
        }

        $transitions = [];

        /** @var Transition $transition */
        foreach ($workflow->getTransitions() as $transition) {
            $transitions[$transition->getId()]['vertex'] = $graph->createVertex($transition->getName());
            $transitions[$transition->getId()]['vertex']->setAttribute('graphviz.color', 'red');

            /** @var Place $from */
            foreach ($transition->getFroms() as $from) {
                $transitions[$transition->getId()]['edge1'] = $place[$from->getId()]->createEdgeTo($transitions[$transition->getId()]['vertex']);
                $transitions[$transition->getId()]['edge1']->setAttribute('graphviz.color', 'grey');
            }

            /** @var Place $to */
            foreach ($transition->getTos() as $to) {
                $transitions[$transition->getId()]['edge2'] = $transitions[$transition->getId()]['vertex']->createEdgeTo($place[$to->getId()]);
                $transitions[$transition->getId()]['edge2']->setAttribute('graphviz.color', 'grey');
            }

        }

        $blue = $graph->createVertex('blue');
        $blue->setAttribute('graphviz.color', 'blue');

        $red = $graph->createVertex('red');
        $red->setAttribute('graphviz.color', 'red');



        $edge = $blue->createEdgeTo($red);
        $edge->setAttribute('graphviz.color', 'grey');

        $graphviz = new \Graphp\GraphViz\GraphViz();
        $image = $graphviz->createImageSrc($graph);
        $o = 0;


    }
}
