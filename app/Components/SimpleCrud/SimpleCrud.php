<?php

namespace App\Components;

use App\Model\tableFacade;
use App\Utils\Paginator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\SessionSection;

/**
 * Class SimpleCrud
 *
 * This class is responsible for handling CRUD operations for a table.
 */
class SimpleCrud extends Control
{

	/**
	 * The default value for the first page, used when no value is specified.
	 *
	 * @var int
	 */
	const firstPageDefault = 0;

	/**
	 * The default number of items per page, used when no value is specified.
	 *
	 * @var int
	 */
	const itemsPerPageDefault = 10;

	/**
	 * The default count of direct links to be shown, used when no value is specified.
	 *
	 * @var int
	 */
	const directLinksCountDefault = 3;

	/**
	 * The value of the foreign key.
	 *
	 * @var int|null
	 */
	public ?int $foreignKeyValue = null;

	/**
	 * The session object used to manage user session data.
	 *
	 * @var object
	 */
	protected SessionSection $session;


	/**
	 * Class constructor.
	 *
	 * @param string      $name        The name parameter.
	 * @param tableFacade $tableFacade The tableFacade parameter.
	 *
	 * @return void
	 */
	public function __construct(public string $name, public tableFacade $tableFacade)
	{
	}


	/**
	 * Render the view for the SimpleCrud class.
	 *
	 * @return void
	 */
	public function render(): void
	{
		$session = $this->getSession();
		$sort = $session->get('sort');
		if ($sort == null) {
			$sort = ['column' => $this->tableFacade->getTableValueName(), 'direction' => 'ASC'];
		}
		$page = (int)$session->get('page') ?: self::firstPageDefault;
		$itemsPerPage = (int)$session->get('itemsPerPage') ?: self::itemsPerPageDefault;

		$this->template->items = $this->tableFacade->get_items($sort['column'], $sort['direction'], $page, $itemsPerPage);
		$this->template->isParentTable = $this->tableFacade->getParentTableName() == null ? true : false;
		$this->template->paginator = $this->getPaginator($page, $itemsPerPage, $this->template->items->count('*'), self::directLinksCountDefault);

		$this->template->render(__DIR__ . '/templates/SimpleCrud.latte');
	}

	/**
	 * Handle the edit action for an item.
	 *
	 * @param int $itemId The ID of the item to edit.
	 *
	 * @return void
	 */
	public function handleEdit(int $itemId): void
	{
		$item = $this->tableFacade->get_item($itemId);
		if ($item) {
			$this->getComponent('editForm')->setDefaults($item->toArray());
			$this->redrawControl('snippetEditForm');
		} else {
			$this->error('Item does not exist!');
		}
	}

	/**
	 * handle item deletion confirmation.
	 *
	 * @param int $itemId The ID of the item to be confirmed.
	 *
	 * @return void
	 */
	public function handleConfirm(int $itemId): void
	{
		$item = $this->tableFacade->get_item($itemId);
		if ($item) {
			$this->template->confirmItem = $item;
			$this->redrawControl('snippetConfirmForm');
		} else {
			$this->error('Item does not exist!');
		}
	}

	/**
	 * Delete an item from the database.
	 *
	 * @param int $itemId The ID of the item to delete.
	 *
	 * @return void
	 */
	public function handleDelete(int $itemId): void
	{
		$item = $this->tableFacade->get_item($itemId);
		if ($item) {
			try {
				$this->tableFacade->delete($itemId);
			} catch (\Nette\Database\ForeignKeyConstraintViolationException $e) {
				$this->flashMessage('The list cannot be deleted because it contains data!', 'error');
			}
			$this->redrawControl('snippetFlash');
			$this->redrawControl('snippetItemTable');
			$this->redrawControl('paginator');
		} else {
			$this->error('Item does not exist!');
		}
	}


	/**
	 * Handle the "Add" action for the SimpleCrud class.
	 *
	 * This method is responsible for redrawing the "snippetEditForm" control.
	 *
	 * @return void
	 */
	public function handleAdd(): void
	{
		$this->redrawControl('snippetEditForm');
	}


	/**
	 * Handle sorting of the view.
	 *
	 * @param string $column The column to be sorted
	 *
	 * @return void
	 */
	public function handleSort($column): void
	{
		$session = $this->getSession();
		$newsort['column'] = $column;
		$sort = $session->get('sort');
		// In case the session does not exist, we set the sort field with default values
		if (!is_array($sort)) {
			$sort = ['column' => 'name', 'direction' => 'asc'];
		}
		// if a column other than the storage column is selected, I set the default sort in ascending order
		if ($sort['column'] != $column) {
			$newsort['direction'] = 'asc';
		} else {
			if ($sort['direction'] == 'asc') {
				$newsort['direction'] = 'desc';
			} else {
				$newsort['direction'] = 'asc';
			}
		}
		$this->getSession()->set('sort', $newsort);
		$this->redrawControl('snippetItemTable');
	}

