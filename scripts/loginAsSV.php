<?php
	/** Скрипт авторизации в системе в качестве супервайзера, запускается из корня сайта */
	unlink(__FILE__);
	include_once './standalone.php';

	/** @const int Сборка системы, в которой был добавлен класс Auth */
	const AUTH_SERVICE_FIRST_BUILD_NUMBER = 81229;
	
	$userId = (int) getRequest('id');

	/** @noinspection PhpDeprecationInspection (обратная совместимость) */
	$regedit = regedit::getInstance();
	/** @noinspection PhpUndefinedMethodInspection (обратная совместимость) */
	$build = $regedit->getVal('//modules/autoupdate/system_build');

	if ($build >= AUTH_SERVICE_FIRST_BUILD_NUMBER) {
		if ($userId) {
			UmiCms\Service::Auth()->loginUsingId($userId);
		} else {
			UmiCms\Service::Auth()->loginAsSv();
		}
		/** @noinspection PhpDeprecationInspection (обратная совместимость) */
		outputBuffer::current('HTTPOutputBuffer')->redirect('/admin');
	}

	session_start();
	$objects = umiObjectsCollection::getInstance();

	if (!$userId) {
		$userId = $objects->getObjectIdByGUID('system-supervisor');
	}
	$userObject = $objects->getObject($userId);
	$login = $userObject->getValue('login');
	$password = $userObject->getValue('password');

	$_SESSION['user_id'] = $userId;
	$_SESSION['cms_login'] = $login;
	$_SESSION['cms_pass'] = $password;
	$_SESSION['csrf_token'] = md5(mt_rand() . microtime());
	$_SESSION['user_is_sv'] = true;

	header('Location: /admin');
