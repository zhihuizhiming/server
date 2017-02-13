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

namespace OC\Contacts\ContactsMenu\Providers;

use OC\Contacts\ContactsMenu\Actions\EMailAction;
use OCP\Contacts\ContactsMenu\IEntry;
use OCP\Contacts\ContactsMenu\IProvider;

class EMailProvider implements IProvider {

	/**
	 * @param IEntry $entry
	 */
	public function process(IEntry $entry) {
		foreach ($entry->getEMailAddresses() as $address) {
			$action = new EMailAction();
			$action->setName('Mail'); // TODO: l10n
			$action->setIcon('icon-mail'); // TODO: absolute path
			$action->setHref('mailto:' . urlencode($address)); // TODO: meaningful URL
			$entry->addAction($action);
		}
	}

}
