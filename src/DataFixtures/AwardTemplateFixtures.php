<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AwardTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AwardTemplateFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $template = new AwardTemplate();
        $template->setName('Generic Award Template');

        $json = json_decode(<<<xENDx
{
    "identity": {
      "id": "{{ context.requestIdentity }}"
    },
    "clr": {
        "id": "{{ context.clrId }}",
        "name": "{{ achievement.name }}",
        "partial": true,
        "learner": {
            "id": "urn:uuid:{{ subject.id }}",
            "name": "{{ subject.firstName }} {{ subject.lastName }}",
            "email": "{{ subject.email }}"
        },
        "publisher": {
            "id": "urn:uuid:{{ awarder.id }}",
            "name": "{{ awarder.name }}"
        },
        "assertions": [
            {
                "id": "{{ context.assertionId }}",
                "recipient": {
                    "type": "id",
                    "identity": "urn:uuid:{{ subject.id }}"
                },
                "achievement": "~{{ achievement.definitionString|raw }}~",
                "evidence": [],
                "results": [],
                "issuedOn": "{{ context.issuedOn|date('c') }}"
            }
        ],
        "issuedOn": "{{ context.issuedOn|date('c') }}"
    }
}
xENDx
        , true, 512, JSON_THROW_ON_ERROR);
        $template->setTemplate($json);
        $template->addAwarder($this->getReference('awarder-1'));
        $template->addAwarder($this->getReference('awarder-2'));

        $manager->persist($template);
        $this->addReference('award-template-1', $template);

        $template = new AwardTemplate();
        $template->setName('Award Template 2');
        $template->setTemplate($json);
        $template->addAwarder($this->getReference('awarder-1'));

        $manager->persist($template);
        $this->addReference('award-template-2', $template);

        $manager->flush();
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [
            AwarderFixtures::class,
        ];
    }
}
