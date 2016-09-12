<?php
/**
 * @copyright Copyright (c) 2016 Morris Jobke <hey@morrisjobke.de>
 *
 * @author Morris Jobke <hey@morrisjobke.de>
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

namespace Test\Repair;

use OC\Repair\RemoveGetETagEntries;
use OC\Repair\UpdateFinishLanguageCode;
use OCP\Migration\IOutput;
use Test\TestCase;

/**
 * Class UpdateFinishLanguageCodeTest
 *
 * @group DB
 *
 * @package Test\Repair
 */
class UpdateFinishLanguageCodeTest extends TestCase {
	/** @var \OCP\IDBConnection */
	protected $connection;

	protected function setUp() {
		parent::setUp();

		$this->connection = \OC::$server->getDatabaseConnection();
	}

	public function testRun() {

		$users = [
			['userid' => 'user1', 'configvalue' => 'fi_FI'],
			['userid' => 'user2', 'configvalue' => 'de'],
			['userid' => 'user3', 'configvalue' => 'fi'],
			['userid' => 'user4', 'configvalue' => 'ja'],
		];

		// insert test data
		$qb = $this->connection->getQueryBuilder();
		$sql = $qb->insert('preferences')
				->values([
					'userid' => '?',
					'appid' => '?',
					'configkey' => '?',
					'configvalue' => '?',
				])
				->getSQL();
		foreach ($users as $user) {
			$this->connection->executeUpdate($sql, [$user['userid'], 'core', 'lang', $user['configvalue']]);
		}

		// check if test data is written to DB
		$qb = $this->connection->getQueryBuilder();
		$result = $qb->select(['userid', 'configvalue'])
			->from('preferences')
			->where($qb->expr()->eq('appid', $qb->createNamedParameter('core')))
			->andWhere($qb->expr()->eq('configkey', $qb->createNamedParameter('lang')))
			->execute();

		$rows = $result->fetchAll();
		$result->closeCursor();

		$this->assertSame($users, $rows, 'Asserts that the entries are the ones from the test data set');

		/** @var IOutput | \PHPUnit_Framework_MockObject_MockObject $outputMock */
		$outputMock = $this->getMockBuilder('\OCP\Migration\IOutput')
			->disableOriginalConstructor()
			->getMock();

		$outputMock->expects($this->once())
			->method('info')
			->with('Changed 1 setting(s) from "fi_FI" to "fi" in properties table.');

		// run repair step
		$repair = new UpdateFinishLanguageCode($this->connection);
		$repair->run($outputMock);

		// check if test data is correctly modified in DB
		$qb = $this->connection->getQueryBuilder();
		$result = $qb->select(['userid', 'configvalue'])
			->from('preferences')
			->where($qb->expr()->eq('appid', $qb->createNamedParameter('core')))
			->andWhere($qb->expr()->eq('configkey', $qb->createNamedParameter('lang')))
			->execute();

		$rows = $result->fetchAll();
		$result->closeCursor();

		// value has changed for one user
		$users[0]['configvalue'] = 'fi';
		$this->assertSame($users, $rows, 'Asserts that the entries are updated correctly.');

		// remove test data
		foreach ($users as $user) {
			$qb = $this->connection->getQueryBuilder();
			$qb->delete('preferences')
				->where($qb->expr()->eq('userid', $qb->createNamedParameter($user['userid'])))
				->andWhere($qb->expr()->eq('appid', $qb->createNamedParameter('core')))
				->andWhere($qb->expr()->eq('configkey', $qb->createNamedParameter('lang')))
				->andWhere($qb->expr()->eq('configvalue', $qb->createNamedParameter($user['configvalue'])))
				->execute();
		}
	}

}
