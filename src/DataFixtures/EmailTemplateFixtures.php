<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\EmailTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EmailTemplateFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $template = new EmailTemplate();
        $template->setName('Email Template 1');
        $template->setTemplate(<<<html
Hello {{ subject.firstName }} {{ subject.lastName }},<br />
<br />
You have received the {{ achievement.name }} credential from {{ awarder.name }}.<br />
<br />
Your current pathway:<br />
{{ context.pathway|raw }}
<br />
You can obtain your credential at <a href="OCP_ACCEPT_URL">OCP_ACCEPT_URL</a>
html
        );
        $template->addAwarder($this->getReference('awarder-1'));
        $template->addAwarder($this->getReference('awarder-2'));
        $template->setFrom('OAS-Awarder1-From-Email@example.com');
        $template->setSubject('OAS-Awarder1: Your award is being offered');

        $manager->persist($template);
        $this->addReference('email-template-1', $template);

        $template = new EmailTemplate();
        $template->setName('Email Template 2');
        $template->setTemplate("Hello {{ subject.firstName }} {{ subject.lastName }},\n\nThis is one more {{ item2 }}.");
        $template->addAwarder($this->getReference('awarder-1'));
        $template->addAwarder($this->getReference('awarder-2'));
        $template->setFrom('OAS-Awarder2-From-Email@example.com');
        $template->setSubject('OAS-Awarder2: Your award is being offered');

        $manager->persist($template);
        $this->addReference('email-template-2', $template);

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
