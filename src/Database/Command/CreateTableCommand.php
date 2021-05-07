<?php declare(strict_types=1);

/*
 * This file is part of the Swift Framework
 *
 * (c) Henri van 't Sant <henri@henrivantsant.dev>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Swift\Database\Command;

use JetBrains\PhpStorm\Deprecated;
use Swift\Console\Command\Command;
use Swift\Database\DatabaseDriver;
use Swift\Kernel\Attributes\Autowire;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class CreateTableCommand
 * @package Swift\Database\Command
 */
#[Autowire, Deprecated]
class CreateTableCommand extends Command {

	/**
	 * @var DatabaseDriver $database
	 */
	private $database;

	/**
	 * the name of the command (the part after "bin/henri")
	 * @var string $defaultName
	 */
	protected static $defaultName = 'database:table:create';

	/**
	 * @var array   $tableColumns
	 */
	private $tableColumns = array();

	/**
	 * CreateTableCommand constructor.
	 *
	 * @param DatabaseDriver $database
	 */
	public function __construct(
		DatabaseDriver  $database
	)
	{
		$this->database = $database;

		parent::__construct();
	}

	/**
	 * Method to set command configuration
	 */
	protected function configure() {
		$this
			// the short description shown while running "php bin/console list"
			->setDescription('Create a table')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('This command will create a table')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$helper     = $this->getHelper('question');

		$tableNameQ = new Question('Wat is the unprefixed name of the new table?');
		$tableName  = $helper->ask($input, $output, $tableNameQ);
		$output->writeln('You chose to create '. $tableName);

		$dropTableQ = new ConfirmationQuestion('Drop table if it allready exists (Y/N)?', false);
		$dropTable  = $helper->ask($input, $output, $dropTableQ);
		$dropTblWord= $dropTable ? '' : 'not ';
		$output->writeln('Table ' . $tableName . ' will ' . $dropTblWord . 'be dropped if it already exists');

		$output->writeln('Now add some columns. Follow the given examples');
		$output->writeln('id int(11) NOT NULL PRIMARY KEY');
		$output->writeln('title varchar(255) NOT NULL');
		$output->writeln('date datetime NOT NULL');

		$this->createTableColumns($input, $output, $helper);

		$confirm    = new ConfirmationQuestion('Ready to create table ' . $tableName . ' with ' . count($this->tableColumns) . ' columns. Do you wish to continue (Y/N)?', false);
		if (!$helper->ask($input, $output, $confirm)) {
			return 0;
		}

		$this->createTable($input, $output, $tableName, $dropTable);


		return 0;
	}

	private function createTableColumns(InputInterface $input, OutputInterface $output, $helper, int $depth = 0) {
		if ($depth > 30) {
			$output->writeln('Max columns exceeded, can not add one more row');
			return;
		}

		$tableRowQ  = new Question('Specify the row to create: ');
		$tableRow   = $helper->ask($input, $output, $tableRowQ);

		$confTblRow = new ConfirmationQuestion('You want to create '. $tableRow . ' is this correct (Y/N)?', true);
		if (!$helper->ask($input, $output, $confTblRow)) {
			$tryAgain   = new ConfirmationQuestion('Column not submitted, try again? (Y/N)', true);
			if ($helper->ask($input, $output, $tryAgain)) {
				$depth++;
				$this->createTableColumns($input, $output, $helper, $depth);
			} else {
				return;
			}
		} else {
			array_push($this->tableColumns, $tableRow);
			$tryAgain   = new ConfirmationQuestion('Column submitted, add another one? (Y/N)', true);
			if ($helper->ask($input, $output, $tryAgain)) {
				$depth++;
				$this->createTableColumns($input, $output, $helper, $depth);
			} else {
				return;
			}
		}
	}

	/**
	 * Method to get table column data from user
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @param string          $tableName
	 * @param bool            $dropTable
	 */
	private function createTable(InputInterface $input, OutputInterface $output, string $tableName, bool $dropTable) : void {
		$queryBody = $this->parseQueryBody();

		try {
			$output->writeln('Creating table ' . $tableName);

			if ($dropTable) {
				$this->database->query('DROP TABLE if EXISTS ' . $this->database->getPrefix() . $tableName);
			}

			$query = 'CREATE TABLE ' . $this->database->getPrefix() . $tableName . ' (' . $queryBody . ')';
			$this->database->query($query);

			$output->writeln('Tabled created.. you will still have to create an Entity to access it');

		} catch (\Exception $exception) {
			$output->writeln($exception->getMessage());
		}
	}

	/**
	 * Method to parse query body
	 *
	 * @return string
	 */
	private function parseQueryBody() : string {
		$body       = '';
		$primaryKey = '';

		foreach ($this->tableColumns as $key => $column) {
			if (strpos($column, 'PRIMARY KEY') !== false) {
				$primaryKey = trim(explode(' ', $column)[0]);
				$column     = trim(str_replace('PRIMARY KEY', '', $column)) . ' AUTO_INCREMENT';
			}

			$body   .= $column;
			if ($key < (count($this->tableColumns) - 1)) {
				$body .= ',';
			}
		}

		if ($primaryKey) {
			$body .= ',PRIMARY KEY(' . $primaryKey . ')';
		}

		return $body;
	}

}