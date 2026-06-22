<?php

// This file contains all MCP tools and resources

use Mcp\Capability\Attribute\McpTool;
use Symfony\Component\Filesystem\Path;
require_once __DIR__ . '/utils/toAbsolute.php';


class ConvertToFormat
{
    #[McpTool(
    	name: 'convert',
    	description: 'Convert a file using the Zamzar API',
    	inputSchema: [
        	'type' => 'object',
        	'properties' => [
            		'API_key' => ['type' => 'string'],
            		'source_file' => ['type' => 'string'],
            		'target_format' => ['type' => 'string'],
            		'target_path' => ['type' => 'string'],
        	],
        	'required' => [
            		'API_key',
            		'source_file',
            		'target_format',
            		'target_path'
        ]
    ]
)]

    public function convert(string $API_key, string $source_file, string $target_format, string $target_path)
    {
	// converts any relative paths to absolute for processing
	// throws error in event of invalid path
	try{
		$resolved_src = toAbsolute($source_file);
		$resolved_target = toAbsolute($target_path);
		if (realpath($resolved_src)===false) { // invalid source path
			throw new \InvalidArgumentException("Invalid source path: {$source_file}");
		} else if (realpath($resolved_target)===false){ // invalid target path
			throw new \InvalidArgumentException("Invalid target path: {$target_path}");
		} else {
			// if valid, proceed as normal
			$source_file = $resolved_src;
			$target_path = $resolved_target;
		}
	} catch (\InvalidArgumentException $e) {
		// return target error message
		return [ 
			'error' => [ 
				'message' => $e->getMessage(), 
			] 
		]; 
	}
	
	try{

		// Connect to the Production API using an API Key
		$zamzar = new \Zamzar\ZamzarClient($API_key);

		// Submit a conversion job
		$job = $zamzar->jobs->create([
    			'source_file' => $source_file,
    			'target_format' => $target_format
		]);

		// Wait for the job to complete (the default timeout is 60 seconds)
		$job->waitForCompletion(30);

		// Download the converted files 
		$job->downloadTargetFiles($target_path);

		// Delete the source and target files on Zamzar's servers
		$job->deleteAllFiles();

		return [
			'status' => 'ok',
			'output_path' => $target_path
		];
	
	} catch (\Throwable $e) {
		// return generic error message
		return [ 
			'error' => [ 
				'message' => $e->getMessage() 
			] 
		];
	}

    }
}

class ViewAccount
{
    #[McpTool(
        name: 'view_account',
        description: 'View Zamzar account details including credits and plan information',
        inputSchema: [
            'type' => 'object',
            'properties' => [
                'API_key' => ['type' => 'string']
            ],
            'required' => ['API_key']
        ]
    )]

    public function viewAccount(string $API_key)
    {
        try {
            $zamzar = new \Zamzar\ZamzarClient($API_key);

            // Fetch account object
            $account = $zamzar->account->get();

            // Extract plan object
            $plan = $account->plan;
	    
	    // return plan information
            return [
                'status' => 'ok',
                'account' => [
                    'production_credits_remaining' => $account->production_credits_remaining,
                    'test_credits_remaining'       => $account->test_credits_remaining,
                ],
                'plan' => [
                    'name'                  => $plan->name,
                    'price_per_month'       => $plan->price_per_month,
                    'conversions_per_month' => $plan->conversions_per_month,
                    'maximum_file_size'     => $plan->maximum_file_size,
                ]
            ];

        } catch (\Throwable $e) {
	    // 
            return [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
        }
    }
}

