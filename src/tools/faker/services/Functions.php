<?php

require 'vendor/autoload.php';

use Appwrite\Faker\Client as FakerClient;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Question\Question;

class Functions
{
    protected ?FakerClient $client = null;
    protected string $endpoint;
    protected string $apiKey;
    protected string $projectId;

    public function __construct()
    {
        $this->client = new FakerClient();
        $this->endpoint = $GLOBALS['APPWRITE_ENDPOINT'];
        $this->apiKey = $GLOBALS['APPWRITE_API_KEY'];
        $this->projectId = $GLOBALS['APPWRITE_PROJECT_ID'];
    }

    private function generateFunctions(Input $input, Output $output)
    {
        $faker = Faker\Factory::create();

        $helper = new QuestionHelper();
        $question = new Question('How many functions do you want to generate? (Default: 10)', 10);
        $functionsNo = $helper->ask($input, $output, $question);

        $functions = [];
        for ($i = 0; $i < $functionsNo; $i++) {
            try {
                // create function
                $functionId = $faker->uuid;
                $functions[] = $this->client->call(FakerClient::METHOD_POST, '/functions', [
                    'content-type' => 'application/json',
                    'x-appwrite-project' => $this->projectId,
                    'x-appwrite-key' => $this->apiKey,
                ], [
                    'functionId' => $functionId,
                    'name' => 'Starter Sample',
                    // available by default on cloud and self-hosted
                    'runtime' => 'node-16.0',
                    'entrypoint' => 'src/main.js',
                    "execute" => ["any"],
                ], false);
            } catch (Exception $e) {
                $output->writeln('Error: ' . $e->getMessage());
            }

        }
        return $functions;
    }

    private function generateDeployments($output, $functions)
    {
        $deployments = [];
        $codeFile = new \CURLFile('src/tools/faker/resources/code.tar.gz', 'application/gzip', 'code.tar.gz');

        for ($i = 0; $i < count($functions); $i++) {
            $function = $functions[$i];
            $functionBody = json_decode($function['body'], true);
            $functionId = $functionBody['$id'];

            try {
                $deployments[] = $this->client->call(FakerClient::METHOD_POST, '/functions/' . $functionId . '/deployments', [
                    'content-type' => 'multipart/form-data',
                    'x-appwrite-project' => $this->projectId,
                    'x-appwrite-key' => $this->apiKey,
                ], [
                    'activate' => true,
                    'code' => $codeFile
                ]);
            } catch (Exception $e) {
                $output->writeln('Error: ' . $e->getMessage());
            }
        }

        return $deployments;
    }

    public function run(Input $input, Output $output)
    {
        $functions = $this->generateFunctions($input, $output);
        if (empty($functions)) {
            $output->writeln('No functions were generated');
        } else {
            $output->writeln('Functions generated: ' . count($functions));
        }

        $deployments = $this->generateDeployments($output, $functions);
        if (empty($deployments)) {
            $output->writeln('No deployments were generated');
        } else {
            $output->writeln('Deployments generated: ' . count($deployments));
        }
    }
}
