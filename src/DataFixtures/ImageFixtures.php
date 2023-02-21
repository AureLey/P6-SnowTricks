<?php

declare(strict_types=1);

/*
 * This file is part of Snowtricks
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Files with data in array
        require 'DataTricks.php';

        foreach ($pictures as $trickname => $image_path) {
            $image = new Image();
            $image->setName($image_path[0]);
            $image->setTrick($this->getReference($trickname));

            $manager->persist($image);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
