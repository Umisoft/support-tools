<?php
	/*
	 * Скрипт позволяет отобразить результат преобразования из фомата CSV в umiDump 2.0 по шаблонам используемым в системе
	 * GET параметры:
	 * f - файл импорта (обязательный)
	 * size - количество элементов для импорта
	 * text - если указан, то резульат будет выведен как текст, иначе как xml файл
	 * import - выполнить импорт данных в систему
	 */
	use UmiCms\Service;

	require_once('standalone.php');

	$file_path = getRequest('f');

	if (!is_file($file_path)) {
		die("failure\nFile $file_path does not exist.");
	}

	$session = Service::Session();
	$import_offset = (int) $session->get('1c_import_offset');
	$umiConfig = mainConfiguration::getInstance();

	$blockSize = getRequest('size');

	if (!$blockSize) {
		$blockSize = (int) $umiConfig->get('modules', 'exchange.splitter.limit');

		if ($blockSize < 0) {
			$blockSize = 25;
		}
	}

	/** @var iUmiImportSplitter|umiImportSplitter $splitter */
	// Сплиттер выполняет преобразование формата файла из CSV в umiDump 2.0 по шаблонам
	$splitter = umiImportSplitter::get('CSV');
	$splitter->setEncoding('utf-8');
	// $splitter->setEncoding('windows-1251');
	$splitter->load($file_path);
	$doc = $splitter->getDocument();
	$xml = $splitter->translate($doc);
	// В $xml содержится результат преобразования

	$asText = getRequest('text');
	if ($asText) {
		echo "<plaintext>";
	} else {
		header('Content-Type: text/xml; charset=utf-8');
	}
	print_r($xml);

	// Далее идет код импорта umiDump 2.0 в систему
	// Класс импорта описан в файле /classes/system/subsystems/import/xmlImporter.php
	$makeImport = getRequest('import');
	if ($makeImport) {
		$oldIgnoreSiteMap = umiHierarchy::$ignoreSiteMap;
		umiHierarchy::$ignoreSiteMap = true;

		$importer = new xmlImporter();
		$importer->loadXmlString($xml);
		$importer->setIgnoreParentGroups($splitter->ignoreParentGroups);
		$importer->setAutoGuideCreation($splitter->autoGuideCreation);
		$importer->setRenameFiles($splitter->getRenameFiles());
		$domainId = Service::DomainDetector()->detectId();
		$importer->setForcedDomainId($domainId);
		$importer->execute();

		umiHierarchy::$ignoreSiteMap = $oldIgnoreSiteMap;
		$session->set('1c_import_offset', $splitter->getOffset());
		$resultMessage = "progress\nImported elements: " . $splitter->getOffset();

		if ($splitter->getIsComplete()) {
			$importFinished = new umiEventPoint('exchangeOnAutoFinish');
			$importFinished->setMode('after');
			$importFinished->addRef('splitter', $splitter);
			$importFinished->call();
			$session->set('1c_import_offset', 0);
			$resultMessage = "success\nComplete. Imported elements: " . $splitter->getOffset();
		}
		print_r($resultMessage);
	}
