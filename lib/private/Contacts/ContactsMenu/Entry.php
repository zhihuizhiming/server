<?php

/**
 * @copyright 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Contacts\ContactsMenu;

use OCP\Contacts\ContactsMenu\IAction;
use OCP\Contacts\ContactsMenu\IEntry;

class Entry implements IEntry {

	/** @var string|int|null */
	private $id = null;

	/** @var string */
	private $fullName = '';

	/** @var string[] */
	private $emailAddresses = [];

	/** @var IAction[] */
	private $actions = [];

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param string $displayName
	 */
	public function setFullName($displayName) {
		$this->fullName = $displayName;
	}

	/**
	 * @return string
	 */
	public function getFullName() {
		return $this->fullName;
	}

	/**
	 * @param string $address
	 */
	public function addEMailAddress($address) {
		$this->emailAddresses[] = $address;
	}

	/**
	 * @return string
	 */
	public function getEMailAddresses() {
		return $this->emailAddresses;
	}

	/**
	 * @param IAction $action
	 */
	public function addAction(IAction $action) {
		$this->actions[] = $action;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		$topAction = (count($this->actions) > 0) ? reset($this->actions)->jsonSerialize() : null;
		$otherActions = array_map(function(IAction $action) {
			return $action->jsonSerialize();
		}, array_slice($this->actions, 1));

		return [
			'id' => $this->id,
			'fullName' => $this->fullName,
			'topAction' => $topAction,
			'actions' => $otherActions,
			'lastMessage' => '',
		];
	}

}
