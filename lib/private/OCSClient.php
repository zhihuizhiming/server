<?php
/**
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Brice Maron <brice@bmaron.net>
 * @author Felix Moeller <mail@felixmoeller.de>
 * @author Frank Karlitschek <frank@karlitschek.de>
 * @author Jarrett <JetUni@users.noreply.github.com>
 * @author Joas Schilling <nickvergessen@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 * @author Kamil Domanski <kdomanski@kdemail.net>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 * @author Robin McCorkell <robin@mccorkell.me.uk>
 * @author Sam Tuke <mail@samtuke.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC;

use OC\Files\Node\File;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\ILogger;

/**
 * Class OCSClient is a class for communication with the ownCloud appstore
 *
 * @package OC
 */
class OCSClient {
	/** @var IClientService */
	private $httpClientService;
	/** @var IConfig */
	private $config;
	/** @var ILogger */
	private $logger;
	/** @var IRootFolder */
	private $rootFolder;
	/** @var ITimeFactory */
	private $timeFactory;

	/**
	 * @param IClientService $httpClientService
	 * @param IConfig $config
	 * @param ILogger $logger
	 * @param IRootFolder $rootFolder
	 * @param ITimeFactory $timeFactory
	 */
	public function __construct(IClientService $httpClientService,
								IConfig $config,
								ILogger $logger,
								IRootFolder $rootFolder,
								ITimeFactory $timeFactory) {
		$this->httpClientService = $httpClientService;
		$this->config = $config;
		$this->logger = $logger;
		$this->rootFolder = $rootFolder;
		$this->timeFactory = $timeFactory;
	}

	/**
	 * Returns whether the AppStore is enabled (i.e. because the AppStore is disabled for EE)
	 *
	 * @return bool
	 */
	public function isAppStoreEnabled() {
		// For a regular edition default to true, all others default to false
		$default = false;
		if (\OC_Util::getEditionString() === '') {
			$default = true;
		}

		return $this->config->getSystemValue('appstoreenabled', $default) === true;
	}

	/**
	 * Get the url of the OCS AppStore server.
	 *
	 * @return string of the AppStore server
	 */
	private function getAppStoreUrl() {
		return $this->config->getSystemValue('appstoreurl', 'https://apps.weasel.rocks');
	}

	/**
	 * Get all the categories from the OCS server
	 *
	 * @return array|null an array of category ids or null
	 * @note returns NULL if config value appstoreenabled is set to false
	 * This function returns a list of all the application categories on the OCS server
	 */
	public function getCategories() {
		if (!$this->isAppStoreEnabled()) {
			return null;
		}

		$categoryCacheFile = 'appstore_categories_cache.json';
		/** @var \OCP\Files\IRootFolder $rootFolder */
		$rootFolder = $this->rootFolder->get('/');
		try {
			/** @var \OCP\Files\File $categoryCache */
			$categoryCache = $rootFolder->get($categoryCacheFile);
			$timeStamp = (int)$this->config->getAppValue('core', $categoryCacheFile, 0);
			if($timeStamp > 0 && $this->timeFactory->getTime() > $timeStamp + 300) {
				return json_decode($categoryCache->getContent(), true);
			}
		} catch (\OCP\Files\NotFoundException $e) {
			$categoryCache = $rootFolder->newFile($categoryCacheFile);
		}

		$client = $this->httpClientService->newClient();
		try {
			$response = $client->get(
				$this->getAppStoreUrl() . '/api/v1/categories.json',
				[
					'timeout' => 20,
				]
			);
		} catch(\Exception $e) {
			$this->logger->error(
				sprintf('Could not get categories: %s', $e->getMessage()),
				[
					'app' => 'core',
				]
			);
			return null;
		}

		$categories = json_decode($response->getBody(), true);
		if(!is_array($categories)) {
			return null;
		}

		$cats = [];

		foreach ($categories as $category) {
			$id = (string)$category['id'];
			$name = (string)$category['translations']['en']['name'];
			$cats[$id] = $name;
		}

		$categoryCache->putContent(json_encode($categories));
		$this->config->setAppValue('core', $categoryCacheFile, $this->timeFactory->getTime());

		return $cats;
	}

	/**
	 * Gets the applications from the appstore
	 *
	 * @return array
	 */
	private function getApps() {
		$client = $this->httpClientService->newClient();

		try {
			$response = $client->get(
				$this->getAppStoreUrl() . '/api/v1/platform/9.0.0/apps.json',
				[
					'timeout' => 20,
				]
			);
		} catch(\Exception $e) {
			$this->logger->error(
				sprintf('Could not get apps: %s', $e->getMessage()),
				[
					'app' => 'core',
				]
			);
			return [];
		}

		$apps = json_decode($response->getBody(), true);
		if(!is_array($apps)) {
			return [];
		}

		$sortedApps = [];
		foreach($apps as $appToRead) {
			$app = [];
			$app['id'] = (string)$appToRead['id'];
			$app['name'] = (string)$appToRead['translations']['en']['name'];
			$app['version'] = (string)$appToRead['releases'][0]['version'];
			$app['checksum'] = (string)$appToRead['releases'][0]['checksum'];
			$app['download'] = (string)$appToRead['releases'][0]['download'];
			$app['preview'] = (string)$appToRead['screenshots'][0]['url'];
			$app['description'] = (string)$appToRead['translations']['en']['description'];
			$app['featured'] = (bool)$appToRead['featured'];
			$app['documentation']['user'] = (string)$appToRead['userDocs'];
			$app['documentation']['admin'] = (string)$appToRead['adminDocs'];
			$app['documentation']['developer'] = (string)$appToRead['developerDocs'];
			$app['website'] = (string)$appToRead['website'];
			$app['bugs'] = (string)$appToRead['issueTracker'];
			$app['detailpage'] = $this->getAppStoreUrl() . '/app/' . $app['id'];

			foreach($appToRead['categories'] as $key => $category) {
				$sortedApps[$category][] = $app;
			}
		}

		return $sortedApps;
	}

	/**
	 * Get all the applications from the OCS server
	 * @param string $category
	 * @return array An array of application data
	 */
	public function getApplications($category) {
		if (!$this->isAppStoreEnabled()) {
			return [];
		}

		return $this->getApps()[$category];
	}


	/**
	 * Get an the applications from the OCS server
	 *
	 * @param string $id
	 * @return array|null an array of application data or null
	 *
	 * This function returns an applications from the OCS server
	 */
	public function getApplication($id) {
		if (!$this->isAppStoreEnabled()) {
			return null;
		}

		$appsInCategories = $this->getApps();
		$requestedApp = null;
		foreach($appsInCategories as $category) {
			foreach($category as $app) {
				if ($app['id'] === $id) {
					$requestedApp = $app;
					break;
				}
			}
		}

		return $requestedApp;
	}

	/**
	 * Get the download url for an application from the OCS server
	 * @param string $id
	 * @return string Download link
	 */
	public function getApplicationDownload($id) {
		if (!$this->isAppStoreEnabled()) {
			return null;
		}

		$app = $this->getApplication($id);
		if(isset($app['download'])) {
			return $app['download'];
		}

		return '';
	}

}
