<?php

namespace AutoDocumentation\Renderer;

use AutoDocumentation\Generator\TypeInfo;
use AutoDocumentation\Generator\TypeRegistry;
use AutoDocumentation\Generator\TypeRenderer;

class HtmlRenderer
{
	private TypeRenderer $typeRenderer;

	public function __construct(
		private TypeRegistry $registry
	) {
		$this->typeRenderer = new TypeRenderer($registry);
	}

	/**
	 * @param array $docs Generated documentation from DocGenerator
	 * @param TypeInfo[] $types All registered types
	 */
	public function render(array $docs, array $types): string
	{
		$sidebar = $this->renderSidebar($docs, $types);
		$content = $this->renderContent($docs, $types);

		return $this->renderLayout($sidebar, $content);
	}

	private function renderLayout(string $sidebar, string $content): string
	{
		$css = $this->getCss();

		return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>API Documentation</title>
            <style>{$css}</style>
        </head>
        <body>
            <div class="layout">
                <nav class="sidebar">
                    <div class="sidebar-header">
                        <h1>📚 API Docs</h1>
                    </div>
                    {$sidebar}
                </nav>
                <main class="content">
                    {$content}
                </main>
            </div>
            <script>{$this->getJs()}</script>
        </body>
        </html>
        HTML;
	}

	/**
	 * @param TypeInfo[] $types
	 */
	private function renderSidebar(array $docs, array $types): string
	{
		$html = '';

		// Endpoints section
		if (!empty($docs)) {
			$html .= '<div class="sidebar-section">';
			$html .= '<h2>Endpoints</h2>';
			$html .= '<ul class="nav-list">';

			foreach ($docs as $controller) {
				$html .= '<li class="nav-group">';
				$html .= '<span class="nav-group-title">' . htmlspecialchars($controller['name']) . '</span>';
				$html .= '<ul class="nav-sublist">';

				foreach ($controller['endpoints'] as $endpoint) {
					$methodClass = 'method-' . strtolower($endpoint['http_method']);
					$html .= sprintf(
						'<li><a href="#%s"><span class="method-badge %s">%s</span> %s</a></li>',
						htmlspecialchars($controller['name']."-".$endpoint['slug']),
						$methodClass,
						htmlspecialchars($endpoint['http_method']),
						htmlspecialchars($endpoint['path'])
					);
				}

				$html .= '</ul></li>';
			}

			$html .= '</ul></div>';
		}

		// Types section (grouped)
		$groupedTypes = $this->registry->getAllGrouped();

		if (!empty($groupedTypes)) {
			$html .= '<div class="sidebar-section">';
			$html .= '<h2>Types</h2>';
			$html .= '<ul class="nav-list">';

			foreach ($groupedTypes as $group => $groupTypes) {
				$html .= '<li class="nav-group">';
				$html .= '<span class="nav-group-title">' . htmlspecialchars($group) . '</span>';
				$html .= '<ul class="nav-sublist">';

				foreach ($groupTypes as $type) {
					$html .= sprintf(
						'<li><a href="%s">%s</a></li>',
						htmlspecialchars($type->getAnchor()),
						htmlspecialchars($type->shortName)
					);
				}

				$html .= '</ul></li>';
			}

			$html .= '</ul></div>';
		}

		return $html;
	}

	/**
	 * @param TypeInfo[] $types
	 */
	private function renderContent(array $docs, array $types): string
	{
		$html = '';

		// Render endpoints
		foreach ($docs as $controller) {
			$html .= $this->renderController($controller);
		}

		// Render types
		if (!empty($types)) {
			$html .= '<section class="types-section">';
			$html .= '<h2>Type Reference</h2>';

			foreach ($types as $type) {
				$html .= $this->renderType($type);
			}

			$html .= '</section>';
		}

		return $html;
	}

	private function renderController(array $controller): string
	{
		$deprecated = $controller['deprecated'] ? '<span class="badge badge-deprecated">Deprecated</span>' : '';
		$tags = '';

		foreach ($controller['tags'] as $tag) {
			$tags .= '<span class="badge badge-tag">' . htmlspecialchars($tag) . '</span>';
		}

		$html = '<section class="controller-section">';
		$html .= '<div class="controller-header">';
		$html .= '<h2>' . htmlspecialchars($controller['name']) . ' ' . $deprecated . '</h2>';
		$html .= '<p class="description">' . htmlspecialchars($controller['description']) . '</p>';
		$html .= '<div class="tags">' . $tags . '<span class="badge badge-version">v' . htmlspecialchars($controller['version']) . '</span></div>';
		$html .= '</div>';

		foreach ($controller['endpoints'] as $endpoint) {
			$html .= $this->renderEndpoint($endpoint, $controller['name']);
		}

		$html .= '</section>';

		return $html;
	}

