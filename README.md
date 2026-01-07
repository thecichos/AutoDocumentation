# API Documentation

## Table of Contents

### Types

- **Core**
  - [TypeRegistry](#type-type-registry)
- **System**
  - [AutoDocumentation](#type-auto-documentation)

---

## Type Reference

### TypeRegistry {#type-type-registry}

Parses classes with the #[Documentable] attribute and extracts their metadata (properties, methods, descriptions) into TypeInfo objects.

**Fully Qualified Name:** `AutoDocumentation\Generator\TypeRegistry`

**Properties:**

| Visibility | Property | Type | Description |
|------------|----------|------|-------------|
| Private | `types` | `array<class-string,` |  |

**Methods:**

| Visibility | Method | Description | Returns |
|------------|--------|-------------|--------|
| Public | `register()` | Registers a class with the #[Documentable] attribute, extracting its metadata into a TypeInfo object | `void` |
| Public | `resolve()` | Resolves a type by its fully-qualified class name or short name, returning null if not found | `AutoDocumentation\Generator\TypeInfo` |
| Public | `isLinkable()` | Checks whether a type name can be linked in documentation (i.e., is registered) | `bool` |
| Public | `getAll()` | Returns all registered types as an associative array keyed by FQCN | `array<class-string,` |
| Public | `getAllGrouped()` | Returns all registered types grouped by their category/group name | `array<string,` |
| Private | `extractProperties()` | Extracts all properties from a class and converts them to PropertyInfo objects | `PropertyInfo`[] |
| Private | `extractMethods()` | Extracts all documented methods from a class and converts them to MethodInfo objects | `array` |
| Private | `parseMethodParamDocBlock()` | Parses the @param tag from a method docblock to extract the type for a specific parameter | `string` |
| Private | `parseMethodReturnDocBlock()` | Parses the @return tag from a method docblock to extract the return type | `string` |
| Private | `parseMethodReturnDescription()` | Parses the @return tag from a method docblock to extract the return description | `string` |
| Private | `parseDocBlockType()` | Parses the @var tag from a property docblock to extract the type hint | `string` |
| Private | `slugify()` | Converts a PascalCase class name to a URL-friendly kebab-case slug | `string` |

---

### AutoDocumentation {#type-auto-documentation}

Auto-generated documentation for your API

**Fully Qualified Name:** `AutoDocumentation\AutoDocumentation`

**Properties:**

| Visibility | Property | Type | Description |
|------------|----------|------|-------------|
| Private | `registry` | [`TypeRegistry`](#type-type-registry) |  |
| Private | `generator` | `AutoDocumentation\Generator\DocGenerator` |  |
| Private | `htmlRenderer` | `AutoDocumentation\Renderer\HtmlRenderer` |  |
| Private | `jsonRenderer` | `AutoDocumentation\Renderer\JsonRenderer` |  |
| Private | `markdownRenderer` | `AutoDocumentation\Renderer\MDRenderer` |  |
| Private | `controllers` | `class-string`[] |  |

---

