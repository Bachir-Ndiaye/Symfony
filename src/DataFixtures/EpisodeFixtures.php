<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    protected $slufigy;

    public function __construct(Slugify $slugify)
    {
        $this->slufigy = $slugify;
    }

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 5; $i++) {
            $episode = new Episode();
            $episode->setTitle('Titre '.$i)
                ->setNumber($i)
                ->setSynopsis('Description...' . $i)
                ->setSlug($this->slufigy->generate('Titre ' . $i))
                ->setSeason($this->getReference('season_' . $i));

            $manager->persist($episode);
        }      
        $manager->flush();
    }

    public function getDependencies()
    {
        // Return all dependencies needed by a Program
        return [
            SeasonFixtures::class,
        ];
    }
}
