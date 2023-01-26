<?php

//src/DataFixtures/VideoFixtures.php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Picture
        $pictures = [

            "50-50" => ["50-50.jpg"],

            "boardslide" => ["boardslide.jpg"],

            "Lipslide" => ["lipslide.jpg"],

            "Back Flip" => ["backflip.jpg"],

            "Front Flip" => ["frontflip.jpg"],

            "Wildcat" => ["wildcat.jpg"],

            "Tamedog" => ["tamedog.jpg"],

            "Backside Misty" => ["misty.jpg"],

            "Beef Carpaccio" => ["beefcarpaccio.jpg"],

            "Beef Curtains" => ["beefcurtains.jpg"],

            "Bloody Dracula" => ["bloodydracula.jpg"],

            "Drunk Driver" => ["drunk.jpg"],

            "Japan Air" => ["japan.jpg"],
        ];
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
