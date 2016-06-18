<?php

/*
 * This file is part of the Sententiaregum project.
 *
 * (c) Maximilian Bosch <maximilian.bosch.27@gmail.com>
 * (c) Ben Bieler <benjaminbieler2014@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ma27\ApiKeyAuthenticationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass which gathers hasher services and keeps them inside a private parameter.
 * The configuration can declare which hash service to use and this one will be fetched from the parameter.
 *
 * @internal
 */
class CompileHasherServicesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \LogicException If multiple tags at one service exist.
     * @throws \LogicException If no appropriate tag is given.
     * @throws \LogicException If no alias is given.
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ma27_api_key_authentication.password_hashing_service')) {
            return;
        }

        $alias = $container->getParameter('ma27_api_key_authentication.password_hashing_service');
        foreach ($container->findTaggedServiceIds('ma27_api_key_authentication.password_hasher') as $id => $tags) {
            if (count($tags) > 1) {
                throw new \LogicException(sprintf(
                    'Service "%s" can have the tag "%s" only one time!',
                    $id,
                    'ma27_api_key_authentication.password_hasher'
                ));
            }

            if (!array_key_exists('alias', $tags[0])) {
                throw new \LogicException(sprintf(
                    'The tag "%s" on service "%s" needs an `alias` property!',
                    'ma27_api_key_authentication.password_hasher',
                    $id
                ));
            }

            if ($tags[0]['alias'] === $alias) {
                $container->setAlias('ma27_api_key_authentication.password.strategy', $id);

                return;
            }
        }

        throw new \LogicException(sprintf(
            'No service found for hashing alias "%s"!',
            $alias
        ));
    }
}
