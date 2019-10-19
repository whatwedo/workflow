<?php


namespace whatwedo\WorkflowBundle\Controller;

use Socius\Entity\Workflow\Transition;
use Socius\Entity\Workflow\Workflow;
use Socius\Service\WorkflowService;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

class ApplyController extends AbstractController
{
    /** @var WorkflowService */
    private $workflowRegistry;


    /** @var RegistryInterface */
    private $doctirine;

    /**
     * @param RegistryInterface $doctirine
     * @required
     */
    public function setDoctirine(RegistryInterface $doctirine): void
    {
        $this->doctirine = $doctirine;
    }

    /**
     * @param WorkflowService $workflowRegistry
     * @required
     */
    public function setWorkflowRegistry(WorkflowService $workflowRegistry): void
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @Route("/workflow/apply/{workflow}/{transition}/{subjectClass}/{subjectId}", name="workflow_apply", methods={"GET"})
     * @param Request $request
     * @param Workflow $workflow
     * @param Transition $transition
     * @param string $subjectClass
     * @param string $subjectId
     * @return Response
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apply(Request $request, Workflow $workflow, Transition $transition, string $subjectClass, string $subjectId): Response
    {
        $subject = $this->doctirine->getRepository($subjectClass)->findById($subjectId);

        $wf =  $this->workflowRegistry->get($subject, $workflow->getName());
        $wf->apply($subject, $transition->getName(), ['foo' => 'bar']);

        $this->doctirine->getManager()->persist($subject);
cd v        $this->doctirine->getManager()->flush();

        if ($request->query->has('ref')) {
            return $this->redirect($request->query->get('ref'));
        }

        return $this->redirectToRoute('home');
    }

}