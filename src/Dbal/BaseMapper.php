<?php

namespace JDS\Dbal;

use Doctrine\DBAL\Connection;
use JDS\Contracts\Diff\DiffableEntityInterface;
use JDS\Dbal\AbstractDatabaseHelper;
use JDS\Exceptions\DiffRunTimeException;

/**
 * BaseMapper
 *
 * Provides automatic:
 *
 * - New ID assignment
 *
 * - Diffable entity original-state capture
 *
 * - Insert/update detection
 *
 * - Database execution helpers
 *
 * - PostPersit dispatch through Datamapper->save()
 *
 *
 * Concrete mappers only implement:
 *
 * - insert(Entity $entity): void
 *
 * - update(Entity $entity): void
 *
 * - findOriginal (Entity $entity): ?Entry
 *
 */
abstract class BaseMapper extends AbstractDatabaseHelper
{
    public function __construct(
        protected DataMapper $dataMapper,
        protected Connection $db
    )
    {
    }

    /**
     * Save entity (insert or update automatically)
     */
    public function save(object $entity): void
    {
        if ($this->isNew($entity)) {
            $this->handleInsert($entity);
        } else {
            $this->handleUpdate($entity);
        }

        //
        // Trigger PostPersist -> ActivityLogger -> Diffservice
        //
        $this->dataMapper->save($entity);
    }

    // ---------------------------------------------------
    // INSERT LOGIC
    // ---------------------------------------------------

    protected function handleInsert(object $entity): void
    {
        //
        // If diffable, new entities have no "original state"
        //
        if ($entity instanceof DiffableEntityInterface) {
            $entity->setOriginalState([]);
        }

        $this->assignNewIdentifierIfNeeded($entity);
        $this->insert($entity);
    }

    /**
     * Override this in child mappers
     */
    abstract protected function insert(object $entity): void;

    // ---------------------------------------------------
    // UPDATE LOGIC
    // ---------------------------------------------------

    protected function handleUpdate(object $entity): void
    {
        if ($entity instanceof DiffableEntityInterface) {
            $original = $this->findOriginal($entity);

            if (!$original) {
                throw new DiffRunTimeException(
                    sprintf(
                        "Original entity not found for type '%s' with ID '%s'",
                        $entity->getEntityType(),
                        $entity->getEntityIdentifier()
                    )
                );
            }
        }

        $this->update($entity);

    }

    /**
     * Override this in child mappers.
     * Must return on instance of the same entity loaded from DB.
     */
    abstract protected function findOriginal(object $entity): ?object;

    /**
     * Override this in child mappers
     */
    abstract protected function update(object $entity): void;

    protected function isNew(object $entity): bool
    {
        if ($entity instanceof DiffableEntityInterface) {
            //
            // if null = definitely new
            //
            $id = $entity->getEntityIdentifier();
            return empty($id) || $id === 'new';
        }

        //
        // fallback (your entities always implement Diffable, so rarely used)
        return false;
    }

    protected function assignNewIdentifierIfNeeded(object $entity): void
    {
        if (!method_exists($entity, 'setRoleId') &&
            !method_exists($entity, 'setId')) {
            return; // Not a new entity or no ID setter
        }
    }
}

