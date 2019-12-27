<?php

namespace whatwedo\WorkflowBundle\Twig;

use http\Env\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Workflow\Registry;
use whatwedo\WorkflowBundle\EventListener\WorkflowSubscriber;
use whatwedo\WorkflowBundle\Manager\WorkflowManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use whatwedo\WorkflowBundle\Entity\Workflow;
use whatwedo\WorkflowBundle\Entity\Workflowable;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Psr\Container\ContainerInterface;

class WorkflowExtension extends AbstractExtension
{

    /** @var \whatwedo\WorkflowBundle\Manager\WorkflowManager */
    private $workflowManager;
    
    /** @var \whatwedo\WorkflowBundle\Service\WorkflowService */
    private $workflowRegistry;

    /** @var RouterInterface */
    private $router;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param WorkflowManager $workflowManager
     * @required
     */
    public function setWorkflowManager(WorkflowManager $workflowManager): void
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @param \whatwedo\WorkflowBundle\Service\WorkflowService $workflowRegistry
     * @required
     */
    public function setWorkflowRegistry(\whatwedo\WorkflowBundle\Service\WorkflowService $workflowRegistry): void
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    /**
     * @param RouterInterface $router
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @param RequestStack $requestStack
     * @required
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('wwd_workflow_buttons', [$this, 'workflowButtons']),
        ];
    }

    public function workflowButtons(Workflowable $entity)
    {
        /** @var Workflow[] $workflows */
        $workflows = $this->workflowManager->getWorkflowsForEntity($entity);


        $result = '';
        if ($workflows) {

            foreach ($workflows as $workflowItem) {
                foreach ($workflowItem->getTransitions() as $transition) {

                    /** @var \Symfony\Component\Workflow\Workflow $workflow */
                    $workflow = $this->workflowRegistry->get($entity, $workflowItem->getName());
                    if ($workflow->can($entity, $transition->getName())) {

                        $pathInfo  = $this->requestStack->getCurrentRequest()->getPathInfo();

                        $parameters = [
                            'workflow' => $workflowItem->getId(),
                            'transition' =>  $transition->getId(),
                            'subjectClass' => get_class($entity),
                            'subjectId' => $entity->getId(),
                            'ref' => $pathInfo
                        ];

                        $applyUrl = $this->router->generate('wwd_workflow_apply', $parameters);
                        $result .= sprintf('<a href="%s" class="btn btn-primary">%s</a>', $applyUrl,  $transition->getName());
                    }
                }
            }
        }

        return $result;

    }

}