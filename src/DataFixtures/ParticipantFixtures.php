<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Enums\ParticipantState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $participant = new Participant();
        $participant->setFirstName('Test1');
        $participant->setLastName('OAS1');
        $participant->setEmail('dward+1@wevad.com');
        $participant->setAcceptedTerms(true);
        $participant->setState(ParticipantState::Active);
        $participant->setSubscribedPathway($this->getReference('pathway-1'));

        $manager->persist($participant);
        $this->addReference('participant-test1', $participant);

        for ($i = 0; $i < 10; ++$i) {
            $participant = new Participant();
            $participant->setFirstName($faker->firstName());
            $participant->setLastName($faker->lastName());
            $participant->setEmail($faker->safeEmail());
            $participant->setPhone($faker->phoneNumber());
            $participant->setAcceptedTerms($faker->optional(0.8)->passthrough(true) === true);
            $participant->setState(ParticipantState::Active);
            $participant->setSubscribedPathway($this->getReference('pathway-1'));
            $manager->persist($participant);
            $this->addReference('participant-'.$i, $participant);
        }

        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            AchievementFixtures::class,
        ];
    }
}
