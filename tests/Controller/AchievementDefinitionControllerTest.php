<?php

namespace App\Test\Controller;

use App\Entity\AchievementDefinition;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AchievementDefinitionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/achievement/definition/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(AchievementDefinition::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AchievementDefinition index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'achievement_definition[name]' => 'Testing',
            'achievement_definition[uri]' => 'Testing',
            'achievement_definition[awarders]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new AchievementDefinition();
        $fixture->setName('My Title');
        $fixture->setUri('My Title');
        $fixture->setAwarders('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AchievementDefinition');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new AchievementDefinition();
        $fixture->setName('Value');
        $fixture->setUri('Value');
        $fixture->setAwarders('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'achievement_definition[name]' => 'Something New',
            'achievement_definition[uri]' => 'Something New',
            'achievement_definition[awarders]' => 'Something New',
        ]);

        self::assertResponseRedirects('/achievement/definition/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getUri());
        self::assertSame('Something New', $fixture[0]->getAwarders());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new AchievementDefinition();
        $fixture->setName('Value');
        $fixture->setUri('Value');
        $fixture->setAwarders('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/achievement/definition/');
        self::assertSame(0, $this->repository->count([]));
    }
}
