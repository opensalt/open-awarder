<?php

namespace App\Namer;

use App\Entity\EvidenceFile;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;

/**
 * Directory namer that uses the award subject and id.
 */
#[AsAlias(id: 'app.namer.award_directory_namer', public: true)]
class AwardFileDirectoryNamer implements DirectoryNamerInterface
{

    /**
     * @inheritDoc
     */
    public function directoryName(object|array $object, PropertyMapping $mapping): string
    {
        if ($object instanceof EvidenceFile) {
            return $object->getAward()->getSubject()->getId()->toBase58().'/'.$object->getAward()->getId()->toBase58();
        }

        return '';
    }
}
