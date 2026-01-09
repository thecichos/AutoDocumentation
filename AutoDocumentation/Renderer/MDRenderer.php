<?php

namespace AutoDocumentation\Renderer;

use AutoDocumentation\Generator\TypeInfo;
use AutoDocumentation\Generator\TypeRegistry;

/**
 * Markdown renderer for API documentation
 *
 * @group Renderer
 */
class MDRenderer
{
	public function __construct(
		private TypeRegistry $registry
	) {}

	/**
	 * Renders documentation as Markdown
	 *
	 * @param array $docs Generated documentation from DocGenerator
	 * @param TypeInfo[] $types All registered types
	 * @param array $info Optional metadata (title, version, description)
	 */
	public function render(array $docs, array $types, array $info = []): string
	{
		$md = '';

		// Header
		$title = $info['title'] ?? 'API Documentation';
		$md .= "# {$title}\n\n";

		if (!empty($info['description'])) {
			$md .= $info['description'] . "\n\n";
		}

		if (!empty($info['version'])) {
			$md .= "**Version:** {$info['version']}\n\n";
		}

		// Table of Contents
		$md .= $this->renderTableOfContents($docs, $types);

		// Endpoints
		if (!empty($docs)) {
			$md .= "---\n\n## Endpoints\n\n";
			foreach ($docs as $controller) {
				$md .= $this->renderController($controller);
			}
		}

		// Types
		if (!empty($types)) {
			$md .= "---\n\n## Type Reference\n\n";
			foreach ($types as $type) {
				$md .= $this->renderType($type);
			}
		}

		return $md;
	}

	/**
	 * @param TypeInfo[] $types
	 */
	private function renderTableOfContents(array $docs, array $types): string
	{
		$md = "## Table of Contents\n\n";

		if (!empty($docs)) {
			$md .= "### Endpoints\n\n";
			foreach ($docs as $controller) {
				$anchor = $this->slugify($controller['name']);
				$md .= "- [{$controller['name']}](#{$anchor})\n";

				foreach ($controller['endpoints'] as $endpoint) {
					$endpointAnchor = $this->slugify($controller['name'] . '-' . $endpoint['slug']);
					$method = strtoupper($endpoint['http_method']);
					$md .= "  - `{$method}` [{$endpoint['path']}](#{$endpointAnchor})\n";
				}
			}
			$md .= "\n";
		}

		if (!empty($types)) {
			$groupedTypes = $this->registry->getAllGrouped();
			$md .= "### Types\n\n";

			foreach ($groupedTypes as $group => $groupTypes) {
				$md .= "- **{$group}**\n";
				foreach ($groupTypes as $type) {
					$anchor = $type->shortName.'-type-' . $type->slug;
					$md .= "  - [{$type->shortName}](#{$anchor})\n";
				}
			}
			$md .= "\n";
		}

		return $md;
	}

	private function renderController(array $controller): string
	{
		$anchor = $this->slugify($controller['name']);
		$md = "### {$controller['name']} {#$anchor}\n\n";

		if ($controller['deprecated']) {
			$md .= "> ⚠️ **Deprecated**\n\n";
		}

		if (!empty($controller['description'])) {
			$md .= $controller['description'] . "\n\n";
		}

		if (!empty($controller['tags'])) {
			$tags = implode('`, `', $controller['tags']);
			$md .= "**Tags:** `{$tags}`\n\n";
		}

		$md .= "**Version:** {$controller['version']}\n\n";

		foreach ($controller['endpoints'] as $endpoint) {
			$md .= $this->renderEndpoint($endpoint, $controller['name']);
		}

		return $md;
	}

	private function renderEndpoint(array $endpoint, string $controllerName): string
	{
		$anchor = $this->slugify($controllerName . '-' . $endpoint['slug']);
		$method = strtoupper($endpoint['http_method']);

		$md = "#### `{$method}` {$endpoint['path']} {#$anchor}\n\n";

		if ($endpoint['deprecated']) {
			$md .= "> ⚠️ **Deprecated**\n\n";
		}

		if (!empty($endpoint['summary'])) {
			$md .= "**{$endpoint['summary']}**\n\n";
		}

		if (!empty($endpoint['description'])) {
			$md .= $endpoint['description'] . "\n\n";
		}

		// Parameters
		if (!empty($endpoint['parameters'])) {
			$md .= $this->renderParameters($endpoint['parameters']);
		}

		// Returns
		if ($endpoint['returns'] !== null) {
			$md .= $this->renderReturns($endpoint['returns']);
		}

		// Responses
		if (!empty($endpoint['responses'])) {
			$md .= $this->renderResponses($endpoint['responses']);
		}

		$md .= "---\n\n";

		return $md;
	}

