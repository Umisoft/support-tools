<?php

	/**
	 * Скрипт заново создает иерархические связи для всех объектный типов.
	 */
	header('Content-type: text/html; charset=utf-8');
	include __DIR__ . '/../standalone.php';

	use UmiCms\Service;

	run();

	/** Запускает пересоздание связей */
	function run() {
		recreateHierarchyRelationsTable();

		$manifest = Service::ManifestFactory()
			->create('Migrate', [], iAtomicOperationCallbackFactory::COMMON);

		while (!$manifest->isReady()) {
			$manifest->execute();
		}

		foreach ($manifest->getLog() as $message) {
			echo $message, PHP_EOL;
		}
	}

	/** Заново создает таблицу cms3_object_type_tree */
	function recreateHierarchyRelationsTable() {
		$connection = ConnectionPool::getInstance()
			->getConnection();

		$dropSql = 'DROP TABLE IF EXISTS `cms3_object_type_tree`';
		$connection->query($dropSql);

		$createSql = <<<SQL
CREATE TABLE `cms3_object_type_tree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned,
  `child_id` int(10) unsigned,
  `level` int(10) unsigned,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique parent-child object type relation` (`parent_id`,`child_id`),
  CONSTRAINT `Object type id from parent_id` FOREIGN KEY (`parent_id`) REFERENCES `cms3_object_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Object type id from child_id` FOREIGN KEY (`child_id`) REFERENCES `cms3_object_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
		$connection->query($createSql);
	}
