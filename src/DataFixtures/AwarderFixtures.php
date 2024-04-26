<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Awarder;
use App\Enums\AwarderState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AwarderFixtures extends Fixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $awarder = new Awarder();
        $awarder->setName('Awarder 1');
        $awarder->setDescription('Awarder 1 description');
        $awarder->setIssuerId('Issuer 1');
        $awarder->setContact('Contact 1');
        $awarder->setState(AwarderState::Active);
        $awarder->setOcpInfo(json_decode(<<<json
{
  "base_url": "https://randaocpservice-test.azurewebsites.net/",
  "client_id": "b924f784-f9fa-4524-8898-b28cf7889521",
  "client_secret": "f6szHFy7W1zeclVtIBcO760AwVsmqUjOw68geA5y",
  "client_id_issued_at": 1713298453,
  "client_secret_expires_at": 0,
  "client_name": "Test Publisher Client",
  "client_uri": "https://nd.gov",
  "token_endpoint_auth_method": "client_secret_basic",
  "scope": "ocp-publisher",
  "grant_types": [
    "client_credentials",
    "refresh_token"
  ]
}
json
        , true, 512, JSON_THROW_ON_ERROR));

        $manager->persist($awarder);
        $this->addReference('awarder-1', $awarder);
        $awarder = new Awarder();
        $awarder->setName('Awarder 2');
        $awarder->setDescription('Awarder 2 description');
        $awarder->setIssuerId('Issuer 2');
        $awarder->setContact('Contact 2');
        $awarder->setState(AwarderState::Active);
        $awarder->setOcpInfo(json_decode(<<<json
{
  "base_url": "https://randaocpservice-test.azurewebsites.net/",
  "client_id": "011fc9cb-617c-4d15-93b8-d0b0484d255f",
  "client_secret": "ksEVkryk3LTCVKfJK1lS4NwcDbCT28Np0ydw5rEC",
  "client_id_issued_at": 1713375779,
  "client_secret_expires_at": 0,
  "client_name": "Test OAS Client",
  "client_uri": "https://oas-qa.opensalt.net",
  "token_endpoint_auth_method": "client_secret_basic",
  "scope": "ocp-publisher ocp-wallet",
  "grant_types": [
    "client_credentials",
    "refresh_token"
  ]
}
json
        , true, 512, JSON_THROW_ON_ERROR));

        $manager->persist($awarder);
        $this->addReference('awarder-2', $awarder);

        $manager->flush();
    }
}
