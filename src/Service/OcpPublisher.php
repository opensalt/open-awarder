<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Award;
use App\Entity\Awarder;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class OcpPublisher
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheInterface $cache,
        private readonly StorageInterface $storage,
        private readonly FilesystemOperator $evidenceStorage,
    ) {
    }

    private function authenticate(Awarder $awarder): HttpClientInterface
    {
        $baseUrl = $awarder->getOcpInfo()['base_url'];

        $token = $this->cache->get('ocp-bearer-token-' . $awarder->getId(), function (ItemInterface $item) use ($awarder, $baseUrl): string {
            $clientId = $awarder->getOcpInfo()['client_id'];
            $clientSecret = $awarder->getOcpInfo()['client_secret'];

            $response = $this->client->request('POST', '/connect/token', [
                'base_uri' => $baseUrl,
                'headers' => [
                    'Accept' => 'application/json; application/ld+json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'ocp-publisher',
                ],
            ]);

            $content = $response->toArray();

            $item->expiresAfter($content['expires_in'] - 300);

            return $content['access_token'];
        });

        return $this->client->withOptions([
            'base_uri' => $baseUrl,
            'auth_bearer' => $token,
            'headers' => [
                'Accept' => 'application/json; application/ld+json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function publishAward(Award $award): void
    {
        $json = $award->getAwardJson();

        $evidence = $award->getEvidence();

        $json['clr']['assertions'][0]['evidence'] = [];
        foreach ($evidence as $file) {
            try {
                $content = $this->evidenceStorage->read($this->storage->resolvePath($file));
                $json['clr']['assertions'][0]['evidence'][] = [
                    'name' => 'evidence',
                    'description' => 'File containing evidence',
                    'artifacts' => [
                        [
                            'name' => $file->getOriginalName(),
                            'url' => 'data:'.$file->getMimetype().';base64,'.base64_encode($content),
                        ],
                    ],
                ];
            } catch (\Throwable) {
                // ignore
            }
        }

        if (empty($json['clr']['assertions'][0]['evidence'])) {
            unset($json['clr']['assertions'][0]['evidence']);
        }

        $response = $this->authenticate($award->getAwarder())->request('POST', '/api/publish', [
            'json' => $json,
        ]);

        $content = $response->toArray(false);

        $award->setLastResponse($content);

        if ($response->getStatusCode() !== 200) {
            $this->entityManager->flush();

            throw new \Exception('Failed to publish award');
        }

        $award->setRequestId($content['requestId']);

        $this->entityManager->flush();
    }

    public function getRequestStatus(Award $award): array
    {
        $response = $this->authenticate($award->getAwarder())->request('GET', '/api/requests/'.$award->getRequestId(), [
        ]);

        return $response->toArray(false);
    }

    public function revokeAward(Award $award): void
    {
        $this->authenticate($award->getAwarder())->request('DELETE', '/api/requests/'.$award->getRequestId(), [
        ]);
    }
}
