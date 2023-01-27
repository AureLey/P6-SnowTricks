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

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    private $hasher;

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
        $user->setRoles(['ROLE_ADMIN']);
        $password = $this->hasher->hashPassword($user, $admin_password);
        $user->setPassword($password);

        $manager->persist($user);
        $this->addReference(self::ADMIN_USER_REFERENCE, $user);

        $manager->flush();
    }
}
