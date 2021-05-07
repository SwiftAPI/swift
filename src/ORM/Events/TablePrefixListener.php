<?php declare( strict_types=1 );

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\ORM\Events;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Swift\Configuration\ConfigurationInterface;
use Swift\Events\Attribute\ListenTo;
use Swift\Kernel\Attributes\Autowire;

/**
 * Class TablePrefixListener
 * @package Swift\ORM\Events
 */
#[Autowire]
class TablePrefixListener implements DoctrineEventListener {

    /**
     * TablePrefixListener constructor.
     */
    public function __construct(
        private ConfigurationInterface $configuration,
    ) {
    }

    #[ListenTo(event: Events::loadClassMetadata)]
    public function onLoadClassMetadata( LoadClassMetadataEventArgs $eventArgs ) {
        $classMetadata = $eventArgs->getClassMetadata();

        if ( ! $classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName ) {
            $classMetadata->setPrimaryTable( [
                'name' => $this->configuration->get('connection.prefix', 'database') . $classMetadata->getTableName()
            ] );
        }

        foreach ( $classMetadata->getAssociationMappings() as $fieldName => $mapping ) {
            if ( ($mapping['type'] === ClassMetadataInfo::MANY_TO_MANY) && $mapping['isOwningSide'] ) {
                $mappedTableName                                                       = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[ $fieldName ]['joinTable']['name'] = $this->configuration->get('connection.prefix', 'database') . $mappedTableName;
            }
        }
    }

}