<?php

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

/**
 * Class tableFacade
 *
 * This class acts as a facade for interacting with a database table.
 *
 * @package App\Facade
 */
class tableFacade
{

	/**
	 * @var string|null $tableName the name of the table
	 */
	protected ?string $tableName = null;

	/**
	 * @var string|null the name of the primary key in the table
	 */
	protected ?string $tableIdName = 'id';

	/**
	 * @var string|null the name of the value column in the table
	 */
	protected ?string $tableValueName = 'name';

	/**
	 * @var string|null the name of the parent table.
	 */
	protected ?string $parenTableName = null;
	/**
	 * @var string $parenTableIdName The name of the ID column in the parent table. Can be null if there is no foreign
	 *                               key relationship.
	 */
	protected ?string $parenTableIdName = 'id';

	/**
	 * @var string|null $foreignKeyName The name of the foreign key column. Can be null if there is no foreign key
	 *                                  relationship.
	 */
	protected ?string $foreignKeyName = null;

	/**
	 * @var mixed $foreignKeyValue The value of the foreign key column. Can be null if there is no foreign key
	 *                             relationship.
	 */
	protected ?int $foreignKeyValue = null;

	/**
	 * __construct method for initializing the object of the class.
	 *
	 * @param Explorer $database Object of Explorer class used for database operations.
	 */
	public function __construct(private Explorer $database,)
	{

	}

	/**
	 * Retrieves the table name associated with the current object.
	 *
	 * @return string|null The table name associated with the current object, or null if not set.
	 */
	public function getTableName(): ?string
	{
		return $this->tableName;
	}

	/**
	 * Set the table name.
	 *
	 * @param string|null $tableName The name of the table. If null, no table name will be set.
	 *
	 * @return tableFacade The instance of the tableFacade class.
	 */
	public function setTableName(?string $tableName): tableFacade
	{
		$this->tableName = $tableName;
		return $this;
	}

	/**
	 * Gets the table ID name.
	 *
	 * @return null|string The table ID name, or null if it is not set.
	 */
	public function getTableIdName(): ?string
	{
		return $this->tableIdName;
	}

	/**
	 * Sets the ID name for the table.
	 *
	 * @param string|null $tableIdName The ID name for the table.
	 *
	 * @return tableFacade The current instance of the tableFacade.
	 */
	public function setTableIdName(?string $tableIdName): tableFacade
	{
		$this->tableIdName = $tableIdName;
		return $this;
	}

	/**
	 * Retrieves the name of the table value.
	 *
	 * @return string|null The name of the table value, or null if it is not set.
	 */
	public function getTableValueName(): ?string
	{
		return $this->tableValueName;
	}

	/**
	 * Set the table value name.
	 *
	 * @param string|null $tableValueName The value to set as the table value name.
	 *
	 * @return tableFacade The tableFacade instance.
	 */
	public function setTableValueName(?string $tableValueName): tableFacade
	{
		$this->tableValueName = $tableValueName;
		return $this;
	}

	/**
	 * Retrieves the parent table name.
	 *
	 * @return string|null The name of the parent table, or null if it is not set.
	 */
	public function getParentTableName(): ?string
	{
		return $this->parenTableName;
	}

	/**
	 * Sets the parent table name.
	 *
	 * @param string|null $parenTableName The name of the parent table.
	 *
	 * @return tableFacade The updated instance of tableFacade.
	 */
	public function setParenTableName(?string $parenTableName): tableFacade
	{
		$this->parenTableName = $parenTableName;
		return $this;
	}

	/**
	 * Gets the value of the ParenTableIdName property.
	 *
	 * @return string|null The value of the ParenTableIdName property.
	 */
	public function getParenTableIdName(): ?string
	{
		return $this->parenTableIdName;
	}

	/**
	 * Set the parent table ID name.
	 *
	 * @param ?string $parenTableIdName The name of the parent table ID.
	 *
	 * @return tableFacade The current instance of the tableFacade class.
	 */
	public function setParenTableIdName(?string $parenTableIdName): tableFacade
	{
		$this->parenTableIdName = $parenTableIdName;
		return $this;
	}

	/**
	 * Get the foreign key name.
	 *
	 * @return ?string The name of the foreign key, or null if it is not set.
	 */
	public function getForeignKeyName(): ?string
	{
		return $this->foreignKeyName;
	}

