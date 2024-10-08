<?php

declare(strict_types=1);

namespace App\Namer;

use App\Entity\EvidenceFile;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

/**
 * Directory namer that uses the award subject and id.
 *
 * @implements DirectoryNamerInterface<EvidenceFile>
 */
#[AsAlias(id: 'app.namer.award_directory_namer', public: true)]
class AwardFileDirectoryNamer implements DirectoryNamerInterface
{
    /**
     * @inheritDoc
     *
     * @param EvidenceFile|array<EvidenceFile> $object
     */
    #[\Override]
    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof EvidenceFile) {
            return $object->getAward()->getSubject()->getId()->toBase58().'/'.$object->getAward()->getId()->toBase58();
        }

        return '';
    }
}
