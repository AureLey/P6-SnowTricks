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

use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VideoFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * load.
     */
    public function load(ObjectManager $manager): void
    {
        // Files with data in array
        require 'DataTricks.php';

        foreach ($videos as $trickname => $video_path) {
            $video = new Video();
            $video->setName($video_path[0]);
            $video->setTrick($this->getReference($trickname));

            $manager->persist($video);
        }

        $manager->flush();
    }

    /**
     * getDependencies.
     *
     * @return void
     */
    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}
