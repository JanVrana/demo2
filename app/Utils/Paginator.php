<?php

namespace App\Utils;

/**
 * Extension of nette tridy pagination to display direct links to pages
 */
class Paginator extends \Nette\Utils\Paginator
{
	/** @var int number of direct links to the page */
	protected int $directLinksCount = 3;


	/**
	 * get number of direct links to the page
	 * @return int
	 */
	public function getDirectLinksCount(): int
	{
		return $this->directLinksCount;
	}

	/**
	 * set number of direct links to the page
	 *
	 * @param int $directLinksCount
	 *
	 * @return Paginator
	 */
	public function setDirectLinksCount(int $directLinksCount): Paginator
	{
		$this->directLinksCount = $directLinksCount;
		return $this;
	}

	/**
	 * returns the number of the page from which direct links to the page should be displayed
	 * @return int
	 */
	public function getDirectLinksFrom(): int
	{
		if ($this->getDirectLinksCount() >= $this->getPageCount()) {
			$from = $this->getFirstPage();
		} else {
			$from = (int)($this->getPage() - floor($this->getDirectLinksCount() / 2));
			if ($from < $this->getFirstPage()) {
				$from = $this->getFirstPage();
			}
		}
		return $from;
	}

	/**
	 * returns the page number to display direct links to the page
	 * @return int
	 */
	public function getDirectLinksTo(): int
	{
		if ($this->getDirectLinksCount() >= $this->getPageCount()) {
			$to = $this->getPageCount();
		} else {
			$to = $this->getDirectLinksFrom() + $this->getDirectLinksCount();
			if ($to > $this->getLastPage()) {
				$to = $this->getLastPage() + 1;
			}
		}
		return $to;
	}

}
