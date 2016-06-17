<?php

namespace Ma27\ApiKeyAuthenticationBundle;

use Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Compiler\CompileHasherServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Ma27ApiKeyAuthenticationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompileHasherServicesPass());
    }
}
