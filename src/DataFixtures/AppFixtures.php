<?php

namespace App\DataFixtures;



use App\Entity\User;
use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        define("IDUSER",0);// represent the User who add all first tricks
        
        // GROUP CREATION
        $group =["Flip", "Slide","Grab"];     
        
        //Data Tricks Array,each trick get an array with property
        $tricks =[

            "50-50"=>[          "50-50",
                                "A slide in which a snowboarder rides straight along a rail or other obstacle. 
                                This trick has its origin in skateboarding, where the trick is performed with both skateboard trucks
                                grinding along a rail.",
                                "Slide",
                    ],

            "boardslide"=>[    "bloardslide",
                                "A slide performed where the riders leading foot passes over the rail on approach, 
                                with their snowboard traveling perpendicular along the rail or other obstacle. 
                                When performing a frontside boardslide, the snowboarder is facing uphill. 
                                When performing a backside boardslide, a snowboarder is facing downhill. 
                                This is often confusing to new riders learning the trick because with a frontside boardslide 
                                you are moving backward and with a backside boardslide you are moving forward.",
                                "Slide",
                            ],


            "Lipslide"=>[       "lipslide",
                                "A slide performed where the rider's trailing foot passes over the rail on approach, 
                                with their snowboard traveling perpendicular along the rail or other obstacle. 
                                When performing a frontside lipslide, the snowboarder is facing downhill. 
                                When performing a backside lipslide, a snowboarder is facing uphill.",
                                "Slide",
                        ],


            "Back Flip"=>[      "back-flip",
                                "Flipping backward off of a jump.",
                                "Flip"
                        ],


            "Front Flip"=>[     "front-flip",
                                "Flipping forward off of a jump.",
                                "Flip"
                            ],


            "Wildcat"=>[        "wildcat",
                                "A backflip performed on a straight jump, 
                                with an axis of rotation in which the snowboarder flips in a cartwheel-like fashion, 
                                over the tail of their snowboard.",
                                "Flip"
                        ],


            "Tamedog"=>[        "tamedog",
                                "A frontflip performed on a straight jump, with an axis of rotation in which the snowboarder flips in a forward,
                                cartwheel-like fashion over the nose of their snowboard.",
                                "Flip"
                        ],


            "Backside Misty"=>[ "backside-misty",
                                "After a rider learns the basic backside 540 off the toes, the Misty Flip can be an easy next progression step.
                                Misty Flip is quite different than the backside rodeo, because instead of corking over the heel edge with a back flip motion,
                                the Misty corks off the toe edge specifically and has more of a Front Flip in the beginning of the trick, followed by a side flip coming out to the landing.",
                                "Flip"
                            ],


            "Beef Carpaccio"=>[ "beef-carpaccio",
                                "A Roast Beef and Chicken Salad (in between the legs) at the same time with hands crossed.",
                                "Grab"
                            ],


            "Beef Curtains"=>[  "beef-curtains",
                                "A Roast Beef and Grosman (in between the legs) at the same time. Also known as The King or Steak Tar Tar",
                                "Grab"
                            ],


            "Bloody Dracula"=>[ "bloody-dracula",
                                "A trick in which the rider grabs the tail of the board with both hands. The rear hand grabs the board as it would do it during a regular tail-grab but the front hand blindly reaches for the board behind the riders back.",
                                "Grab"
                            ],


            "Drunk Driver"=>[   "drunk-driver",
                                "Similar to a Truck driver, it is a stalefish grab and mute grab performed at the same time.",
                                "Grab"],


            "Japan Air"=>[      "japan-air",
                                "The front hand grabs the toe edge in between the feet and the front knee is pulled to the board.",
                                "Grab"],
            ];

        //Picture
        $pictures =[

            "50-50"=>["50-50.jpeg","picture"],

            "boardslide"=>["bloardslide.jpeg","picture"],

            "Lipslide"=>["lipslide.jpeg","picture"],

            "Back Flip"=>["backflip.jpeg","picture"],

            "Front Flip"=>[ "frontflip.jpeg","picture"],

            "Wildcat"=>["wildcat.jpeg","picture"],

            "Tamedog"=>["tamedog.jpeg","picture"],

            "Backside Misty"=>["misty.jpeg","picture"],

            "Beef Carpaccio"=>["beef carpaccio.jpeg","picture"],

            "Beef Curtains"=>["beef curtains.jpeg","pictures"],

            "Bloody Dracula"=>["bloody dracula.jpeg","picture"],

            "Drunk Driver"=>["drunk.jpeg","picture"],

            "Japan Air"=>["japan.jpeg","picture"],
            ];
        // VIDEOS
        $videos =[

            "50-50"=>["https://youtu.be/kxZbQGjSg4w","video"],

            "boardslide"=>["https://www.youtube.com/watch?v=R3OG9rNDIcs","video"],

            "Lipslide"=>["https://youtu.be/LSVn5aI56aU","video"],

            "Back Flip"=>["https://youtu.be/0sehBOkD01Q","video"],

            "Front Flip"=>[ "https://youtu.be/qvnsjVJCbA0","video"],

            "Wildcat"=>["https://youtu.be/7KUpodSrZqI","video"],

            "Tamedog"=>["https://youtu.be/qvnsjVJCbA0","video"],

            "Backside Misty"=>["https://youtu.be/yMvDA7FEWjk","video"],

            "Beef Carpaccio"=>["https://youtu.be/5ylWnm4rF1o","video"],

            "Beef Curtains"=>["https://youtu.be/5ylWnm4rF1o","video"],

            "Bloody Dracula"=>["https://youtu.be/UU9iKINvlyU","video"],

            "Drunk Driver"=>["https://youtu.be/f9FjhCt_w2U","video"],

            "Japan Air"=>["https://youtu.be/YAElDqyD-3I","video"],
          ];
            

        // USER Creation
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@admin.com');
        $user->setRole('ROLE_ADMIN');
        $user->setPassword('NoHashingPassword');

        $manager->persist($user);

        //GROUP Creation      
        foreach($group as $key =>$value)
        {   
            $groupItem = new Group();
            $groupItem->setName($value);
            
            $manager->persist($groupItem);
        }
        

        //TRICK Creation
        foreach($tricks as $trickname =>$trickproperty)
        {
            $now = new \DateTimeImmutable('now');            

            $trick = new Trick();
            $trick->setName($trickname);
            $trick->setSlug($trickproperty[0]);
            $trick->setContent($trickproperty[1]);
            $trick->setCreatedAt($now);
            $trick->setUpdatedAt($now);            
            $groupItem = new Group();
            $object = array_intersect($group,$trickproperty);
            $groupItem->setName(array_values($object)[0]);
            $trick->setgroupTrick($groupItem);       
            // $key = array_search($trickproperty[2],$group);
            // $trick->setGroupTrick($key);// souci de groupe
            $trick->setUser($user);
            $this->setMedia($trick,$manager, $pictures, $videos);

            $manager->persist($trick);            
        }

        $manager->flush();
    }

    private function setMedia(Trick $trick, ObjectManager $manager, Array $pictures, Array $videos)
    {
        // MEDIA Creation
        foreach($pictures as $trickname =>$mediaproperty)
        {
            if($trickname === $trick->getName())
            {
                $media = new Media();
                $media->setLink($mediaproperty[0]);
                $media->setType($mediaproperty[1]);
                $media->setMediaTrick($trick);

                $manager->persist($media); 
            }
            
        }

        foreach($videos as $trickname =>$mediaproperty)
        {
            if($trickname === $trick->getName())
            {
                $media = new Media();
                $media->setLink($mediaproperty[0]);
                $media->setType($mediaproperty[1]);
                $media->setMediaTrick($trick);

                $manager->persist($media);
            }
        }
    }
        
        
        
    
}
