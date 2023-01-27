<?php

declare(strict_types=1);

/*
 * This file is part of ...
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
    public function load(ObjectManager $manager): void
    {
        // VIDEOS
        $videos = [
            '50-50' => ['https://www.youtube.com/embed/kxZbQGjSg4w'],

            'boardslide' => ['https://www.youtube.com/embed/R3OG9rNDIcs'],

            'Lipslide' => ['https://www.youtube.com/embed/LSVn5aI56aU'],

            'Back Flip' => ['https://www.youtube.com/embed/0sehBOkD01Q'],

            'Front Flip' => ['https://www.youtube.com/embed/qvnsjVJCbA0'],

            'Wildcat' => ['https://www.youtube.com/embed/7KUpodSrZqI'],

            'Tamedog' => ['https://www.youtube.com/embed/qvnsjVJCbA0'],

            'Backside Misty' => ['https://www.youtube.com/embed/yMvDA7FEWjk'],

            'Beef Carpaccio' => ['https://www.youtube.com/embed/5ylWnm4rF1o'],

            'Beef Curtains' => ['https://www.youtube.com/embed/5ylWnm4rF1o'],

            'Bloody Dracula' => ['https://www.youtube.com/embed/UU9iKINvlyU'],

            'Drunk Driver' => ['https://www.youtube.com/embed/f9FjhCt_w2U'],

            'Japan Air' => ['https://www.youtube.com/embed/YAElDqyD-3I'],
        ];

        foreach ($videos as $trickname => $video_path) {
            $video = new Video();
            $video->setName($video_path[0]);
            $video->setTrick($this->getReference($trickname));

            $manager->persist($video);
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
