<?php

namespace Matheo\UserBundle\Role;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\Role\Role;


/**
 * RoleHierarchy defines a role hierarchy.
 */
class RoleHierarchy implements RoleHierarchyInterface
{
    /**
     * @var array
     */
    protected $tree;



    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        /** @var \Gedmo\Tree\RepositoryUtilsInterface $repo */
        $repo = $em->getRepository('MatheoUserBundle:Rol');

        $tree = $repo->childrenHierarchy();

        $this->buildTree($tree[0]);
    }

    /**
     * Recursive processing of each nested set node.
     *
     * @param array $node
     */
    protected function buildTree($node)
    {
        $children = [];

        foreach ($node['__children'] as $role) {
            $children[] = $role['role'];
            $this->buildTree($role);
        }

        $this->tree[$node['role']] = [
            'name' => $node['name'],
            'children' => $children
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getReachableRoles(array $roles)
    {
        $auth = [];

        foreach ($roles as $role) {
            $role = ($role instanceof RoleInterface ? $role->getRole() : (string)$role);

            $this->getReachableChilds($role, $auth);
        }

        return $auth;
    }

    /**
     * Recursive collection of authorized roles.
     *
     * @param string $role
     * @param array  $auth
     */
    protected function getReachableChilds($role, &$auth)
    {
        $auth[] = new Role($role);

        if (isset($this->tree[$role]['children'])) {
            foreach ($this->tree[$role]['children'] as $child) {
                $this->getReachableChilds($child, $auth);
            }
        }
    }
}
