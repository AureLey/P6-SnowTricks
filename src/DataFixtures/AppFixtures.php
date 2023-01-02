<?php

//src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;



use App\Entity\User;
use App\Entity\Group;
use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    
    public function load(ObjectManager $manager): void
    { 
        // USER Creation
        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@admin.com');
        $user->setRole('ROLE_ADMIN');
        $user->setPassword('NoHashingPassword');

        $manager->persist($user);
        $this->addReference(self::ADMIN_USER_REFERENCE, $user);
                
        $manager->flush();
    }  
}
