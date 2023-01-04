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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';


    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void
    {         
        // USER Creation
        $admin_password = 'admin';

        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@admin.com');
        $user->setRoles(["ROLE_ADMIN"]);
        $password = $this->hasher->hashPassword($user, $admin_password);
        $user->setPassword($password);

        $manager->persist($user);
        $this->addReference(self::ADMIN_USER_REFERENCE, $user);
                
        $manager->flush();
    }  
}
