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

namespace Tests\Contacts\ContactsMenu;

use OC\Contacts\ContactsMenu\Entry;
use OCP\Contacts\ContactsMenu\IAction;
use Test\TestCase;

class EntryTest extends TestCase {

	/** @var Entry */
	private $entry;

	protected function setUp() {
		parent::setUp();

		$this->entry = new Entry();
	}

	public function testSetId() {
		$this->entry->setId(123);
	}

	public function testSetGetFullName() {
		$fn = 'Danette Chaille';
		$this->assertEquals('', $this->entry->getFullName());
		$this->entry->setFullName($fn);
		$this->assertEquals($fn, $this->entry->getFullName());
	}

	public function testAddGetEMailAddresses() {
		$this->assertEmpty($this->entry->getEMailAddresses());
		$this->entry->addEMailAddress('user@example.com');
		$this->assertEquals(['user@example.com'], $this->entry->getEMailAddresses());
	}

	public function testAddAndSortAction() {
		// Three actions, two with equal priority
		$action1 = $this->createMock(IAction::class);
		$action2 = $this->createMock(IAction::class);
		$action3 = $this->createMock(IAction::class);
		$action1->expects($this->any())
			->method('getPriority')
			->willReturn(10);
		$action1->expects($this->any())
			->method('getName')
			->willReturn('Bravo');
		$action1->expects($this->once())
			->method('jsonSerialize')
			->willReturn(['id' => 1]);

		$action2->expects($this->any())
			->method('getPriority')
			->willReturn(0);
		$action2->expects($this->any())
			->method('getName')
			->willReturn('Batman');
		$action2->expects($this->once())
			->method('jsonSerialize')
			->willReturn(['id' => 2]);

		$action3->expects($this->any())
			->method('getPriority')
			->willReturn(10);
		$action3->expects($this->any())
			->method('getName')
			->willReturn('Alfa');
		$action3->expects($this->once())
			->method('jsonSerialize')
			->willReturn(['id' => 3]);

		$expectedJson = [
			'id' => null,
			'fullName' => '',
			'topAction' => [
				'id' => 3,
			],
			'actions' => [
				[
					'id' => 1,
				],
				[
					'id' => 2
				],
			],
			'lastMessage' => '',
		];
		$this->entry->addAction($action1);
		$action1->x = 1;
		$this->entry->addAction($action2);
		$action2->x = 2;
		$this->entry->addAction($action3);
		$action3->x = 3;
		$json = $this->entry->jsonSerialize();

		$this->assertEquals($expectedJson, $json);
	}

	public function testSetGetProperties() {
		$props = [
			'prop1' => 123,
			'prop2' => 'string',
		];

		$this->entry->setProperties($props);

		$this->assertNull($this->entry->getProperty('doesntexist'));
		$this->assertEquals(123, $this->entry->getProperty('prop1'));
		$this->assertEquals('string', $this->entry->getProperty('prop2'));
	}

	public function testJsonSerialize() {
		$expectedJson = [
			'id' => 123,
			'fullName' => 'Guadalupe Frisbey',
			'topAction' => null,
			'actions' => [],
			'lastMessage' => '',
		];

		$this->entry->setId(123);
		$this->entry->setFullName('Guadalupe Frisbey');
		$json = $this->entry->jsonSerialize();

		$this->assertEquals($expectedJson, $json);
	}

}
