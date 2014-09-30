<?php

namespace DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Matheo\UserBundle\Entity\User;


class LoadUserData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     *  {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $users = [
            [
                'username' => 'webmaster',
                'password' => 'webmaster',
                'email' => 'webmaster@localhost',
                'group' => 'group-su'
            ],
            [
                'username' => 'admin',
                'password' => 'admin',
                'email' => 'admin@localhost',
                'group' => 'group-adm'
            ]
        ];

        $userManager = $this->container->get('fos_user.user_manager');

        foreach ($users as $user) {
            /** @var User $user */
            $user = $userManager->createUser();

            $user
                ->setUsername($user['username'])
                ->setPlainPassword($user['password'])
                ->setEmail($user['email'])
                ->addGroup($this->getReference($user['group']))
                ->setEnabled(true);

            $userManager->updateUser($user);
        }

        // flush
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
