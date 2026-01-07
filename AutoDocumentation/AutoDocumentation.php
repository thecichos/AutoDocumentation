<?php

namespace AutoDocumentation;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;
use AutoDocumentation\Generator\DocGenerator;
use AutoDocumentation\Generator\TypeRegistry;
use AutoDocumentation\Renderer\HtmlRenderer;
use AutoDocumentation\Renderer\JsonRenderer;
use AutoDocumentation\Renderer\MDRenderer;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;

#[Documentable('Auto-generated documentation for your API', group: 'System')]
class AutoDocumentation
{
	private TypeRegistry $registry;
	private DocGenerator $generator;
	private HtmlRenderer $htmlRenderer;
	private JsonRenderer $jsonRenderer;
	private MDRenderer $markdownRenderer;

	/** @var class-string[] */
	private array $controllers = [];

	public function __construct()
	{
		$this->registry = new TypeRegistry();
		$this->generator = new DocGenerator($this->registry);
		$this->htmlRenderer = new HtmlRenderer($this->registry);
		$this->jsonRenderer = new JsonRenderer($this->registry);
		$this->markdownRenderer = new MDRenderer($this->registry);
	}

	/**
	 * Register model/DTO classes that should be linkable in documentation
	 *
	 * @param class-string[] $classes Classes with #[Documentable] attribute
	 */
	public function registerTypes(array $classes): self
	{
		foreach ($classes as $class) {
			$this->registry->register(new ReflectionClass($class));
		}
		return $this;
	}

	/**
	 * Register API controller classes to document
	 *
	 * @param class-string[] $classes Classes with #[ApiDoc] attribute
	 */
	public function registerControllers(array $classes): self
	{
		$this->controllers = array_merge($this->controllers, $classes);
		return $this;
	}

	/**
	 * Scan a directory for documentable types (models/DTOs)
	 */
	public function scanTypesDirectory(string $directory, string $namespace): self
	{
		$this->scanDirectory($directory, $namespace, function (string $className) {
			$this->registry->register(new ReflectionClass($className));
		});

		return $this;
	}

	/**
	 * Scan a directory for API controllers
	 */
	public function scanControllersDirectory(string $directory, string $namespace): self
	{
		$this->scanDirectory($directory, $namespace, function (string $className) {
			$this->controllers[] = $className;
		});

		return $this;
	}

	/**
	 * Generate raw documentation array
	 */
	public function generate(): array
	{
		return $this->generator->generate($this->controllers);
	}

	/**
	 * Render documentation as HTML
	 */
	public function toHtml(): string
	{
		$docs = $this->generate();
		return $this->htmlRenderer->render($docs, $this->registry->getAll());
	}

	/**
	 * Render documentation as OpenAPI 3.0 JSON
	 */
	public function toOpenApi(array $info = []): string
	{
		$docs = $this->generate();
		return $this->jsonRenderer->render($docs, $this->registry->getAll(), $info);
	}

	public function toMarkdown(array $info = []): string
	{
		$docs = $this->generate();
		return $this->markdownRenderer->render($docs, $this->registry->getAll(), $info);
	}

	/**
	 * Save HTML documentation to file
	 */
	public function saveHtml(string $filePath): self
	{
		file_put_contents($filePath, $this->toHtml());
		return $this;
	}

	/**
	 * Save OpenAPI JSON to file
	 */
	public function saveOpenApi(string $filePath, array $info = []): self
	{
		file_put_contents($filePath, $this->toOpenApi($info));
		return $this;
	}

	public function saveMarkdown(string $filePath, array $info = []): self
	{

		$f = fopen($filePath, 'w+');

		if ($f === false) {
			throw new \RuntimeException("Failed to open file for writing: $filePath");
		}
		$wrote = fwrite($f, $this->toMarkdown($info));

		if ($wrote === false) {
			throw new \RuntimeException("Failed to write to file: $filePath");
		}

		fclose($f);

		return $this;
	}

	/**
	 * Get the type registry for advanced usage
	 */
	public function getRegistry(): TypeRegistry
	{
		return $this->registry;
	}

	private function scanDirectory(string $directory, string $namespace, callable $callback): void
	{
		$directory = rtrim($directory, DIRECTORY_SEPARATOR);

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory)
		);

		foreach ($iterator as $file) {
			if ($file->isDir() || $file->getExtension() !== 'php') {
				continue;
			}

			$className = $this->resolveClassName(
				$file->getPathname(),
				$directory,
				$namespace
			);

			if (class_exists($className)) {
				$callback($className);
			}
		}
	}

	private function resolveClassName(string $filePath, string $baseDir, string $namespace): string
	{
		$relativePath = str_replace($baseDir, '', $filePath);
		$relativePath = trim($relativePath, DIRECTORY_SEPARATOR);
		$relativePath = str_replace('.php', '', $relativePath);
		$relativePath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

		return rtrim($namespace, '\\') . '\\' . $relativePath;
	}
}