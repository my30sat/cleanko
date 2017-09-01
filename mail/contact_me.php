<?
/* Скрипт умеет слать на почту данные из веб-формы и вставлять в письмо приложенное изображение.
	Картинки складываются в папку mailpictures, так что не забудьте создать ее.
*/
// В какой кодировке приходят данные.
$incoming_encoding = 'Windows-1251';
// Почта куда слать сообщения.
$address = "dgrgg@ukr.net";
// Максимальная разрешенная ширина загружаемого изображения
$max_image_width = 3000;
header("Content-Type: text/html; charset=utf-8");
// Получаем переменные
$error = "";
if (!isset($_POST['telnumber']) || $_POST['telnumber'] == "") {$error .= "Не указан номер телефона.";}
	else {$tel = $_POST['telnumber'];}
	if (!isset($_POST['mail']) || $_POST['mail'] == "") {$error .= "Не указан Email.";}
else {$count = $_POST['mail'];}
if (!isset($_POST['clientname']) || $_POST['clientname'] == "") {$error .= "Не указано имя.";}
	else {$navn = $_POST['clientname'];}
if (!isset($_POST['comment']) || $_POST['comment'] == "") {$error .= "Не указан комментарий.";}
	else {$comment = $_POST['count'];}
if ($error != "") {
	echo '<font color="red">'.$error.'</font><a href="/javascript:history.back();">Назад</a>';
	die();
}
// Проверяем переменные на вшивость.
$navn = htmlspecialchars($navn,ENT_QUOTES);
$mail = htmlspecialchars($mail,ENT_QUOTES);
$tel = htmlspecialchars($tel,ENT_QUOTES);
$comment = htmlspecialchars($comment,ENT_QUOTES);
// Переводим в UTF-8
$navn = iconv($incoming_encoding, 'UTF-8', $navn);
$mail = iconv($incoming_encoding, 'UTF-8', $mail);
$tel = iconv($incoming_encoding, 'UTF-8', $tel);
$comment = iconv($incoming_encoding, 'UTF-8', $comment);
// Узнаем айпи отправителя.
$remote_ip = $_SERVER["REMOTE_ADDR"];
if ($_FILES['userfile']['size'] > 0) {
	$imageinfo = getimagesize($_FILES['userfile']['tmp_name']);
	$error = "";
	if($_FILES['userfile']['type'] != "image/jpeg") {
		$error .= "Загружать можно только файлы JPG.<br />";
	} elseif($imageinfo["0"] > $max_image_width) {
		$error .= "Файл, который вы загрузили имеет слишком большое разрешение. Разрешенный максимум - ".$max_image_width." пикселей по ширине.";
	} elseif($imageinfo['mime'] != 'image/jpeg') {
		$error .= "Файл, который вы загрузили поврежден или не является изображением.<br />";
	} elseif (!preg_match("/\.(jpg|jpeg|JPG|JPEG)$/i", $_FILES['userfile']['name'])){
		$error .= "Неверный тип файла.<br />";
	}
	
	if ($error != "") {
		echo '<font color="red">'.$error.'</font><a href="/javascript:history.back();">Назад</a>';
		die();
	}
	$uploadfile = 'mailpictures/' . basename($_FILES['userfile']['name']);
	
	if (!move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		echo '<p>Ошибка загрузки файла.</p><a href="javascript:history.back();">Назад</a>';
		die();
	}
} else {
	$uploadfile = 'mailpictures/no-photo.png';
}
// Тема письма. Для фильтров в гмайле.
$sub = "[Почта с сайта] $navn, $tel, $mail";
// Собственно, текст. Точнее, HTML.
$mes = '<html>
<h2>Получено новое сообщение с сайта http://my-awesome-site.com/.</h2>
<table border="0" width="100%">
<tr>
	<td width="20%">Имя:</td>
	<td>'.$navn.'</td>
</tr>
	<tr>
		<td>Телефон:</td>
		<td>'.$tel.'</td>
	</tr>
	<tr>
		<td>E-mail:</td>
		<td>'.$mail.'</td>
	</tr>
	<tr>
		<td colspan="2">
			<table width="100%" border="0" style="border: 1px solid #333;">
			<tr>
				<td width="400">Изображение (нажмите для увеличения):</td>
				<td width="*" style="padding: 0 10px;">Сообщение:</td>
			</tr>
			<tr>
				<td style="text-align: left;">
					<a href="http://my-awesome-site.com/'.$uploadfile.'">
						<img width="400" src="http://my-awesome-site.com/'.$uploadfile.'" />
					</a>
				</td>
				<td style="padding: 0 10px; vertical-align: top;">'.$comment.'</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>IP:</td>
		<td><a href="http://ip-whois.net/ip_geo.php?ip='.$remote_ip.'">'.$remote_ip.'</a></td>
	</tr>
</table>';
// Шлем песьмо, чочо.
$send = mail($address,$sub,$mes,"Content-type:text/plain; charset = utf-8\r\nFrom:$address");
if ($send == 'true') {
	echo 'Ваша заявка отправлена!<br><br><a href="javascript:history.back()">Назад</a>';
} else  {
	echo  "Ошибка при отправке заявки!<br><br><a href=\"javascript:history.back()\">Вернуться и попробовать еще раз.</a>";
}
?>
