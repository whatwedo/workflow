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
use whatwedo\WorkflowBundle\EventHandler\PlaceEventHandlerAbstract;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class WorkflowCheckCommand extends Command
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
            ->setName('whatwedo:workflow:check')
            ->setDescription('check workflow Places')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkPlaceDefintions = $this->workflowManager->getCheckPlaceDefnitions();

        /** @var PlaceEventDefinition $checkPlaceDefintion */
        foreach ($checkPlaceDefintions as $checkPlaceDefintion) {

            if ( !empty($checkPlaceDefintion->getEventSubscriber()) ) {
                $supportedEntities = $checkPlaceDefintion->getPlace()->getWorkflow()->getSupports();

                foreach ($supportedEntities as $supportedEntity) {
                    $checkPlaceEntities = $this->workflowManager->getEntitiesInPlace($supportedEntity, $checkPlaceDefintion->getPlace()->getName());

                    foreach ($checkPlaceEntities as $checkPlaceEntity) {
                        /** @var PlaceEventDefinition $eventSubscriberClass */
                        $eventSubscriberClass = $checkPlaceDefintion->getEventSubscriber();
                        /** @var PlaceEventHandlerAbstract $workflowSubscriber */
                        $workflowSubscriber = $this->container->get($eventSubscriberClass);

                        $success = $workflowSubscriber->run($checkPlaceEntities, $checkPlaceDefintion);

                        $result = true;
                    }
                }
            }


        }

    }
}
