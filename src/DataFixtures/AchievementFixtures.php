<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AchievementDefinition;
use App\Entity\Pathway;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class AchievementFixtures extends Fixture implements DependentFixtureInterface
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; ++$i) {
            $achievement = new AchievementDefinition();
            $achievement->setName('Achievement '.$i);
            $achievement->setUri('https://example.com/'.$i);
            $achievement->setIdentifier((string) $i);
            $achievement->addAwarder($this->getReference('awarder-1'));
            if ($i < 3) {
                $achievement->addAwarder($this->getReference('awarder-2'));
            }

            $json = json_decode(<<<xENDx
{
  "id": "https://example.com/{$i}",
  "name": "Achievement {$i}",
  "description": "Description of Achievement {$i}",
  "achievementType": "Achievement",
  "issuer": {
    "id": "urn:uuid:{{ awarder.id }}",
    "name": "{{ awarder.name }}"
  },
  "resultDescriptions": [],
  "image": "https://placehold.co/300x50/000000/FFFFFF.png?text=Achievement+{$i}"
}
xENDx
            , true, 512, JSON_THROW_ON_ERROR);
            if ($i < 3) {
                $json['resultDescriptions'][] = [
                    'id' => 'urn:uuid:'.(Uuid::fromString('ba82e190-f624-4a62-a82f-04c9f5df975'.$i))->toRfc4122(),
                    'name' => 'Score',
                    'resultType' => 'RawScore',
                ];
            }

            $achievement->setDefinition($json);
            $manager->persist($achievement);
            $this->addReference('achievement-'.$i, $achievement);
        }

        $pathway = new Pathway();
        $pathway->setName('Pathway 1');
        $pathway->setEmailTemplate(<<<html
<div class="credential-page">
  <ul class="tree">
    <li>
        <span class="cred-cb"><input id="credential-10" type="checkbox" {{ '10' in context.credentialIds ? 'checked' : '' }}></span>
        <strong class="cred-hcs">(WMS)</strong> <label for="credential-10"><span class="cred-desc">Achievement 10</span></label>
        <ul>
          <li>
            <span class="cred-cb"><input id="credential-1" type="checkbox" {{ '1' in context.credentialIds ? 'checked' : '' }}></span>
            <label for="credential-1"><span class="cred-desc">Achievement 1</span></label>
          </li>
          <li>
            <span class="cred-cb"><input id="credential-2" type="checkbox" {{ '2' in context.credentialIds ? 'checked' : '' }}></span>
            <label for="credential-2"><span class="cred-desc">Achievement 2</span></label>
          </li>
          <li>
            <span class="cred-cb"><input id="credential-3" type="checkbox" {{ '3' in context.credentialIds ? 'checked' : '' }}></span>
            <label for="credential-3"><span class="cred-desc">Achievement 3</span></label>
            <ul>
              <li>
                <span class="cred-cb"><input id="credential-4" type="checkbox" {{ '4' in context.credentialIds ? 'checked' : '' }}></span>
                <label for="credential-4"><span class="cred-desc">Achievement 4</span></label>
              </li>
              <li>
                <span class="cred-cb"><input id="credential-5" type="checkbox" {{ '5' in context.credentialIds ? 'checked' : '' }}></span>
                <label for="credential-5"><span class="cred-desc">Achievement 5</span></label>
              </li>
            </ul>
          </li>
        </ul>
    </li>
  </ul>
</div>
html
        );
        $pathway->setFinalCredential($this->getReference('achievement-1'));

        $manager->persist($pathway);
        $this->addReference('pathway-1', $pathway);

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
