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

namespace OC\Contacts\ContactsMenu\Actions;

use OCP\Contacts\ContactsMenu\ILinkAction;

class EMailAction implements ILinkAction {

	/** @var string */
	private $icon;

	/** @var string */
	private $name;

	/** @var string */
	private $href;

	/**
	 * @param string $icon absolute URI to an icon
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param string $href
	 */
	public function setHref($href) {
		$this->href = $href;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize() {
		return [
			'title' => $this->name,
			'icon' => $this->icon,
			'hyperlink' => $this->href,
		];
	}

}
