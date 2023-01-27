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

use App\Entity\Group;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends Fixture implements DependentFixtureInterface
{    
    /**
     * load
     *
     * @param  ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // GROUP CREATION
        $group = ['Flip', 'Slide', 'Grab'];

        foreach ($group as $value) {
            $groupItem = new Group();
            $groupItem->setName($value);

            $manager->persist($groupItem);
            $this->addReference($value, $groupItem);
        }

        $manager->flush();
    }
    
    /**
     * getDependencies
     *
     * @return void
     */
    public function getDependencies()
    {
        return [
            AppFixtures::class,
        ];
    }
}
