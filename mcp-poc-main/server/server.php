#!/usr/bin/env php
<?php

// This file is an entry point that starts
// the server and registers any tools.

declare(strict_types=1);

require_once __DIR__ . '/../dependencies/vendor/autoload.php';
require_once __DIR__ . '/elements.php';

use Mcp\Server;
use Mcp\Server\Transport\StdioTransport;

// initialising server
$server = Server::builder()
    ->setServerInfo('Zamzar MCP Server', '1.0.0')
    // manual registration of MCP tools
    ->addTool([ConvertToFormat::class, 'convert'], 'convert')
    ->addTool([ViewAccount::class, 'viewAccount'],'view_account')
    // ->setDiscovery(__DIR__, ['.'])
    // The above line should facilitate auto-discovery of MCP tools (functions marked with
    // the #[McpTool] attribute in elements.php). I couldn't get it to work in testing so I
    // registered the tools manually using addTool() instead.
    ->build();


$transport = new StdioTransport();

$server->run($transport);
