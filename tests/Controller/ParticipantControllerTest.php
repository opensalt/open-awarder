<?php

namespace App\Test\Controller;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ParticipantControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/participant/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Participant::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Participant index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'participant[firstName]' => 'Testing',
            'participant[lastName]' => 'Testing',
            'participant[email]' => 'Testing',
            'participant[subscribedPathway]' => 'Testing',
            'participant[acceptedTerms]' => 'Testing',
            'participant[phone]' => 'Testing',
            'participant[aboutMe]' => 'Testing',
            'participant[state]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participant();
        $fixture->setFirstName('My Title');
        $fixture->setLastName('My Title');
        $fixture->setEmail('My Title');
        $fixture->setSubscribedPathway('My Title');
        $fixture->setAcceptedTerms('My Title');
        $fixture->setPhone('My Title');
        $fixture->setAboutMe('My Title');
        $fixture->setState('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Participant');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participant();
        $fixture->setFirstName('Value');
        $fixture->setLastName('Value');
        $fixture->setEmail('Value');
        $fixture->setSubscribedPathway('Value');
        $fixture->setAcceptedTerms('Value');
        $fixture->setPhone('Value');
        $fixture->setAboutMe('Value');
        $fixture->setState('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'participant[firstName]' => 'Something New',
            'participant[lastName]' => 'Something New',
            'participant[email]' => 'Something New',
            'participant[subscribedPathway]' => 'Something New',
            'participant[acceptedTerms]' => 'Something New',
            'participant[phone]' => 'Something New',
            'participant[aboutMe]' => 'Something New',
            'participant[state]' => 'Something New',
        ]);

        self::assertResponseRedirects('/participant/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getSubscribedPathway());
        self::assertSame('Something New', $fixture[0]->getAcceptedTerms());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getAboutMe());
        self::assertSame('Something New', $fixture[0]->getState());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Participant();
        $fixture->setFirstName('Value');
        $fixture->setLastName('Value');
        $fixture->setEmail('Value');
        $fixture->setSubscribedPathway('Value');
        $fixture->setAcceptedTerms('Value');
        $fixture->setPhone('Value');
        $fixture->setAboutMe('Value');
        $fixture->setState('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/participant/');
        self::assertSame(0, $this->repository->count([]));
    }
}
