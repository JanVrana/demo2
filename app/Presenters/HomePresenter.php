<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\SimpleCrud;
use App\Model\tableFacade;
use Nette;

/**
 * Class HomePresenter
 *
 * Presenter for handling home related actions and views.
 */
class HomePresenter extends Nette\Application\UI\Presenter
{

	/**
	 * Constructor function for the class.
	 *
	 * @param \Nette\Database\Explorer $database The database object to be used for database operations.
	 *
	 * @return void
	 */
	public function __construct(private Nette\Database\Explorer $database)
	{
	}

	/**
	 * Method that is called before rendering the page.
	 * starts the session - because then when using csrf protection the web does not work
	 * @return void
	 */
	public function beforeRender(): void
	{
		parent::beforeRender();
		$session = $this->getSession();
		$session->start();
	}

	/**
	 * Renders the default view.
	 */
	public function renderDefault(): void
	{
	}

	/**
	 * Renders views items for a given foreign key value
	 *
	 * @param int $foreignKeyValue The foreign key value
	 *
	 * @return void
	 */
	public function renderItems(int $foreignKeyValue): void
	{
		$tableFacadeList = $this->getTableFacadeList();
		$this->template->list = $tableFacadeList->get_item((int)$foreignKeyValue);
	}

	/**
	 * Creates a SimpleCrudList component.
	 *
	 * @return SimpleCrud The created SimpleCrudList component.
	 */
	public function createComponentSimpleCrudList(): SimpleCrud
	{
		$tableFacade = $this->getTableFacadeList();
		$crud = new SimpleCrud('CrudList', $tableFacade);
		return $crud;
	}

	/**
	 * Creates an instance of SimpleCrud for managing a CrudItem entity.
	 *
	 * @return SimpleCrud
	 */
	public function createComponentSimpleCrudItem(): SimpleCrud
	{
		$tableFacate = $this->getTableFacadeItem();
		$crud = new SimpleCrud('CrudItem', $tableFacate);
		return $crud;
	}

	/**
	 * Returns a new instance of tableFacade with predefined settings.
	 *
	 * @return tableFacade A new instance of tableFacade with the table name set to 'list',
	 *                    the table ID name set to 'id', and the table value name set to 'name'.
	 */
	protected function getTableFacadeList(): tableFacade
	{
		$tableFacade = new tableFacade($this->database);
		$tableFacade->setTableName('list');
		$tableFacade->setTableIdName('id');
		$tableFacade->setTableValueName('name');
		return $tableFacade;
	}


	/**
	 * Returns an instance of tableFacade.
	 *
	 * This method creates a new instance of tableFacade and sets the necessary properties for the item table.
	 * The table name is set to 'item', the ID column is set to 'id', and the value column is set to 'name'.
	 * Additionally, the parent table name is set to 'list', the parent table ID column is set to 'id',
	 * and the foreign key column is set to 'list_id'. The foreign key value is retrieved from the 'foreignKeyValue'
	 * parameter.
	 *
	 * @return tableFacade An instance of tableFacade.
	 */
	protected function getTableFacadeItem(): tableFacade
	{
		$tableFacade = new tableFacade($this->database);
		$tableFacade->setTableName('item');
		$tableFacade->setTableIdName('id');
		$tableFacade->setTableValueName('name');
		$tableFacade->setParenTableName('list');
		$tableFacade->setParenTableIdName('id');
		$tableFacade->setForeignKeyName('list_id');
		// when the signal is triggered, the render method of the presenter does not start, so I take the 'foreignKeyValue' value from the parameter
		$tableFacade->setForeignKeyValue((int)$this->getParameter('foreignKeyValue'));
		return $tableFacade;
	}


}
