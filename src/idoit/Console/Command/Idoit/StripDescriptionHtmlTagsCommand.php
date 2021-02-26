<?php

namespace idoit\Console\Command\Idoit;

use idoit\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StripDescriptionHtmlTagsCommand extends AbstractCommand
{
    const NAME = 'strip-description-html';

    /**
     * @var OutputInterface
     */
    private $output;

    private $database;

    /**
     * Get name for command
     *
     * @return string
     */
    public function getCommandName()
    {
        return self::NAME;
    }

    /**
     * Get description for command
     *
     * @return string
     */
    public function getCommandDescription()
    {
        return 'With this command you can strip html tags in description field of all categories and objects';
    }

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    public function getCommandDefinition()
    {
        $definition = new InputDefinition();

        return $definition;
    }

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    public function isConfigurable()
    {
        return true;
    }

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    public function getCommandUsages()
    {
        return [];
    }

    /**
     * @author Illia Polianskyi
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->database = \isys_application::instance()->container->get('database');

        $this->processAllCatDescriptions('isys_obj');

        $catgResult = $this->database->query('select isysgui_catg__source_table from isysgui_catg;');
        while ($row = $catgResult->fetch_assoc()) {
            $catgTableName = $row['isysgui_catg__source_table'] . '_list';
            $this->processAllCatDescriptions($catgTableName);
        }

        $catsResult = $this->database->query('select isysgui_cats__source_table from isysgui_cats;');
        while ($row = $catsResult->fetch_assoc()) {
            $catsTableName = $row['isysgui_cats__source_table'];
            $this->processAllCatDescriptions($catsTableName);
        }

        $this->processAllCatDescriptions('isys_catg_custom_fields_list');

        $output->writeln('HTML in description fields of all categories have been successfully processed');
        return Command::SUCCESS;
    }

    /**
     * method for processing category tables with description fields, stripping tags from them
     * @author Illia Polianskyi
     *
     * @param string $catTableName
     */
    private function processAllCatDescriptions(string $catTableName) : void
    {
        $idFieldName = $catTableName . '__id';
        $descriptionFieldName = $catTableName . '__description';
        if (!\isys_cmdb_dao::instance($this->database)->fieldsExistsInTable($catTableName, [$descriptionFieldName])) {
            return;
        }
        $sql = "SELECT " . $idFieldName . ", " . $descriptionFieldName . " FROM " . $catTableName . " WHERE (" . $descriptionFieldName . " IS NOT NULL) AND (" . $descriptionFieldName . " != '');";
        $result = $this->database->query($sql);
        while ($row = $result->fetch_assoc()) {
            $id = $row[$idFieldName];
            $descr = $row[$descriptionFieldName];
            $descr = $this->processHtml($descr);
            $this->database->query("UPDATE " . $catTableName . " SET " . $descriptionFieldName . " = '" . $descr . "' WHERE " . $idFieldName . " = " . $id);
        }
    }

    /**
     * Method for removing tags and appending whitespaces between them
     * @url https://www.php.net/manual/en/function.strip-tags
     *
     * @param $string
     * @return string
     */
    private function processHtml($string) : string
    {
        // ----- replace BR TAGs -----
        $string = preg_replace('/<br\s*\/?>/i', "\n", $string);
        // ----- remove HTML TAGs -----
        $string = preg_replace('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\t", ' ', $string);   // --- replace with space

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/\s+/', ' ', $string));
        $string = trim(preg_replace('/(\r\n|\r|\n)+/', "\n", $string));

        return $string;
    }
}
