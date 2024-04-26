<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('dward');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword('$2y$13$8BRjJzQNaE.Iag48Vk9D..GiA97WItoPrf3ikdOTSbOIARyfBq1Ty');

        $manager->persist($user);

        $manager->flush();
    }
}
