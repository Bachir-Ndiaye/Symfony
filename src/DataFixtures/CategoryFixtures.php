<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


class CategoryFixtures extends Fixture
{
    const CAREGORIES = [
        'Action',
        'Aventure',
        'Animation',
        'Fantastique',
        'Indy',
        'Horreur',
        'Science Fiction'
    ];
    public function load(ObjectManager $manager)
    {
        foreach(self::CAREGORIES as $key => $categoryName){
            $category = new Category();
            $category->setName($categoryName);
            $this->addReference('category_'.$key, $category);
            $manager->persist($category);
        }
        $manager->flush();
    }
}
