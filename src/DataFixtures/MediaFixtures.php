<?php
//src/DataFixtures/MediaFixtures.php

namespace App\DataFixtures;


use App\Entity\Media;
use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MediaFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {  
        //Picture
        $pictures =[

            "50-50"=>["50-50.jpg","picture"],

            "boardslide"=>["boardslide.jpg","picture"],

            "Lipslide"=>["lipslide.jpg","picture"],

            "Back Flip"=>["backflip.jpg","picture"],

            "Front Flip"=>[ "frontflip.jpg","picture"],

            "Wildcat"=>["wildcat.jpg","picture"],

            "Tamedog"=>["tamedog.jpg","picture"],

            "Backside Misty"=>["misty.jpg","picture"],

            "Beef Carpaccio"=>["beefcarpaccio.jpg","picture"],

            "Beef Curtains"=>["beefcurtains.jpg","picture"],

            "Bloody Dracula"=>["bloodydracula.jpg","picture"],

            "Drunk Driver"=>["drunk.jpg","picture"],

            "Japan Air"=>["japan.jpg","picture"],
            ];
        // VIDEOS
        $videos =[

            "50-50"=>["https://youtu.be/embed/kxZbQGjSg4w","video"],

            "boardslide"=>["https://www.youtube.com/embed/R3OG9rNDIcs","video"],

            "Lipslide"=>["https://youtu.be/embed/LSVn5aI56aU","video"],

            "Back Flip"=>["https://youtu.be/embed/0sehBOkD01Q","video"],

            "Front Flip"=>[ "https://youtu.be/embed/qvnsjVJCbA0","video"],

            "Wildcat"=>["https://youtu.be/embed/7KUpodSrZqI","video"],

            "Tamedog"=>["https://youtu.be/embed/qvnsjVJCbA0","video"],

            "Backside Misty"=>["https://youtu.be/embed/yMvDA7FEWjk","video"],

            "Beef Carpaccio"=>["https://youtu.be/embed/5ylWnm4rF1o","video"],

            "Beef Curtains"=>["https://youtu.be/embed/5ylWnm4rF1o","video"],

            "Bloody Dracula"=>["https://youtu.be/embed/UU9iKINvlyU","video"],

            "Drunk Driver"=>["https://youtu.be/embed/f9FjhCt_w2U","video"],

            "Japan Air"=>["https://youtu.be/embed/YAElDqyD-3I","video"],
          ];
        
        
        
        
        $this->SetMedias($pictures,'picture',$manager);
        $this->SetMedias($videos,'video',$manager);        
        
        $manager->flush();
    }
    private function SetMedias(Array $media,string $type,ObjectManager $manager)
    {
        foreach($media as $trickname =>$mediaproperty)
        {   
            $media = new Media();
            $media->setLink($mediaproperty[0]);
            $media->setMediaTrick($this->getReference($trickname));
            
            if($type === 'picture')
            {
                $media->setType('picture');               
            }
            else
            {
                $media->setType('video');
            }

            $manager->persist($media);
        }

    }
    public function getDependencies()
    {
        return [
            TrickFixtures::class,
        ];
    }
}