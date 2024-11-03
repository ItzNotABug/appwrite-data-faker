<?php

require 'vendor/autoload.php';
require 'src/tools/faker/services/auth.php';
require 'src/tools/faker/services/databases.php';
require 'src/tools/faker/services/storage.php';
require 'src/tools/faker/services/functions.php';

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Question\ChoiceQuestion;

class AppwriteFakerTool
{
    public function __construct()
    {
    }

    public function run(Input $input, Output $output)
    {
        $helper = new QuestionHelper();
        $question = new ChoiceQuestion(
            'Which services do you want to generate data for?',
            ['All', 'Auth', 'Databases', 'Storage', 'Functions']
        );
        $question->setMultiselect(true);
        $answer = $helper->ask($input, $output, $question);
        $output->writeln("You selected: " . implode(', ', $answer));

        if (in_array('All', $answer)) {
            $auth = new Auth();
            $storage = new Storage();
            $databases = new Databases();
            $functions = new Functions();

            $auth->run($input, $output);
            $databases->run($input, $output);
            $storage->run($input, $output);
            $functions->run($input, $output);
        }

        if (in_array('Auth', $answer)) {
            $auth = new Auth();
            $auth->run($input, $output);
        }
        if (in_array('Databases', $answer)) {
            $databases = new Databases();
            $databases->run($input, $output);
        }
        if (in_array('Storage', $answer)) {
            $storage = new Storage();
            $storage->run($input, $output);
        }
        if (in_array('Functions', $answer)) {
            $functions = new Functions();
            $functions->run($input, $output);
        }
    }
}
