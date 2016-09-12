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

namespace OC\Repair;

use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;

class UpdateFinishLanguageCode implements IRepairStep {
	/** @var IDBConnection */
	private $connection;

	/**
	 * @param IDBConnection $db
	 */
	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName() {
		return 'Repair language code for fi_FI to fi';
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(IOutput $output) {
		$qb = $this->connection->getQueryBuilder();

		$affectedRows = $qb->update('preferences')
			->set('configvalue', $qb->createNamedParameter('fi'))
			->where($qb->expr()->eq('appid', $qb->createNamedParameter('core')))
			->andWhere($qb->expr()->eq('configkey', $qb->createNamedParameter('lang')))
			->andWhere($qb->expr()->eq('configvalue', $qb->createNamedParameter('fi_FI')))
			->execute();

		$output->info('Changed ' . $affectedRows . ' setting(s) from "fi_FI" to "fi" in properties table.');
	}
}
