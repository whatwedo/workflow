<?php
/**
 * Created by PhpStorm.
 * User: mauri
 * Date: 20.05.18
 * Time: 16:19
 */

namespace whatwedo\WorkflowBundle\Command;


use Socius\Entity\Address;
use Socius\Entity\AddressType;
use Socius\Entity\Contact;
use Socius\Entity\ContactType;
use Socius\Entity\Department;
use Socius\Entity\Group;
use Socius\Entity\Member;
use Socius\Entity\MemberAttribute;
use Socius\Entity\MemberValue;
use Socius\Entity\Role;
use Socius\Entity\Workflow\Place;
use Socius\Entity\Workflow\WorkflowLog;
use Socius\Repository\Workflow\PlaceRepository;
use Socius\Repository\Workflow\WorkflowLogRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkflowCommand extends Command
{
    /** @var RegistryInterface */
    private $doctrine;

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
            // the name of the command (the part after "bin/console")
            ->setName('whatwedo:workflow')

            // the short description shown while running "php bin/console list"
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
        /** @var PlaceRepository $placesRepo */
        $placesRepo = $this->doctrine->getRepository(Place::class);
        /** @var WorkflowLogRepository $workflowLogRepo */
        $workflowLogRepo = $this->doctrine->getRepository(WorkflowLog::class);
        $limitedPlaces = $placesRepo->findByLimited();

        /** @var Place $place */
        foreach ($limitedPlaces as $place) {

            $subjects = $this->doctrine->getRepository($place->getWorkflow()->getSupports()[0])
                ->findBy(['currentPlace' => $place->getName()]);

            foreach ($subjects as $subject) {
                $lastLog = $workflowLogRepo->getLastLog($subject);
                if ($lastLog->getDate() < new \DateTime($place->getLimit() . ' Days ago')) {
                    $o =0;
                }
            }

        }



    }




}