	private function renderEndpoint(array $endpoint, string $controllerName): string
	{
		$methodClass = 'method-' . strtolower($endpoint['http_method']);
		$deprecated = $endpoint['deprecated'] ? '<span class="badge badge-deprecated">Deprecated</span>' : '';

		$html = '<div class="endpoint" id="' . htmlspecialchars($controllerName."-".$endpoint['slug']) . '">';
		$html .= '<div class="endpoint-header">';
		$html .= '<span class="method-badge ' . $methodClass . '">' . htmlspecialchars($endpoint['http_method']) . '</span>';
		$html .= '<code class="path">' . htmlspecialchars($endpoint['path']) . '</code>';
		$html .= $deprecated;
		$html .= '</div>';

		$html .= '<h3>' . htmlspecialchars($endpoint['summary']) . '</h3>';

		if (!empty($endpoint['description'])) {
			$html .= '<p class="description">' . htmlspecialchars($endpoint['description']) . '</p>';
		}

		// Parameters
		if (!empty($endpoint['parameters'])) {
			$html .= $this->renderParameters($endpoint['parameters']);
		}

		// Returns
		if ($endpoint['returns'] !== null) {
			$html .= $this->renderReturns($endpoint['returns']);
		}

		// Responses
		if (!empty($endpoint['responses'])) {
			$html .= $this->renderResponses($endpoint['responses']);
		}

		$html .= '</div>';

		return $html;
	}

