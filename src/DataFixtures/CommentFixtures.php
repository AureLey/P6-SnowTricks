<?php
//src/DataFixtures/CommentFixtures.php

namespace App\DataFixtures;


use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        //Picture
        $tricks = [
            "50-50", "boardslide", "Lipslide", "Back Flip", "Front Flip",
            "Wildcat", "Tamedog", "Backside Misty", "Beef Carpaccio", "Beef Curtains",
            "Bloody Dracula", "Drunk Driver", "Japan Air",
        ];

        foreach ($tricks as $value) {

            for ($j = 0; $j <= rand(1, 4); $j++) {
                $now = new \DateTimeImmutable('now');
                $comment = new Comment();
                $comment->setContent("Lorem ipsum, dolor sit amet consectetur adipisicing elit.");
                $comment->setCreatedAt($now);
                $comment->setCommentUser($this->getReference(AppFixtures::ADMIN_USER_REFERENCE));
                $comment->setCommentTrick($this->getReference($value));

                $manager->persist($comment);
            }
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
