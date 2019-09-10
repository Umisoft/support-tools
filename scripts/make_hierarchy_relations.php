<?php

	/**
	 * Скрипт заново создает иерархические связи для всех страниц на сайте.
	 * Его можно использовать, когда на сайте обнаружилась циклическая иерархия страниц.
	 */
	header('Content-type: text/html; charset=utf-8');
	include __DIR__ . '/../standalone.php';

	main();

	function main() {
		checkUserGrants();
		recreateHierarchyRelationsTable();

		$sql = 'SELECT id FROM cms3_hierarchy';
		$result = queryResult($sql);

		foreach ($result as $row) {
			createRelationsFor((int) $row['id']);
		}
	}

	/**
	 * Выполняет запрос к БД с обработкой ошибок
	 * @param string $sql строка запроса
	 * @return IQueryResult результат выполнения запроса
	 */
	function queryResult($sql) {
		$connection = ConnectionPool::getInstance()->getConnection();
		$result = $connection->queryResult($sql);

		if ($connection->errorOccurred()) {
			throw new coreException($connection->errorDescription($sql));
		}

		return $result;
	}

	/** Проверяет, что у пользователя MySQL есть права на создание таблиц */
	function checkUserGrants() {
		$sql = 'SHOW GRANTS';
		$result = queryResult($sql);

		if ($result->length() === 0) {
			throw new coreException('Ошибка: не удалось получить информацию о правах пользователя MySQL');
		}

		$row = $result->fetch();
		$grants = $row[0];

		if (!hasSufficientGrants($grants)) {
			throw new coreException('Ошибка: у пользователя MySQL нет прав на создание таблиц');
		}
	}

	/**
	 * Проверяет, что у пользователя MySQL есть права на создание таблиц
	 * @param string $allGrants права пользователя на работу с БД
	 * @return bool
	 */
	function hasSufficientGrants($allGrants) {
		$sufficientGrants = ['GRANT ALL', 'CREATE'];

		foreach ($sufficientGrants as $grant) {
			if (contains($allGrants, $grant)) {
				return true;
			}
		}

		return false;
	}

	/** Заново создает таблицу cms3_hierarchy_relations */
	function recreateHierarchyRelationsTable() {
		$dropSql = 'DROP TABLE IF EXISTS cms3_hierarchy_relations';
		queryResult($dropSql);

		$createSql = <<<SQL
CREATE TABLE `cms3_hierarchy_relations` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rel_id` INT(10) UNSIGNED DEFAULT NULL,
  `child_id` INT(10) UNSIGNED DEFAULT NULL,
  `level` INT(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rel_id` (`rel_id`),
  KEY `child_id` (`child_id`),
  KEY `level` (`level`),
  CONSTRAINT `Hierarchy relation by child_id` FOREIGN KEY (`child_id`) REFERENCES `cms3_hierarchy` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Hierarchy relation by rel_id` FOREIGN KEY (`rel_id`) REFERENCES `cms3_hierarchy` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;
		queryResult($createSql);
	}

	/**
	 * Заново создает связи в таблице cms3_hierarchy_relations для страницы
	 * @param int $pageId идентификатор страницы
	 */
	function createRelationsFor($pageId) {
		$ancestors = getAllAncestors($pageId);
		$level = umiCount($ancestors);

		$sql = <<<SQL
INSERT INTO cms3_hierarchy_relations (rel_id, child_id, level) VALUES (NULL, '{$pageId}', '{$level}')
SQL;
		queryResult($sql);

		foreach ($ancestors as $ancestorId) {
			$ancestorSql = <<<SQL
INSERT INTO cms3_hierarchy_relations (rel_id, child_id, level) VALUES ('{$ancestorId}', '{$pageId}', '{$level}')
SQL;
			queryResult($ancestorSql);
		}
	}

	/**
	 * Возвращает идентификаторы всех предков страницы
	 * @param int $childId идентификатор страницы
	 * @return int[]|bool
	 */
	function getAllAncestors($childId) {
		$ancestors = [];
		$currentId = $childId;

		while (true) {
			$sql = "SELECT rel FROM cms3_hierarchy WHERE id = '{$currentId}'";
			$result = queryResult($sql);

			if ($result->length() === 0) {
				throw new coreException("У страницы {$currentId} не указан родитель в таблице cms3_hierarchy");
			}

			$row = $result->fetch();
			$currentId = (int) $row['rel'];
			$isLastRow = !$currentId;
			$isCyclic = in_array($currentId, $ancestors);

			if ($isLastRow || $isCyclic) {
				break;
			}

			$ancestors[] = $currentId;
		}

		return array_reverse($ancestors);
	}
