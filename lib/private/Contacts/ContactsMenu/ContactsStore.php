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

use Exception;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\Contacts\IManager;

class ContactsStore {

	/** @var IManager */
	private $manager;

	/**
	 * @param IManager $manager
	 */
	public function __construct(IManager $manager) {
		$this->manager = $manager;
	}

	/**
	 * @param string|null $filter
	 * @return IEntry[]
	 */
	public function getContacts($filter) {
		$allContacts = $this->manager->search($filter ?: '', [
			'FN',
		]);

		return array_map(function(array $contact) {
			return $this->contactArrayToEntry($contact);
		}, $allContacts);
	}

	/**
	 * @param array $contact
	 * @return Entry
	 */
	private function contactArrayToEntry(array $contact) {
		$entry = new Entry();

		if (isset($contact['id'])) {
			$entry->setId($contact['id']);
		}

		if (isset($contact['FN'])) {
			$entry->setFullName($contact['FN']);
		}

		if (isset($contact['EMAIL'])) {
			foreach ($contact['EMAIL'] as $email) {
				$entry->addEMailAddress($email);
			}
		}

		return $entry;
	}

}