	private function renderParameters(array $parameters): string
	{
		$md = "**Parameters:**\n\n";
		$md .= "| Name | Type | In | Required | Description |\n";
		$md .= "|------|------|-----|----------|-------------|\n";

		foreach ($parameters as $param) {
			$name = "`{$param['name']}`";
			$type = $this->formatType($param['type']);
			$in = $param['in'];
			$required = $param['required'] ? '✅ Yes' : '❌ No';

			$description = $this->escapeTableCell($param['description']);

			if ($param['example'] !== null) {
				$example = json_encode($param['example']);
				$description .= " Example: `{$example}`";
			}

			if ($param['has_default']) {
				$default = json_encode($param['default']);
				$description .= " Default: `{$default}`";
			}

			$md .= "| {$name} | {$type} | {$in} | {$required} | {$description} |\n";
		}

		$md .= "\n";

		return $md;
	}

	private function renderReturns(array $returns): string
	{
		$type = $this->formatType($returns['type']);
		$status = $returns['status_code'];

		$md = "**Returns:** `{$status}` — {$type}";

		if (!empty($returns['description'])) {
			$md .= " — {$returns['description']}";
		}

		$md .= "\n\n";

		return $md;
	}

	private function renderResponses(array $responses): string
	{
		$md = "**Responses:**\n\n";
		$md .= "| Status | Type | Description |\n";
		$md .= "|--------|------|-------------|\n";

		foreach ($responses as $response) {
			$status = "`{$response['status_code']}`";
			$type = $response['type'] ? $this->formatType($response['type']) : '—';
			$description = $this->escapeTableCell($response['description']);

			$md .= "| {$status} | {$type} | {$description} |\n";
		}

		$md .= "\n";

		return $md;
	}

	private function renderType(TypeInfo $type): string
	{
		$anchor = 'type-' . $type->slug;
		$md = "### {$type->shortName} {#$anchor}\n\n";

		if (!empty($type->description)) {
			$md .= $type->description . "\n\n";
		}

		$md .= "**Fully Qualified Name:** `{$type->fqcn}`\n\n";

		// Properties
		if (!empty($type->properties)) {
			$md .= "**Properties:**\n\n";
			$md .= "| Visibility | Property | Type | Description |\n";
			$md .= "|------------|----------|------|-------------|\n";

			foreach ($type->properties as $prop) {
				$visibility = $prop->accessibility->name;
				$name = "`{$prop->name}`";

				$propType = $prop->nullable ? '?' : '';
				$propType .= $this->formatType($prop->type);

				$description = $this->escapeTableCell($prop->description);

				if ($prop->deprecated) {
					$description = "⚠️ Deprecated. " . $description;
				}

				if ($prop->example !== null) {
					$example = json_encode($prop->example);
					$description .= " Example: `{$example}`";
				}

				$md .= "| {$visibility} | {$name} | {$propType} | {$description} |\n";
			}

			$md .= "\n";
		}

		// Methods
		if (!empty($type->methods)) {
			$md .= "**Methods:**\n\n";
			$md .= "| Visibility | Method | Description | Returns |\n";
			$md .= "|------------|--------|-------------|--------|\n";

			foreach ($type->methods as $method) {
				$visibility = $method->accessibility->name;
				$name = "`{$method->name}()`";
				$description = $this->escapeTableCell($method->description);
				$returns = $method->returnType ? $this->formatType($method->returnType) : '—';

				if ($method->deprecated) {
					$description = "⚠️ Deprecated. " . $description;
				}

				$md .= "| {$visibility} | {$name} | {$description} | {$returns} |\n";
			}

			$md .= "\n";
		}

		$md .= "---\n\n";

		return $md;
	}

	/**
	 * Format a type name, linking to documented types when available
	 */
	private function formatType(string $typeName): string
	{
		// Handle nullable
		$prefix = '';
		if (str_starts_with($typeName, '?')) {
			$prefix = '?';
			$typeName = substr($typeName, 1);
		}

		// Handle arrays
		$suffix = '';
		if (preg_match('/^(.+)\[\]$/', $typeName, $matches)) {
			$suffix = '[]';
			$typeName = $matches[1];
		}

		// Check if it's a documented type
		$typeInfo = $this->registry->resolve($typeName);

		if ($typeInfo !== null) {
			$anchor = $typeInfo->shortName.'-type-'.$typeInfo->slug;
			return $prefix . "[`{$typeInfo->shortName}`](#{$anchor})" . $suffix;
		}

		return $prefix . "`{$typeName}`" . $suffix;
	}

	/**
	 * Escape content for use in a Markdown table cell
	 */
	private function escapeTableCell(string $text): string
	{
		// Replace pipe characters and newlines
		return str_replace(['|', "\n", "\r"], ['\\|', ' ', ''], $text);
	}

	private function slugify(string $text): string
	{
		$text = strtolower($text);
		$text = preg_replace('/[^a-z0-9]+/', '-', $text);
		return trim($text, '-');
	}
}