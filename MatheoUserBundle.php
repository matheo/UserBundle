<?php

namespace Matheo\UserBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;


class MatheoUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // https://github.com/juanmf/UserBundle
        $mappings = [
            // customize the mappings to disable the 'roles' field
            realpath(__DIR__ . '/Resources/config/doctrine/model') => 'FOS\UserBundle\Model',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                $mappings, ['fos_user.model_manager_name'], 'fos_user.backend_type_orm'
            )
        );
    }
}
