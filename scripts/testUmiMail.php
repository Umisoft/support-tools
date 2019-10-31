<?php
	/** Скрипт отправляет письма средствами UMI.CMS */
	require_once('standalone.php');

	if(isset($_GET['email'])) {

		$fromMail = "info@marketprezent.ru";
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
		$emails = $_GET['email'];

		$mail = new umiMail();
		$mail->addRecipient($emails); // Почта и имя получателя
		$mail->setFrom($fromMail, $fromName); // Почта и имя отправителя
		$mail->setSubject($subject); // Заголовок
		$mail->setContent($content); // Контент письма
		$mail->commit();
		if($mail->send()) 
			echo "<p style=\"color:green\">sendmail return: ok</p>";
		else
			echo "<p style=\"color:red\">sendmail return: failed</p>";
		exit();
	}
	?>
<html>
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<title>send email</title>
	<script type="text/javascript">
	function c() {
			var email = document.getElementById('email');
			email.value = '';
	}
	</script>
	</head>
	<body>
		<form method="get">
		<input type="text" style="width: 180px" name="email" placeholder="Enter your e-mail" onClick="c()" id="email">
		<input type="submit" value="Test">
		</form>
	</body>
</html>