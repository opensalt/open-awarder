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

    public function import(string $uri): void
    {
        $response = $this->httpClient->request('GET', $uri, [
            'headers' => [
                'Accept' => 'application/ld+json',
            ],
        ]);

        $achievement = $response->toArray();

        $achievementDefinition = $this->achievementDefinitionRepository->findOneBy(['uri' => $achievement['id']]);

        if ('Achievement' !== $achievement['type'] && !in_array('Achievement', $achievement['type'] ?? [])) {
           throw new \InvalidArgumentException('URL does not point to an achievement');
        }

        // Change the imported achievement to be CLR1 like instead of OB3 like

        unset($achievement['type']);

        if (null !== ($achievement['@context'] ?? null)) {
            unset($achievement['@context']);
        }

        if (is_array($achievement['achievementType'] ?? null)) {
            $achievement['achievementType'] = $achievement['achievementType'][0];
        }

        if (null !== ($achievement['image'] ?? null) && null !== ($achievement['image']['id'] ?? null)) {
            $achievement['image'] = $achievement['image']['id'];
        }

        if (null === ($achievement['issuer'] ?? null)) {
            // OB3 to CLR1 difference
            $achievement['issuer'] = [
                'id' => 'urn:uuid:{{ awarder.id }}',
                'name' => '{{ awarder.name }}',
            ];
        }

        if (null !== ($achievement['criteria'] ?? null)) {
            // OB3 to CLR1 difference
            $achievement['requirement'] = $achievement['criteria'];
            unset($achievement['criteria']);
        }

        if (null !== ($achievement['resultDescription'] ?? null)) {
            // OB3 to CLR1 difference
            $achievement['resultDescriptions'] = $achievement['resultDescription'];
            unset($achievement['resultDescriptions']);
        }

        if (null !== ($achievement['alignment'] ?? null)) {
            // OB3 to CLR1 difference
            $achievement['alignments'] = $achievement['alignment'];
            unset($achievement['alignment']);
        }

        if (null === $achievementDefinition) {
            $achievementDefinition = new AchievementDefinition();
            $achievementDefinition->setName($achievement['name']);
            $achievementDefinition->setUri($achievement['id']);
            $achievementDefinition->setIdentifier($achievement['id']);
            $achievementDefinition->setDefinition($achievement);

            $this->achievementDefinitionRepository->save($achievementDefinition);
        }
    }
}
