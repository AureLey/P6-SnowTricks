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

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * load.
     */
    public function load(ObjectManager $manager): void
    {
        // Files with data in array
        require 'DataTricks.php';

        foreach ($tricks as $key => $value) {
            for ($j = 0; $j <= rand(1, 4); ++$j) {
                $now = new \DateTime('now');
                $comment = new Comment();
                $comment->setContent('Lorem ipsum, dolor sit amet consectetur adipisicing elit.');
                $comment->setCreatedAt($now);
                $comment->setCommentUser($this->getReference(AppFixtures::ADMIN_USER_REFERENCE));
                $comment->setCommentTrick($this->getReference($key));

                $manager->persist($comment);
            }
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
