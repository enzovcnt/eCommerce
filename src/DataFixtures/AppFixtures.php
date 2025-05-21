<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName($faker->word());
            $product->setDescription($faker->text());
            $product->setPrice($faker->randomFloat(2, 10, 100));
            $product->setStock($faker->randomNumber());
            $product->setDate($faker->dateTime());
            $manager->persist($product);
        }
        $manager->flush();
    }
}