	private function renderParameters(array $parameters): string
	{
		$html = '<div class="params-section">';
		$html .= '<h4>Parameters</h4>';
		$html .= '<table class="params-table">';
		$html .= '<thead><tr><th>Name</th><th>Type</th><th>In</th><th>Required</th><th>Description</th></tr></thead>';
		$html .= '<tbody>';

		foreach ($parameters as $param) {
			$typeHtml = $this->typeRenderer->renderFromString($param['type']);
			$required = $param['required'] ? '<span class="badge badge-required">Required</span>' : '<span class="badge badge-optional">Optional</span>';
			$inBadge = '<span class="badge badge-in">' . htmlspecialchars($param['in']) . '</span>';

			$description = htmlspecialchars($param['description']);

			if ($param['example'] !== null) {
				$description .= '<br><small class="example">Example: <code>' . htmlspecialchars(json_encode($param['example'])) . '</code></small>';
			}

			if ($param['has_default']) {
				$description .= '<br><small class="default">Default: <code>' . htmlspecialchars(json_encode($param['default'])) . '</code></small>';
			}

			$html .= '<tr>';
			$html .= '<td><code>' . htmlspecialchars($param['name']) . '</code></td>';
			$html .= '<td>' . $typeHtml . '</td>';
			$html .= '<td>' . $inBadge . '</td>';
			$html .= '<td>' . $required . '</td>';
			$html .= '<td>' . $description . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table></div>';

		return $html;
	}

	private function renderReturns(array $returns): string
	{
		$typeHtml = $this->typeRenderer->renderFromString($returns['type']);

		$html = '<div class="returns-section">';
		$html .= '<h4>Returns <span class="status-code">' . $returns['status_code'] . '</span></h4>';
		$html .= '<p>' . $typeHtml;

		if (!empty($returns['description'])) {
			$html .= ' — ' . htmlspecialchars($returns['description']);
		}

		$html .= '</p></div>';

		return $html;
	}

	private function renderResponses(array $responses): string
	{
		$html = '<div class="responses-section">';
		$html .= '<h4>Responses</h4>';
		$html .= '<table class="responses-table">';
		$html .= '<thead><tr><th>Status</th><th>Type</th><th>Description</th></tr></thead>';
		$html .= '<tbody>';

		foreach ($responses as $response) {
			$typeHtml = $response['type']
				? $this->typeRenderer->renderFromString($response['type'])
				: '<span class="type-builtin">—</span>';

			$statusClass = $this->getStatusClass($response['status_code']);

			$html .= '<tr>';
			$html .= '<td><span class="status-badge ' . $statusClass . '">' . $response['status_code'] . '</span></td>';
			$html .= '<td>' . $typeHtml . '</td>';
			$html .= '<td>' . htmlspecialchars($response['description']) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table></div>';

		return $html;
	}

	private function renderType(TypeInfo $type): string
	{
		$html = '<div class="type-definition" id="type-' . htmlspecialchars($type->slug) . '">';
		$html .= '<h3>' . htmlspecialchars($type->shortName) . '</h3>';
		$html .= '<p class="description">' . htmlspecialchars($type->description) . '</p>';
		$html .= '<code class="fqcn">' . htmlspecialchars($type->fqcn) . '</code>';

		if (!empty($type->properties)) {
			$html .= '<table class="properties-table">';
			$html .= '<thead><tr><th>Accessibility</th><th>Property</th><th>Type</th><th>Description</th></tr></thead>';
			$html .= '<tbody>';

			foreach ($type->properties as $prop) {
				$typeHtml = $this->typeRenderer->renderFromString($prop->type);

				if ($prop->nullable) {
					$typeHtml = '?' . $typeHtml;
				}

				$deprecated = $prop->deprecated ? '<span class="badge badge-deprecated">Deprecated</span>' : '';

				$description = htmlspecialchars($prop->description);

				if ($prop->example !== null) {
					$description .= '<br><small class="example">Example: <code>' . htmlspecialchars(json_encode($prop->example)) . '</code></small>';
				}

				$html .= '<tr>';
				$html .= '<td>'.(["<span class='badge badge-public'>public</span>", "<span class='badge badge-protected'>protected</span>", "<span class='badge badge-private'>private</span>"][$prop->accessibility]) . '</td>';
				$html .= '<td><code>' . htmlspecialchars($prop->name) . '</code> ' . $deprecated . '</td>';
				$html .= '<td>' . $typeHtml . '</td>';
				$html .= '<td>' . $description . '</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody></table>';
		}

		$html .= '</div>';

		return $html;
	}

	private function getStatusClass(int $code): string
	{
		return match (true) {
			$code >= 200 && $code < 300 => 'status-success',
			$code >= 300 && $code < 400 => 'status-redirect',
			$code >= 400 && $code < 500 => 'status-client-error',
			$code >= 500 => 'status-server-error',
			default => 'status-info'
		};
	}

	private function getCss(): string
	{
		return <<<'CSS'
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        .layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: #1a1a2e;
            color: #eee;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #333;
        }
        
        .sidebar-header h1 {
            font-size: 1.4rem;
        }
        
        .sidebar-section {
            padding: 20px;
        }
        
        .sidebar-section h2 {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #888;
            margin-bottom: 10px;
        }
        
        .nav-list {
            list-style: none;
        }
        
        .nav-group-title {
            display: block;
            font-weight: 600;
            padding: 8px 0;
            color: #ccc;
        }
        
        .nav-sublist {
            list-style: none;
            padding-left: 10px;
        }
        
        .nav-sublist li a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 0;
            color: #aaa;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        
        .nav-sublist li a:hover {
            color: #fff;
        }
        
        .content {
            flex: 1;
            margin-left: 280px;
            padding: 40px;
            max-width: 1000px;
        }
        
        .controller-section {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .controller-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .controller-header h2 {
            margin-bottom: 10px;
        }
        
        .endpoint {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .endpoint:last-child {
            border-bottom: none;
        }
        
        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        
        .endpoint h3 {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .method-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .method-get { background: #61affe; color: #fff; }
        .method-post { background: #49cc90; color: #fff; }
        .method-put { background: #fca130; color: #fff; }
        .method-patch { background: #50e3c2; color: #fff; }
        .method-delete { background: #f93e3e; color: #fff; }
        
        .path {
            font-size: 1rem;
            color: #333;
            background: #f5f5f5;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-deprecated { background: #f93e3e; color: #fff; }
        .badge-tag { background: #e0e0e0; color: #666; }
        .badge-version { background: #1a1a2e; color: #fff; }
        .badge-required { background: #f93e3e; color: #fff; }
        .badge-optional { background: #49cc90; color: #fff; }
        .badge-in { background: #61affe; color: #fff; }
        .badge-public { background: #61affe; color: #fff; }
        .badge-protected { background: #fca130; color: #fff; }
        .badge-private { background: #f93e3e; color: #fff; }
        
        .tags {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        
        .params-section, .returns-section, .responses-section {
            margin-top: 20px;
        }
        
        .params-section h4, .returns-section h4, .responses-section h4 {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f9f9f9;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #666;
        }
        
        .type-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: 500;
        }
        
        .type-link:hover {
            text-decoration: underline;
        }
        
        .type-builtin {
            color: #6b21a8;
        }
        
        .example, .default {
            color: #888;
        }
        
        .status-code {
            background: #49cc90;
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .status-success { background: #49cc90; color: #fff; }
        .status-redirect { background: #61affe; color: #fff; }
        .status-client-error { background: #fca130; color: #fff; }
        .status-server-error { background: #f93e3e; color: #fff; }
        
        .types-section {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .types-section > h2 {
            margin-bottom: 20px;
        }
        
        .type-definition {
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        .type-definition:last-child {
            border-bottom: none;
        }
        
        .type-definition h3 {
            margin-bottom: 8px;
        }
        
        .fqcn {
            display: block;
            font-size: 0.85rem;
            color: #888;
            margin: 10px 0;
        }
        
        .description {
            color: #666;
        }
        CSS;
	}

	private function getJs(): string
	{
		return <<<'JS'
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    history.pushState(null, '', this.getAttribute('href'));
                }
            });
        });
        
        // Highlight current section in sidebar
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const id = entry.target.getAttribute('id');
                const link = document.querySelector(`a[href="#${id}"]`);
                if (link) {
                    if (entry.isIntersecting) {
                        link.style.color = '#fff';
                    } else {
                        link.style.color = '';
                    }
                }
            });
        }, { threshold: 0.5 });
        
        document.querySelectorAll('.endpoint, .type-definition').forEach(el => {
            observer.observe(el);
        });
        JS;
	}
}