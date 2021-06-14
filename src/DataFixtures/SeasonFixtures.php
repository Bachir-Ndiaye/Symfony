<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $season = new Season();
            $season->setNumber($i)
            ->setYear('1500' . $i)
            ->setDescription('Description...' . $i)
            ->setProgram($this->getReference('program_0'));
            $this->addReference('season_'.$i, $season);
            $manager->persist($season);
        }    
        $manager->flush();
    }

    public function getDependencies()
    {
        // Return all dependencies needed by a Program
        return [
            ProgramFixtures::class,
        ];
    }
}
