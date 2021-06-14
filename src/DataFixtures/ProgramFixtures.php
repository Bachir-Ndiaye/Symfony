<?php

namespace App\DataFixtures;

use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for($i=0; $i<5; $i++){
            $program = new Program();
            $program->setTitle('Walking dead'.$i)
                ->setSynopsis('Des zombies envahissent la terre'.$i)
                ->setPoster('Image walking dead...'.$i)
                ->setCountry('US'.$i)
                ->setYear('1978'.$i)
                ->setCategory($this->getReference('category_'.$i));
                $this->addReference('program_' . $i, $program);
                $program->addActor($this->getReference('actor_' . $i));
                $manager->persist($program);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Return all dependencies needed by a Program
        return [
            ActorFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
