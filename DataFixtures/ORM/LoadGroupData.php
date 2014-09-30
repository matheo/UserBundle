<?php

namespace DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Matheo\UserBundle\Entity\Group;


class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     *  {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $su = new Group('Webmasters');
        $su->addRole($this->getReference('rol-su'));
        $manager->persist($su);

        $adm = new Group('Admins');
        $adm->addRole($this->getReference('rol-adm'));
        $manager->persist($adm);

        // sharing
        $this->addReference('group-su',  $su);
        $this->addReference('group-adm', $adm);

        // flush
        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}