<?php

use AutoDocumentation\Attributes\ApiDoc;
use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Endpoint;
use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Response;
use AutoDocumentation\Attributes\Returns;

include 'vendor/autoload.php';


$docs = new AutoDocumentation\AutoDocumentation();

$docs->registerTypes([
	ApiDoc::class,
	Documentable::class,
	Endpoint::class,
	Method::class,
	Param::class,
	Response::class,
	Returns::class,
]);

$docs->saveMarkdown('README.md');

