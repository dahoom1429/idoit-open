<?php

namespace idoit\Console;

use DirectoryIterator;
use FilesystemIterator;
use idoit\Console\Command\AbstractCommand;
use idoit\Console\Subscriber\CommandEvents;
use idoit\Psr4AutoloaderClass;
use isys_application;
use isys_component_session;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IdoitConsoleApplication extends Application
{
    /**
     * IdoitConsoleApplication constructor.
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('i-doit console utility', isys_application::instance()->info['version']);
        $this->loadCommands();
    }

    public function useEventDispatcher(EventDispatcherInterface $dispatcher = null)
    {
        $this->setDispatcher($dispatcher ?: $this->createDispatcher());
    }

    /**
     * Creates an EventDispatcher Instance with pre configured event handling via Subscriber
     *
     * @return EventDispatcher
     */
    private function createDispatcher()
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addSubscriber(new CommandEvents());

        return $dispatcher;
    }

    protected function loadCommands()
    {
        $classes = require __DIR__ . '/../../../src/classmap.inc.php';

        $commands = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src/idoit/Console/Command', FilesystemIterator::SKIP_DOTS));

        /**
         * @var $file SplFileInfo
         */
        foreach ($iterator as $file) {
            // Exclude dot, abstract classes, and interfaces
            if ($file->isDir() || stripos($file->getBasename(), 'abstract') !== false || stripos($file->getBasename(), 'interface') !== false ||
                stripos($file->getBasename(), 'command') === false) {
                continue;
            }

            $namespace = str_replace('/', '\\', str_replace($file->getFilename(), '', str_replace('src/', '', $file->getPathname())));
            $class = $namespace . $file->getBasename('.' . $file->getExtension());

            $commands[$namespace . $file->getBasename('.' . $file->getExtension())] = $file->getPathname();
        }

        /**
         * Collect commands for add-ons
         */
        $iterator = new DirectoryIterator('src/classes/modules');

        /**
         * @var $file SplFileInfo
         */
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                continue;
            }

            if (!is_dir($file->getPathname() . '/src/Console/Command')) {
                continue;
            }

            // Take care of psr-4 autoloading for commands (init.php needs database connection to get loaded)
            Psr4AutoloaderClass::factory()
                ->addNamespace(
                    'idoit\Module\\' . ucfirst($file->getBasename()) . '',
                    $file->getPathname() . '/src/'
                );

            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($file->getPathname() . '/src/Console/Command', FilesystemIterator::SKIP_DOTS));

            /**
             * @var $addOn SplFileInfo
             */
            foreach ($iterator as $addOn) {
                // Exclude dot, abstract classes, and interfaces
                if ($addOn->isDir() || stripos($addOn->getBasename(), 'abstract') !== false || stripos($addOn->getBasename(), 'interface') !== false ||
                    stripos($addOn->getBasename(), 'command') === false) {
                    continue;
                }

                $namespace = 'idoit\Module' . str_replace(
                    'src',
                    ucfirst($file->getBasename()),
                    str_replace('/', '\\', str_replace($addOn->getFilename(), '', str_replace($file->getPathname(), '', $addOn->getPathname())))
                );

                $commands[$namespace . $addOn->getBasename('.' . $addOn->getExtension())] = $addOn->getPathname();
            }

            unset($iterator);
        }

        $commands += array_filter($classes, function ($file, $class) {
            return stripos($class, '\\Command\\') !== false && stripos($file, 'src/classes/modules') !== false;
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($commands as $class => $file) {
            // @see  ID-6904  We should always check if a class exists.
            if (!class_exists($class)) {
                continue;
            }

            $command = new $class();

            if ($command instanceof AbstractCommand) {
                $command->setSession(isys_component_session::instance(null));
                $command->setContainer(isys_application::instance()->container);
            }

            $this->add($command);
        }
    }
}
