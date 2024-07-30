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


        $clrType = null;
        $postUrl = null;
        if (null !== ($json['clr']['assertions'] ?? null)) {
            $clrType = 1;
            $postUrl = '/api/publish/ThenPush';
        }
        if (null !== ($json['clr']['credentialSubject'] ?? null)) {
            $clrType = 2;
            $postUrl = '/api/publish/2_0/Push';
        }

        $evidenceJson = [];
        foreach ($evidence as $file) {
            try {
                $content = $this->evidenceStorage->read($this->storage->resolvePath($file));
                $evidenceJson[] = match($clrType) {
                    1 => [
                        'name' => 'evidence',
                        'description' => 'File containing evidence',
                        'artifacts' => [
                            [
                                'name' => $file->getOriginalName(),
                                'url' => 'data:'.$file->getMimetype().';base64,'.base64_encode($content),
                            ],
                        ],
                    ],
                    2 => [
                        'id' => 'data:'.$file->getMimetype().';base64,'.base64_encode($content),
                        'type' => ['Evidence'],
                        'name' => $file->getOriginalName(),
                        'description' => 'File containing evidence',
                    ]
                };
            } catch (\Throwable) {
                // ignore
            }
        }

        if (count($evidenceJson) > 0) {
            switch ($clrType) {
                case 1:
                    $json['clr']['assertions'][0]['evidence'] = $evidenceJson;
                    break;
                case 2:
                    $json['clr']['credentialSubject']['verifiableCredential'][0]['evidence'] = $evidenceJson;
                    break;
            }
        }

        $response = $this->authenticate($award->getAwarder())->request('POST', $postUrl, [
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

    /**
     * @return array<array-key, mixed>
     */
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