	/**
	 * Handle the change in the number of items per page.
	 *
	 * @param int $itemsPerPage The new number of items per page.
	 *
	 * @return void
	 */
	public function handleItemsPerPage($itemsPerPage): void
	{
		$session = $this->getSession();
		$session->set('itemsPerPage', $itemsPerPage);
		// when redrawing, the number of pages changes, so I always go back to the beginning
		$session->set('page', 0);
		$this->redrawControl('snippetItemTable');
		$this->redrawControl('paginator');
	}


	/**
	 * Handle the page change event.
	 *
	 * @param int $page The new page number.
	 *
	 * @return void
	 */
	public function handlePage($page): void
	{
		$session = $this->getSession();
		$session->set('page', $page);
		$this->redrawControl('snippetItemTable');
		$this->redrawControl('paginator');
	}

	/**
	 * Handle the "show" action for an item.
	 * redirects to the current presenter items:$itemId
	 *
	 * @param int $itemId The ID of the item to show.
	 *
	 * @return void
	 */
	public function handleShow($itemId): void
	{
		$this->presenter->redirect('items', $itemId);
	}


	/**
	 * Create the component for the edit form.
	 *
	 * This method creates a Form object and initializes it with the necessary form fields and buttons.
	 * The form is protected against CSRF attacks. It contains a hidden field for the item ID,
	 * a text field for the item name (with a required validation), a button for canceling the edit,
	 * and a submit button for saving the changes. The "editFormSucceeded" method of the current instance
	 * will be called when the form is successfully submitted.
	 *
	 * @return Form The initialized edit form.
	 */
	protected function createComponentEditForm(): Form
	{
		$form = new Form;
		$form->addProtection();
		$form->addHidden('id');
		$form->addText('name', 'Název:')->setRequired('Fill in the name of the item');
		$form->AddButton('storno', 'Storno');
		$form->addSubmit('send', 'Save');
		$form->onSuccess[] = [$this, 'editFormSucceeded'];
		return $form;
	}


	/**
	 * Handle the form submission for editing a record.
	 *
	 * @param Form  $form The form object containing the submitted data.
	 * @param array $data The submitted data from the form.
	 *
	 * @return void
	 */
	public function editFormSucceeded(Form $form, array $data): void
	{
		if (isset($data['id']) and (int)$data['id'] > 0) {
			$updatedRows = $this->tableFacade->update($data);
			if ($updatedRows == 0) {
				$this->flashMessage("The item has not been updated.", 'error');
			} else {
				$this->flashMessage("The item has been updated successfully.", 'success');
			}
		} else {
			$insert = $this->tableFacade->add($data);
			if ($insert) {
				$this->flashMessage("Item added successfully.", 'success');
			} else {
				$this->flashMessage("Failed to add item.", 'error');
			}
		}
		$this->presenter->payload->saved = true;
		$this->redrawControl('snippetFlash');
		$this->redrawControl('snippetEditForm');
		$this->redrawControl('snippetItemTable');
	}

	/**
	 * Get the paginator instance.
	 *
	 * @param int $currentPage      The current page number.
	 * @param int $itemsPerPage     The number of items to display per page.
	 * @param int $itemCount        The total number of items.
	 * @param int $directLinksCount The number of direct links to display before and after the current page. Default is 3.
	 *
	 * @return Paginator Returns an instance of the Paginator class.
	 */
	protected function getPaginator(int $currentPage, int $itemsPerPage, int $itemCount, int $directLinksCount = 3): Paginator
	{
		$paginator = new Paginator();
		$paginator->setBase(0);
		$paginator->setPage($currentPage);
		$paginator->setItemsPerPage($itemsPerPage);
		$paginator->setItemCount($itemCount);
		$paginator->setDirectLinksCount($directLinksCount);
		return $paginator;
	}

	/**
	 * Get the session section for the current presenter.
	 *
	 * @return SessionSection|\Nette\Http\Session The session section for the current presenter.
	 */
	protected function getSession(): SessionSection|\Nette\Http\Session
	{
		return $this->presenter->getSession($this->name . '-' . $this->tableFacade->getTableName() . '-' . (string)$this->tableFacade->getForeignKeyValue());
	}
}
