<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AchievementDefinition;
use App\Entity\Awarder;
use App\Entity\AwardTemplate;
use App\Entity\EmailTemplate;
use App\Entity\Participant;
use App\Entity\Pathway;
use App\Enums\AwarderState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        /*
        $faker = \Faker\Factory::create();
        $populator = new \Faker\ORM\Doctrine\Populator($faker, $manager);
        $populator->addEntity(Awarder::class, 2, [
            'state' => static fn(): AwarderState => AwarderState::Active,
        ]);
        $populator->addEntity(AchievementDefinition::class, 10);
        $populator->addEntity(Pathway::class, 2);
        $populator->addEntity(AwardTemplate::class, 5);
        $populator->addEntity(EmailTemplate::class, 5);
        $populator->addEntity(Participant::class, 10);

        $populator->execute();

        $manager->flush();
        */
    }
}
