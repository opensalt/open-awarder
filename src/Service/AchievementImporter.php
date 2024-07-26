<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AchievementDefinition;
use App\Repository\AchievementDefinitionRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class AchievementImporter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private AchievementDefinitionRepository $achievementDefinitionRepository,
    ) {
    }

    public function import(string $uri): AchievementDefinition
    {
        $response = $this->httpClient->request('GET', $uri, [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $achievement = $response->toArray();

        $achievementDefinition = $this->achievementDefinitionRepository->findOneBy(['uri' => $achievement['id']]);

        if (null !== $achievementDefinition) {
            throw new \InvalidArgumentException('This achievement is already in the system.');
        }

        if ('Achievement' !== $achievement['type'] && !in_array('Achievement', $achievement['type'] ?? [])) {
           throw new \InvalidArgumentException('URL does not point to an achievement');
        }

        $achievementDefinition = new AchievementDefinition();
        $achievementDefinition->setName($achievement['name']);
        $achievementDefinition->setUri($achievement['id']);
        $achievementDefinition->setIdentifier($achievement['id']);
        $achievementDefinition->setDefinition($achievement);

        $this->achievementDefinitionRepository->save($achievementDefinition);

        return $achievementDefinition;
    }
}
