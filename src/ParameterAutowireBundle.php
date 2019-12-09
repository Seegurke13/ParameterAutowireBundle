<?php


namespace ParameterAutowireBundle;


use ParameterAutowireBundle\DependencyInjection\ParameterAutowirePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ParameterAutowireBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ParameterAutowirePass($container->getParameterBag()), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}