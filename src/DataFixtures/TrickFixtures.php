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

use App\Entity\Trick;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Files with data in array
        require 'DataTricks.php';

        // TRICK Creation

        foreach ($tricks as $trickname => $trickproperty) {
            $now = new \DateTime('now');

            $trick = new Trick();
            $trick->setName($trickname)
                ->setSlug($trickproperty[0])
                ->setContent($trickproperty[1])
                ->setCreatedAt($now)
                ->setUpdatedAt($now)
                ->setgroupTrick($this->getReference($trickproperty[2]))
                ->setFeaturedImage($trickproperty[3])
                ->setUser($this->getReference(AppFixtures::ADMIN_USER_REFERENCE));

            $manager->persist($trick);
            $this->addReference($trickname, $trick);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GroupFixtures::class,
        ];
    }
}
