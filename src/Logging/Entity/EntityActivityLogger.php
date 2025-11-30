<?php

namespace JDS\Logging\Entity;

use JDS\Contracts\Logging\EntityActivityLogWriterInterface;
use JDS\Exceptions\EntityLogException;
use ReflectionClass;
use ReflectionMethod;

class EntityActivityLogger
{
    public function __construct(
        private EntityActivityLogWriterInterface $writer,
        private ?string $currentUserId = null
    )
    {
    }

    public function log(object $entity, string $action, ?array $fields = null): void
    {
        $entityName = $this->getEntityName($entity);
        $entityId = $this->getEntityId($entity);

        $data = $fields
            ? $this->extractSpecificFields($entity, $fields)
            : $this->extractAllFields($entity);

        $record = new EntityActivityRecord(
            entityName: $entityName,
            entityId: $entityId,
            action: $action,
            fields: $data,
            userId: $this->currentUserId,
            timestamp: new \DateTimeImmutable()
        );

        $this->writer->write($record);
    }

    private function extractAllFields(object $entity): array
    {
        $class = new ReflectionClass($entity);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $data = [];

        foreach ($methods as $method) {
            if ($this->isGetter($method)) {
                $property = $this->getterToProperty($method->getName());
                $data[$property] = $method->invoke($entity);
            }
        }
        return $data;
    }

    private function isGetter(ReflectionMethod $method): bool
    {
        if ($method->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        return str_starts_with($method->getName(), 'get') || str_starts_with($method->getName(), 'is');
    }

    private function getterToProperty(string $getter): string
    {
        $name = preg_replace('/^(get|is)/', '', $getter);
        return lcfirst($name);
    }

    private function getEntityName(object $entity): string
    {
        return (new ReflectionClass($entity))->getShortName();
    }

    private function getEntityId(object $entity): string
    {
        $possible = ['getCompanyId', 'getId'];

        foreach ($possible as $method) {
            if (method_exists($entity, $method)) {
                return (string)$entity->$method();
            }
        }
        throw new EntityLogException("Entity has no ID getter method.");
    }
}

