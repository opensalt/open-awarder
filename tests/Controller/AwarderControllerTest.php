<?php

namespace App\Test\Controller;

use App\Entity\Awarder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AwarderControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/awarder/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Awarder::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Awarder index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'awarder[name]' => 'Testing',
            'awarder[description]' => 'Testing',
            'awarder[issuerId]' => 'Testing',
            'awarder[contact]' => 'Testing',
            'awarder[protocol]' => 'Testing',
            'awarder[ocpInfo]' => 'Testing',
            'awarder[achievements]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Awarder();
        $fixture->setName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setIssuerId('My Title');
        $fixture->setContact('My Title');
        $fixture->setProtocol('My Title');
        $fixture->setOcpInfo('My Title');
        $fixture->setAchievements('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Awarder');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Awarder();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setIssuerId('Value');
        $fixture->setContact('Value');
        $fixture->setProtocol('Value');
        $fixture->setOcpInfo('Value');
        $fixture->setAchievements('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'awarder[name]' => 'Something New',
            'awarder[description]' => 'Something New',
            'awarder[issuerId]' => 'Something New',
            'awarder[contact]' => 'Something New',
            'awarder[protocol]' => 'Something New',
            'awarder[ocpInfo]' => 'Something New',
            'awarder[achievements]' => 'Something New',
        ]);

        self::assertResponseRedirects('/awarder/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getIssuerId());
        self::assertSame('Something New', $fixture[0]->getContact());
        self::assertSame('Something New', $fixture[0]->getProtocol());
        self::assertSame('Something New', $fixture[0]->getOcpInfo());
        self::assertSame('Something New', $fixture[0]->getAchievements());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Awarder();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setIssuerId('Value');
        $fixture->setContact('Value');
        $fixture->setProtocol('Value');
        $fixture->setOcpInfo('Value');
        $fixture->setAchievements('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/awarder/');
        self::assertSame(0, $this->repository->count([]));
    }
}
