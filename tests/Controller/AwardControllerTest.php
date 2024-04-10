<?php

namespace App\Test\Controller;

use App\Entity\Award;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AwardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/award/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Award::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Award index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'award[results]' => 'Testing',
            'award[evidence]' => 'Testing',
            'award[state]' => 'Testing',
            'award[awarder]' => 'Testing',
            'award[subject]' => 'Testing',
            'award[awardTemplate]' => 'Testing',
            'award[emailTemplate]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Award();
        $fixture->setResults('My Title');
        $fixture->setEvidence('My Title');
        $fixture->setState('My Title');
        $fixture->setAwarder('My Title');
        $fixture->setSubject('My Title');
        $fixture->setAwardTemplate('My Title');
        $fixture->setEmailTemplate('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Award');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Award();
        $fixture->setResults('Value');
        $fixture->setEvidence('Value');
        $fixture->setState('Value');
        $fixture->setAwarder('Value');
        $fixture->setSubject('Value');
        $fixture->setAwardTemplate('Value');
        $fixture->setEmailTemplate('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'award[results]' => 'Something New',
            'award[evidence]' => 'Something New',
            'award[state]' => 'Something New',
            'award[awarder]' => 'Something New',
            'award[subject]' => 'Something New',
            'award[awardTemplate]' => 'Something New',
            'award[emailTemplate]' => 'Something New',
        ]);

        self::assertResponseRedirects('/award/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getResults());
        self::assertSame('Something New', $fixture[0]->getEvidence());
        self::assertSame('Something New', $fixture[0]->getState());
        self::assertSame('Something New', $fixture[0]->getAwarder());
        self::assertSame('Something New', $fixture[0]->getSubject());
        self::assertSame('Something New', $fixture[0]->getAwardTemplate());
        self::assertSame('Something New', $fixture[0]->getEmailTemplate());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Award();
        $fixture->setResults('Value');
        $fixture->setEvidence('Value');
        $fixture->setState('Value');
        $fixture->setAwarder('Value');
        $fixture->setSubject('Value');
        $fixture->setAwardTemplate('Value');
        $fixture->setEmailTemplate('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/award/');
        self::assertSame(0, $this->repository->count([]));
    }
}
