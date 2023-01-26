<?php

//src/DataFixtures/TrickFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Group;
use App\Entity\Video;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Repository\GroupRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TrickFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Data Tricks Array,each trick get an array with property
        $tricks = [

            "50-50" => [
                "50-50",
                "A slide in which a snowboarder rides straight along a rail or other obstacle. 
                                This trick has its origin in skateboarding, where the trick is performed with both skateboard trucks
                                grinding along a rail.",
                "Slide",
                "50-50.jpg",
            ],

            "boardslide" => [
                "boardslide",
                "A slide performed where the riders leading foot passes over the rail on approach, 
                                with their snowboard traveling perpendicular along the rail or other obstacle. 
                                When performing a frontside boardslide, the snowboarder is facing uphill. 
                                When performing a backside boardslide, a snowboarder is facing downhill. 
                                This is often confusing to new riders learning the trick because with a frontside boardslide 
                                you are moving backward and with a backside boardslide you are moving forward.",
                "Slide",
                "boardslide.jpg",
            ],


            "Lipslide" => [
                "lipslide",
                "A slide performed where the rider's trailing foot passes over the rail on approach, 
                                with their snowboard traveling perpendicular along the rail or other obstacle. 
                                When performing a frontside lipslide, the snowboarder is facing downhill. 
                                When performing a backside lipslide, a snowboarder is facing uphill.",
                "Slide",
                "lipslide.jpg",
            ],


            "Back Flip" => [
                "back-flip",
                "Flipping backward off of a jump.",
                "Flip",
                "backflip.jpg",
            ],


            "Front Flip" => [
                "front-flip",
                "Flipping forward off of a jump.",
                "Flip",
                "frontflip.jpg"
            ],


            "Wildcat" => [
                "wildcat",
                "A backflip performed on a straight jump, 
                                with an axis of rotation in which the snowboarder flips in a cartwheel-like fashion, 
                                over the tail of their snowboard.",
                "Flip",
                "wildcat.jpg",
            ],


            "Tamedog" => [
                "tamedog",
                "A frontflip performed on a straight jump, with an axis of rotation in which the snowboarder flips in a forward,
                                cartwheel-like fashion over the nose of their snowboard.",
                "Flip",
                "tamedog.jpg",
            ],


            "Backside Misty" => [
                "backside-misty",
                "After a rider learns the basic backside 540 off the toes, the Misty Flip can be an easy next progression step.
                                Misty Flip is quite different than the backside rodeo, because instead of corking over the heel edge with a back flip motion,
                                the Misty corks off the toe edge specifically and has more of a Front Flip in the beginning of the trick, followed by a side flip coming out to the landing.",
                "Flip",
                "misty.jpg",
            ],


            "Beef Carpaccio" => [
                "beef-carpaccio",
                "A Roast Beef and Chicken Salad (in between the legs) at the same time with hands crossed.",
                "Grab",
                "beefcarpaccio.jpg",
            ],


            "Beef Curtains" => [
                "beef-curtains",
                "A Roast Beef and Grosman (in between the legs) at the same time. Also known as The King or Steak Tar Tar",
                "Grab",
                "beefcurtains.jpg",
            ],


            "Bloody Dracula" => [
                "bloody-dracula",
                "A trick in which the rider grabs the tail of the board with both hands. The rear hand grabs the board as it would do it during a regular tail-grab but the front hand blindly reaches for the board behind the riders back.",
                "Grab",
                "bloodydracula.jpg",
            ],


            "Drunk Driver" => [
                "drunk-driver",
                "Similar to a Truck driver, it is a stalefish grab and mute grab performed at the same time.",
                "Grab",
                "drunk.jpg",
            ],


            "Japan Air" => [
                "japan-air",
                "The front hand grabs the toe edge in between the feet and the front knee is pulled to the board.",
                "Grab",
                "japan.jpg",
            ],
        ];

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