	/**
	 * Set the foreign key name.
	 *
	 * @param ?string $foreignKeyName The name of the foreign key.
	 *
	 * @return tableFacade The current instance of the tableFacade class.
	 */
	public function setForeignKeyName(?string $foreignKeyName): tableFacade
	{
		$this->foreignKeyName = $foreignKeyName;
		return $this;
	}

	/**
	 * Get the foreign key value.
	 *
	 * @return ?int The value of the foreign key, or null if not set.
	 */
	public function getForeignKeyValue(): ?int
	{
		return $this->foreignKeyValue;
	}

	/**
	 * Set the foreign key value.
	 *
	 * @param ?int $foreignKeyValue The value of the foreign key.
	 *
	 * @return tableFacade The current instance of the tableFacade class.
	 */
	public function setForeignKeyValue(?int $foreignKeyValue): tableFacade
	{
		$this->foreignKeyValue = $foreignKeyValue;
		return $this;
	}


	/**
	 * Get items from the database table.
	 *
	 * @param ?string $sortColumn    The column to sort the items. Defaults to null.
	 * @param string  $sortDirection The direction ('ASC'|'DESC') to sort the items. Defaults to 'ASC'.
	 * @param int     $offset        The offset for pagination. Defaults to 0.
	 * @param int     $limit         The maximum number of items to return. Defaults to 100.
	 *
	 * @return Selection The selected items from the database table.
	 */
	public function get_items(?string $sortColumn = null, string $sortDirection = 'ASC', int $offset = 0, int $limit = 100): ?Selection
	{
		if (is_null($sortColumn)) {
			$sortColumn = $this->getTableValueName();
		}
		// Check for the correct table name and sort direction. If it is wrong set to default values
		$sortDirection = strtoupper($sortDirection);
		if ($sortColumn == $this->getTableValueName() or $sortColumn == $this->getTableIdName()) {
			if ($sortDirection != 'DESC') {
				$sortDirection = 'ASC';
			}
		} else {
			$sortColumn = $this->getTableValueName();
		}
		$selection = $this->database->table($this->getTableName())->order("{$sortColumn} {$sortDirection}")->page($offset + 1, $limit);
		if ($this->getParentTableName()) {
			$selection = $selection->where($this->getForeignKeyName(), $this->getForeignKeyValue());
		}

		return $selection;

	}

	/**
	 * Retrieves an item from the database table.
	 *
	 * @param int $itemId The ID of the item to retrieve.
	 *
	 * @return ?ActiveRow The retrieved item as an ActiveRow object, or null if not found.
	 */
	public function get_item(int $itemId): ?ActiveRow
	{
		return $this->database->table($this->getTableName())->get($itemId);
	}


	/**
	 * Updates an item in the database table.
	 *
	 * @param array $data The data used for updating the item. It should contain 'id' and 'name' keys.
	 *
	 * @return bool True if the item was successfully updated, False otherwise.
	 */
	public function update(array $data)
	{
		if (is_numeric($data['id']) and $data['id'] > 0) {
			return $this->database->table($this->getTableName())->where($this->getTableIdName(), $data['id'])->update([$this->getTableValueName() => $data['name'],]);
		} else {
			$this->error('invalid item id');
		}
	}

	/**
	 * Deletes an item from the database table.
	 *
	 * @param int $itemId The ID of the item to delete.
	 *
	 * @return void
	 */
	public function delete(int $itemId): void
	{
		if (is_numeric($itemId) and $itemId > 0) {
			$this->database->table($this->getTableName())->where($this->tableIdName, $itemId)->delete();
		} else {
			$this->error('invalid item id');
		}
	}


	/**
	 * Adds a new item to the database table.
	 * If ParentTableName is specified then insert is performed including getForeignKeyName otherwise without
	 *
	 * @param array $data The data to insert into the table. The 'name' key must be provided.
	 *
	 * @return ActiveRow The newly inserted item as an ActiveRow object.
	 *
	 */
	public function add($data): ?ActiveRow
	{

		if ($this->getParentTableName()) {
			$parentTable = $this->database->table($this->getParentTableName())->get($this->getForeignKeyValue());
			if ($parentTable) {
				return $this->database->table($this->getTableName())->insert([$this->getTableValueName() => $data['name'], $this->getForeignKeyName() => $this->getForeignKeyValue()]);
			} else {
				$this->error('invalid foreign key');
			}
		} else {
			return $this->database->table($this->getTableName())->insert([$this->getTableValueName() => $data['name']]);
		}
	}

}
