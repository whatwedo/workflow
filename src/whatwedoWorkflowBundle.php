<?php

namespace whatwedo\WorkflowBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use whatwedo\WorkflowBundle\DependencyInjection\Compiler\WorkflowPass;
use whatwedo\WorkflowBundle\DependencyInjection\WorkflowBundleExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class whatwedoWorkflowBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new WorkflowPass());
    }

    public function getContainerExtension()
    {
        return new WorkflowBundleExtension();
    }
}
