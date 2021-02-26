<?php

namespace idoit\Module\Console\Console\Command\idoit\Update;

use idoit\Module\Console\Steps\Step;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * The update entry point - interacts with user to prepare the parameters and calls 2 sub commands
 */
class UpdateCommand extends UpdateBase
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|mixed|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $exitCode = $this->callSubcommand('update-1', $input, $output);

        if ($exitCode !== Command::SUCCESS) {
            return $exitCode;
        }

        $exitCode = $this->callSubcommand('update-2', $input, $output);

        if ($exitCode !== Command::SUCCESS) {
            return $exitCode;
        }

        return Command::SUCCESS;
    }

    /**
     * @param string          $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    private function callSubcommand(string $command, InputInterface $input, OutputInterface $output)
    {
        global $g_absdir;

        $phpBinaryFinder = new PhpExecutableFinder();
        $phpBinaryPath = $phpBinaryFinder->find();
        $parameters = [
            $phpBinaryPath,
            "$g_absdir/console.php",
            $command,
            '--ansi'
        ];

        foreach ($input->getOptions() as $key => $value) {
            $value = $this->getOptionValue($key) ?? $value;

            if (is_bool($value)) {
                if ($value) {
                    $parameters[] = '--' . $key;
                }

                continue;
            }

            $parameters[] = '--' . $key . '=' . $value;
        }

        $step1 = new Process($parameters);
        $step1->run();

        $output->write($step1->getIncrementalOutput());
        $output->write($step1->getErrorOutput());

        return $step1->getExitCode();
    }

    /**
     * Create the work
     *
     * @return Step
     */
    protected function createStep()
    {
        return null;
    }
}
