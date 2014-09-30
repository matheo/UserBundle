<?php

namespace DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Matheo\UserBundle\Entity\Rol;


class LoadRolData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     *  {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $su = new Rol('ROLE_SUPER_ADMIN');
        $su->setName('Super Admin');
        $manager->persist($su);

        $adm = new Rol('ROLE_ADMIN');
        $adm->setName("Admin")
            ->setParent($su);
        $manager->persist($adm);

        // sharing
        $this->addReference('rol-su',  $su);
        $this->addReference('rol-adm', $adm);

        // flush
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}