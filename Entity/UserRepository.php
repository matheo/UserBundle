<?php

namespace Matheo\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;


/**
 * UserRepository
 */
class UserRepository extends EntityRepository implements UserProviderInterface
{
    /**
     * Utility override to be used by the UserManager
     *
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->select(['u', 'g'])
            ->leftJoin('u.groups', 'g');

        foreach ($criteria as $fieldName => $value) {
            $q->andWhere("u.$fieldName = :$fieldName")
              ->setParameter($fieldName, $value);
        }

        if ($orderBy) {
            foreach ($orderBy as $fieldName => $orientation) {
                $q->addOrderBy("u.$fieldName", strtoupper(trim($orientation)));
            }
        }

        return $q->getQuery()->getSingleResult();
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->select(['u', 'g', 'r'])
            ->leftJoin('u.groups', 'g')
            ->leftJoin('g.roles', 'r')
            ->where('u.username = :username OR u.email = :email')
            ->andWhere('u.enabled = 1')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q
                ->getSingleResult()
                ->loadRoles();

        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active user identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        /** @var User $user */
        return $this
            ->createQueryBuilder('u')
            ->select(['u', 'g', 'r'])
            ->join('u.groups', 'g')
            ->leftJoin('g.roles', 'r')
            ->where('u.id = :id')
            ->setParameter('id', $user->getId())
            ->getQuery()
            ->getSingleResult()
            ->loadRoles();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
