<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($figureCounter = 1000; $figureCounter < 1010; $figureCounter++)
        {
            $group = new Group();
            $group->setName('group'.$figureCounter);
            $manager->persist($group);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
