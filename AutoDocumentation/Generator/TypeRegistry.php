<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;
use ReflectionClass;
use ReflectionProperty;

class TypeRegistry
{
    /** @var array<class-string, TypeInfo> */
    private array $types = [];

    public function register(ReflectionClass $class): void
    {
        $attr = $class->getAttributes(Documentable::class)[0] ?? null;
        
        if (!$attr) {
            return;
        }

        $doc = $attr->newInstance();
        
        $this->types[$class->getName()] = new TypeInfo(
            fqcn: $class->getName(),
            shortName: $class->getShortName(),
            slug: $doc->slug ?? $this->slugify($class->getShortName()),
            group: $doc->group,
            description: $doc->description,
            properties: $this->extractProperties($class)
        );
    }

    public function resolve(string $typeName): ?TypeInfo
    {
        // Direct FQCN match
        if (isset($this->types[$typeName])) {
            return $this->types[$typeName];
        }

        // Short name match (e.g., "User" → "App\Models\User")
        foreach ($this->types as $info) {
            if ($info->shortName === $typeName) {
                return $info;
            }
        }

        return null;
    }

    public function isLinkable(string $typeName): bool
    {
        return $this->resolve($typeName) !== null;
    }

    /**
     * @return array<class-string, TypeInfo>
     */
    public function getAll(): array
    {
        return $this->types;
    }

    /**
     * @return array<string, TypeInfo[]> Grouped by category
     */
    public function getAllGrouped(): array
    {
        $grouped = [];
        
        foreach ($this->types as $type) {
            $grouped[$type->group][] = $type;
        }
        
        ksort($grouped);
        return $grouped;
    }

    /**
     * @return PropertyInfo[]
     */
    private function extractProperties(ReflectionClass $class): array
    {
        $properties = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $attr = $prop->getAttributes(Property::class)[0] ?? null;
            $propDoc = $attr?->newInstance();

            $type = $prop->getType()?->getName() ?? 'mixed';
            $nullable = $prop->getType()?->allowsNull() ?? true;
            
            // Check for array type in docblock
            $docType = $this->parseDocBlockType($prop);

            $properties[] = new PropertyInfo(
                name: $prop->getName(),
                type: $docType ?? $type,
                nullable: $nullable,
                description: $propDoc?->description ?? '',
                example: $propDoc?->example,
                deprecated: $propDoc?->deprecated ?? false
            );
        }

        return $properties;
    }

    private function parseDocBlockType(ReflectionProperty $prop): ?string
    {
        $docComment = $prop->getDocComment();
        
        if (!$docComment) {
            return null;
        }

        // Match @var Type or @var Type[]
        if (preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function slugify(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
    }
}