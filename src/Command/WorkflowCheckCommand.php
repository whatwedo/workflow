<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 20.05.18
 * Time: 16:19
 */

namespace whatwedo\WorkflowBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use whatwedo\WorkflowBundle\Entity\EventDefinition;
use whatwedo\WorkflowBundle\Entity\WorkflowLog;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;

class WorkflowCheckCommand extends Command
{
    /** @var \Doctrine\Persistence\ManagerRegistry */
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
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine
     * @required
     */
    public function setDoctrine(\Doctrine\Persistence\ManagerRegistry $doctrine): void
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

        /** @var EventDefinition $eventDefintion */
        foreach ($eventDefintions as $eventDefintion) {

            if ( !empty($eventDefintion->getEventHandler()) ) {
                $supportedEntities = $eventDefintion->getPlace()->getWorkflow()->getSupports();

                foreach ($supportedEntities as $supportedEntity) {
                    $checkPlaceEntities = $this->workflowManager->getEntitiesInPlace($eventDefintion->getPlace(), $supportedEntity);

                    foreach ($checkPlaceEntities as $checkPlaceEntity) {

                        if ($eventDefintion->isApplyOnce()) {

                        }

                        /** @var PlaceEventHandlerAbstract $eventHandler */
                        if ($eventHandler = $this->workflowManager->getEventHandler($eventDefintion, EventDefinition::CHECK))  {

                            $log = $this->workflowManager->getLastEventLogForEntity($checkPlaceEntity, $eventDefintion);
                            if ($eventDefintion->isApplyOnce() && $log) {

                                if (is_array($log->getData())) {
                                    if (isset($log->getData()['runned'])) {
                                        continue;
                                    }
                                }
                                $o = 0;

                            }

                            $success = $eventHandler->run($checkPlaceEntity, $eventDefintion);
                            $result = true;

                            $workflowLog = new WorkflowLog($checkPlaceEntity);
                            $workflowLog->setEventDefinition($eventDefintion);
                            $workflowLog->setSuccess($success);

                            if ($eventDefintion->isApplyOnce()) {
                                $workflowLog->setData(['runned' => new \DateTime('now')]);
                            }


                            $this->doctrine->getManager()->persist($workflowLog);
                            $this->doctrine->getManager()->flush();


                        }
                    }
                }
            }
        }

    }
}
