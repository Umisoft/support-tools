<?php
	/** Скрипт отправляет письма всем подписчикам указанной в $dispatchId рассылки */
	use UmiCms\Service;

	require_once('standalone.php');

	$test = true;
	if (isset($_GET['notest'])) {
		$test = false;
	}
	// Сюда надо указать id рассылки
	$dispatchId = 225780;

	$mailer = new umiMail();

	$mailSettings = Service::get('MailSettings');
	$mailer->setFrom($mailSettings->getSenderEmail(), $mailSettings->getSenderName());

	$sel = new selector('objects');
	$sel->types('hierarchy-type')->name('dispatches', 'subscriber');
	$sel->where('subscriber_dispatches')->equals($dispatchId);
	$sel->group('name');

	foreach ($sel->result() as $recipient) {
		$nextMailer = clone $mailer;

		$subscriber = new umiSubscriber($recipient->getId());
		$recipientName = $subscriber->getFullName();
		$email = $subscriber->getEmail();

		if ($test) {
			echo "<p>Будет отправленно: $email $recipientName</p>\n";
			continue;
		}

		$fromMail = "my@zingery.ru";
		$fromName = "Интернет-Магазин";
		$subject = "Все отлично!";
		$content = <<<MSG
<html>
<head>
 <title>Тестирование отправки письма с хоста: </title>
</head>
<body>
<p>Если Вы читаете это сообщение на русском и в HTML!</p>
<table>
 <tr>
<th>1</th><th>2</th><th>3</th><th>4</th>
 </tr>
 <tr>
<td>1</td><td>2</td><td>3</td><td>4</td>
 </tr>
 <tr>
<td>2</td><td>4</td><td>6</td><td>8</td>
 </tr>
</table>
<p>то это означает, что функция работает правильно.</p>
</body>
</html>
MSG;

		$nextMailer->setSubject($subject);
		$nextMailer->setContent($content);
		$nextMailer->addRecipient($email, $recipientName);
		$nextMailer->commit();
		$result = $nextMailer->send();
		if ($result) {
			echo "<p style=\"color:green\">Отправлено: $email $recipientName</p>\n";
		} else {
			echo "<p style=\"color:red\">Не отправлено: $email $recipientName</p>\n";
		}
	}