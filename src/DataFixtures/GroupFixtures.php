<?php

//src/DataFixtures/GroupFixtures.php

namespace App\DataFixtures;



use App\Entity\User;
use App\Entity\Group;
use App\Entity\Video;
use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{
    

    public function load(ObjectManager $manager): void
    { 
        // GROUP CREATION
        $group =["Flip", "Slide","Grab"];
             
        foreach($group as $key=>$value)
        {   
            $groupItem = new Group();
            $groupItem->setName($value);
            
            $manager->persist($groupItem);
            $this->addReference($value, $groupItem);
        }
        
        $manager->flush();
    }
    public function getDependencies()
    {
        return [
            AppFixtures::class,
        ];
    }  
}
