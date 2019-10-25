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
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\PlaceEventDefinition;
use whatwedo\WorkflowBundle\EventHandler\PlaceEventHandlerAbstract;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class WorkflowCheckCommand extends Command
{
    /** @var RegistryInterface */
    private $doctrine;

    /** @var WorkflowManager */
    private $workflowManager;

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
        /** @var EventDefinition[] $eventDefintions */
        $eventDefintions = $this->workflowManager->getCheckPlaceDefnitions();

        /** @var PlaceEventDefinition $eventDefintion */
        foreach ($eventDefintions as $eventDefintion) {

            if ( !empty($eventDefintion->getE()) ) {
                $supportedEntities = $eventDefintion->getPlace()->getWorkflow()->getSupports();

                foreach ($supportedEntities as $supportedEntity) {
                    $checkPlaceEntities = $this->workflowManager->getEntitiesInPlace($supportedEntity, $eventDefintion->getPlace()->getName());

                    foreach ($checkPlaceEntities as $checkPlaceEntity) {
                        /** @var PlaceEventHandlerAbstract $eventHandler */
                        if ($eventHandler = $this->workflowManager->getEventHandler($eventDefintion, EventDefinition::CHECK))  {
                            $success = $eventHandler->run($checkPlaceEntities, $eventDefintion);
                            $result = true;
                        }
                    }
                }
            }
        }

    }
}
