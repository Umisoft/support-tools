<?php
/**
 * @author ZingerY
 * @name cleanAdvAjax
 * @description Скрипт служит для полуавтоматической очистки базы данных UMI.CMS от мусорных объектов.
 * @version 1.0.2
 * @license Free
 * @copyright 2007-2020 Umisoft
 */
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
	header("Cache-Control: no-cache, must-revalidate");
	header("Cache-Control: post-check=0,pre-check=0", false);
	header("Cache-Control: max-age=0", false);
	header("Pragma: no-cache");

	include './standalone.php';

	$cleaner = new cleanAdv();

	if (!isset($_REQUEST['json'])) {
		header('Content-Type: text/html; charset=utf-8');
?>
<html><head>
<title>Очистка БД</title>
<link rel='shortcut icon' type='image/x-icon' href='data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAGgAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAwAAAAMAAAADAAAAAaAAAADAAAAAwAAAAAAAAADAAAAAwAAAAaAAAAMgAAADIAAAAyAAAAMgAAADIAAAAyAAAAMgAAADIAAAAaAAAAGgAAAAwAAAAAAAAAAAAAAAIAAAAMAAAAGgAAACgAAAAwAAAAMgAaMGQALVfMACxWzAAYMFwAAAAcAAAAFgAAAA4AAAAKAAAABAAAAAAAAAAAAAAABgAAAA4AAAAUAAAAGAAcNlwAK1XMVJS3/zRnmv8AMFrKAB46VAAAAAoAAAAIAAAABAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAFNfEABS3m6OnGf/zhvnf9fn8D/RXir/wA3Y8YANWBGAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAArVUgAK1XMT42z/2isyP9IgKz/UIez/2qqyP9ViLv/AEFuwAA+akQAAAAAAAAAAAAAAAAAAAAAAAAAAAFXhz4BVYW2X6HA/z95o/9CeKf/ZqbF/2Gdwv9elcH/dLTR/2WYy/8BAQGqAQEBPAAAAAAAAAAAAAAAAAArVUgAK1XMM2iY/1CMs/9pq8j/Z6fG/02As/9xsc7/bqnN/2yjzv9tbW3/qpmZ/wEBAaQBTHpCAAAAAAFgkT4BXo+waq7J/2aoxf9Wkrj/S4Cv/12Xv/93udL/Zp3I/3u61f9+fn7/zsDA/3l5ef9ViLv/AU9+pgAAAAABYpOCEm2buCB4osIzhavQWKLA5nS50fpurMz/Zp3I/4PH2v+IiIj/08rK/4ODg/9gpMb/Y6fJ/wFTgqQAAAAAAWOVBAFjlBQBYpMoAWGSQAFhknYQbJqqS5u62nm51fyRkZH/2dTU/42Njf9orM7/dLjU/wFYh7QBVoZAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWKTEAFik2wAAABo3dzc/5SUlP9wtNb/gMTb/wFcjbIAGmPMABNYSAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJAAAAGaIzN3/h8vd/wFgka4AMIDMP3K2/wAndMwAJHBIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABM0w4AWWWnAFklZwBY5Q+ADmLSAA2iMxShcn/AC5+zAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPpJIADyPzAA3ikgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAP//AAD+fwAA/D8AAPgfAADwDwAA4AcAAMADAACAAQAAAAEAAPgDAAD/AwAA/4EAAP+YAAD//QAA//8AAA==' />
<style>
.td{border: 1px solid #CCC;padding: 2px 6px;text-align: center;}
.tdc{padding: 2px 6px;}
.butt{margin: 0px 5px;font-size: 15px;padding: 2px 12px;}
table{border-collapse: collapse;border-spacing: 0;}
.count{cursor:pointer;font-weight: 600;}
.load{background: url('data:image/gif;base64,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') no-repeat center;padding: 0px 16px;}
#txtError{min-height: 21px;width: 60px;margin: 5px 0px;}
</style>
</head><body>
order_type_id <?=$cleaner->order_type_id?>
<hr/>
ord_item_type_id <?=$cleaner->ord_item_type_id?>
<hr/>
cust_type_id <?=$cleaner->cust_type_id?>
<hr/>
cust_field_id <?=$cleaner->cust_field_id?>
<hr/>
ord_items_field_id <?=$cleaner->ord_items_field_id?>
<hr/>
<button class="butt" id="testBut">Старт!!</button>
<button class="butt" id="autoBut" disabled="disabled" onclick="Del(this,true);" data-e="0">Удалить все!!</button>
<button class="butt" id="stopBut" disabled="disabled" onclick="Stop();">Шайтанамане остановись!!!</button>
<hr/>
<div id="main">
</div>
<div id="txtError"></div>
<script>
var info = [
	{name:'заказов без номера',query:'noname',count:0,del:1000,countDel:0,maxCount:0,fullTime:0},
	{name:'заказов без покупателя',query:'nocustomer',count:0,del:1000,countDel:0,maxCount:0,fullTime:0},
	{name:'заказов без товаров',query:'noitem',count:0,del:1000,countDel:0,maxCount:0,fullTime:0},
	{name:'незарегистрированных покупателей, не оформивших ниодного заказа',query:'badcustomers',count:0,del:1000,countDel:0,maxCount:0,fullTime:0},
	{name:'элементов заказа, не содержащихся ни в одном заказе',query:'baditems',count:0,del:1000,countDel:0,maxCount:0,fullTime:0},
	{name:'пустых Object Content',query:'objcontent',count:0,del:10000,countDel:0,maxCount:0,fullTime:0},
	{name:'пустых Imgage Content',query:'objcontentimage',count:0,del:10000,countDel:0,maxCount:0,fullTime:0},
]
var infoTable;
var auto = false;
var consoleDebug = false;
// Отправка запроса
function ajax(opt) {
	var xhr = new XMLHttpRequest;
	xhr.open('POST', '<?=$_SERVER["SCRIPT_NAME"]?>', true);
	xhr.onreadystatechange = function() {
			if (xhr.readyState == 4) {
				opt.func(xhr,opt.step,opt.start);
			}
		};
	xhr.responseType = 'json';
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	if(opt.load) {
		opt.load(opt.step);
	}
	xhr.send('json=1&action=' + opt.action + '&step=' + info[opt.step].query + (opt.count ? '&count=' + opt.count : '') + (opt.start ? '&start=' + opt.start : ''));
}
// Процесс загрузки количества
function loadCount(step) {
	var tr = document.querySelector('#' + info[step].query);
	tr.cells[1].innerHTML = '';
	tr.cells[1].classList.add('load');
}
// Обработка ответа сервера при обновлении количества
function doneUpCount(xhr,step) {
	if (consoleDebug) {
		console.log(xhr);
	}
	var tr = document.querySelector('#' + info[step].query);
	tr.cells[1].classList.remove('load');
	if (xhr.status == 200) {
		var res = xhr.response;
		if (res && typeof res == 'object') {
			info[step].count = res[0];
			tr.cells[1].innerHTML = info[step].count;
		}
	} else {
		if (checkError(step, xhr, txtError)) {
			return;
		}
	}
}
// Обработка ответа сервера при переобновлении количества badcustomers и baditems после удаления noname
function doneUpCountAfterNoname(xhr,step,start) {
	if (consoleDebug) {
		console.log(xhr);
	}
	var tr = document.querySelector('#' + info[step].query);
	tr.cells[1].classList.remove('load');
	if(xhr.status == 200) {
		var res = xhr.response;
		if(res && typeof res == 'object') {
			info[step].count = res[0];
			info[step].maxCount = +res[0];
			tr.cells[1].innerHTML = info[step].count;
		}
		if(step < 5) {
			ajax({
				func: doneUpCountAfterNoname,
				action: 'count',
				load: loadCount,
				step: ++step,
				start: start
			});
		} else {
			ajax({
				func: doneDel,
				action: 'delete',
				count: info[1].del + start,
				step: 1,
				load: loading,
				start: start
			});
		}

	} else {
		if (checkError(step, xhr, txtError))
			return;
	}
}
// Обновление количества
function updateCount(e) {
	var step = e.dataset.e;
	ajax({
		func: doneUpCount,
		action: 'count',
		load: loadCount,
		step: step
	});
}
// Обработка ответа сервера при загрузке таблицы
function doneCount(xhr,step) {
	if (consoleDebug) {
		console.log(xhr);
	}
	txtError.classList.remove('load');
	if (xhr.status == 200) {
		var res = xhr.response;
		if (res && typeof res == 'object') {
			res = res.slice(0, 2);
			info[step].count = res[0];
			info[step].maxCount = +res[0];
			var list = res.join("</td><td class='td'>");
			infoTable.firstChild.innerHTML += "<tr id='" + info[step].query + "'><td class='td' title='" + info[step].name + "'>" + info[step].query + "</td><td class='td count' onclick='updateCount(this);' data-e='" + step + "'>" + list + "</td><td class='td'>0</td><td class='td'>0</td><td class='td'><input class='loop' type='checkbox'></td><td class='tdc'><button onclick='Del(this);' data-e='" + step + "'>Удалить</button></td><td class='tdc'></td><td class='tdc delmsg'></td></tr>";
			if (info[step].error) {
				delete info[step].error;
			}
		}
		if (++step in info) {
			ajax({
				func: doneCount,
				action: 'count',
				step: step,
				load: loadTable
			});
		} else {
			testBut.disabled = false;
			autoBut.disabled = false;
			stopBut.disabled = false;
		}
	} else {
		if (checkError(step, xhr,txtError))
			return;
	}
}
// Проверка и запись ошибок
function checkError(step, xhr,elem) {
	if (consoleDebug) {
		console.log(xhr.status,xhr.statusText);
	}
	elem.innerHTML = xhr.status + ' ' + xhr.statusText;
	if (!info[step].error) {
		info[step].error = {status: xhr.status, statusText: xhr.statusText, count: 1}
	} else {
		if(info[step].error.status == xhr.status) {
			info[step].error.count++;
		}
	}
	return info[step].error.count > 3;
}
// Обработка ответа сервера на запрос удаления
function doneDel(xhr,step,start) {
	if (consoleDebug) {
		console.log(xhr);
	}
	var tr = document.querySelector('#' + info[step].query);
	tr.cells[7].classList.remove('load');
	if (xhr.status == 200) {
		var res = xhr.response;
		if (res && typeof res == 'object') {
			res = res.slice(0, 2);
			info[step].count -= res[0];
			info[step].countDel += res[0];
			info[step].fullTime += res[1];
			info[step].fullTime = Math.round(info[step].fullTime * 10000) / 10000
			tr.cells[1].innerHTML = info[step].count;
			tr.cells[2].innerHTML = res[1];
			tr.cells[3].innerHTML = info[step].countDel;
			tr.cells[4].innerHTML = info[step].fullTime;
			//tr.cells[8].innerHTML = 'Удалено ' + info[step].name + ' ' + res[0];
			var proc = Math.round(info[step].countDel / info[step].maxCount * 100);
			proc = (proc ? (proc > 100 ? 100 : proc) : 100) + '%';
			tr.cells[8].innerHTML = proc;
			document.title = (+step+1) + '/' + info.length + ' ' + proc + ' Очистка БД';
			var loop = tr.cells[5].firstChild.checked;
			if (info[step].error) {
				delete info[step].error;
			}
			if (loop && info[step].count > 0 && res[0] > 0) {
				ajax({
					func: doneDel,
					action: 'delete',
					count: info[step].del + start,
					step: step,
					load: loading,
					start: start
				});
			} else if (auto) {
				if (step == 0) {
					ajax({
						func: doneUpCountAfterNoname,
						action: 'count',
						load: loadCount,
						step: 3,
						start: start
					});
				} else if(++step in info) {
					ajax({
						func: doneDel,
						action: 'delete',
						count: info[step].del + start,
						step: step,
						load: loading,
						start: start
					});
				}
			}
		}
	} else {
		if (checkError(step, xhr,tr.cells[8])) {
			return;
		}
		var loop = tr.cells[3].firstChild.checked;
		if (loop) {
			ajax({
				func: doneDel,
				action: 'delete',
				count: info[step].del + start,
				step: step,
				load: loading,
				start: start
			});
		}
	}
}
// Включение всех чекбоксов loop
function checkBox(b) {
	var inputs = document.querySelectorAll('input.loop');
	for (var i = 0; i < inputs.length; i++) {
		inputs[i].checked = b;
	}
}
// Удаление
function Del(e,a) {
	if (a) {
		auto = true;
		checkBox(true);
	}
	var step = e.dataset.e;
	ajax({
		func: doneDel,
		action: 'delete',
		count: info[step].del,
		step: step,
		load: loading,
		start: 0
	});
}
// Остановить автоудаление
function Stop() {
	auto = false;
	checkBox(false);
}
// Процесс удаления
function loading(step) {
	var tr = document.querySelector('#' + info[step].query);
	tr.cells[7].innerHTML = '';
	tr.cells[7].classList.add('load');
}
// Процесс загрузки таблицы
function loadTable() {
	txtError.classList.add('load');
}
// Старт загрузки таблицы
function startTest() {
	testBut.disabled = true;
	auto = false;
	if (infoTable) {
		infoTable.remove();
	}
	infoTable = document.createElement('table');
	infoTable.classList.add('msgTable');
	infoTable.innerHTML = "<tr><th class='td'>Name</th><th class='td'>Count</th><th class='td'>Time</th><th class='td'>Delete</th><th class='td'>FullTime</th><th class='td'>Loop</th><th class='tdc'></th><th class='tdc'></th><th class='tdc'></th></tr>";
	main.appendChild(infoTable);
	ajax({
		func: doneCount,
		action: 'count',
		step: 0,
		load: loadTable
	});
}
testBut.addEventListener('click', startTest, true);
</script>
</body>
</html>
<?
	} else {
		header('Content-Type: application/json;');
		$config = mainConfiguration::getInstance();

		$host = $config->get("connections", "core.host");
		$user = $config->get("connections", "core.login");
		$password = $config->get("connections", "core.password");
		$db = $config->get("connections", "core.dbname");
		$port = $config->get("connections", "core.port");
		$connection = ConnectionPool::getInstance()->getConnection();
		$mysqli = new mysqli($host, $user, $password, $db, $port);

		if ($mysqli->connect_error) {
			die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
		}
		if (!$mysqli->real_connect($host, $user, $password, $db, $port)) {
			die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
		} else {
			$cleaner->do_clean();
		}
	}

	/** Класс для очистки базы данных  */
	class cleanAdv {

		public $connection;
		public $order_type_id;
		public $ord_item_type_id;
		public $cust_type_id;
		public $cust_field_id;
		public $ord_items_field_id;

		public function __construct() {

			$this->connection = ConnectionPool::getInstance()->getConnection();

			$types_coll = umiObjectTypesCollection::getInstance();
			//Получаем id типа данных "Заказ"
			$this->order_type_id = $types_coll->getBaseType('emarket', 'order');
			//Получаем id типа данных "Товар в заказе"
			$this->ord_item_type_id = $types_coll->getBaseType('emarket', 'order_item');
			//Получаем id типа данных "Незарегистрированный покупатель"
			$this->cust_type_id = $types_coll->getBaseType('emarket', 'customer');

			$order_type = $types_coll->getType($this->order_type_id);
			//Получаем id поля "customer_id"
			$this->cust_field_id = $order_type->getFieldId('customer_id');
			//Получаем id поля "order_items"
			$this->ord_items_field_id = $order_type->getFieldId('order_items');
		}

		/**
			Обработка запроса, варианты:
			action=count&step=noname
			action=count&step=nocustomer
			action=count&step=noitem
			action=count&step=badcustomers
			action=count&step=baditems
			action=count&step=objcontent
			action=count&step=objcontentimage

			action=delete&step=noname&count=1000
		*/
		public function do_clean() {

			if (!getRequest('action')) {
				echo 'choose action';
				die();
			}

			if (!getRequest('step')) {
				echo 'choose step';
				die();
			}

			$action = getRequest('action');
			$step = getRequest('step');

			$start = 0;
			if (getRequest('start'))
				$start = getRequest('start');

			if ($action == 'delete') {
				$count = getRequest('count');
			}

			switch ($action) {
				case 'count':{
					switch ($step) {
						case 'noname':{
							echo $this->countNoname();
							break;
						}
						case 'nocustomer':{
							echo $this->countNocustomer();
							break;
						}
						case 'noitem':{
							echo $this->countNoitem();
							break;
						}
						case 'badcustomers':{
							echo $this->countBadcustomers();
							break;
						}
						case 'baditems':{
							echo $this->countBaditems();
							break;
						}
						case 'objcontent':{
							echo $this->countObjectContent();
							break;
						}
						case 'objcontentimage':{
							echo $this->countObjectContentImage();
							break;
						}
						default:{
							echo 'wrong step';
							break;
						}
					}
					break;
				}
				case 'delete':{
					switch ($step) {
						case 'noname':{
							echo $this->deleteNoname($count,$start);
							break;
						}
						case 'nocustomer':{
							echo $this->deleteNocustomer($count,$start);
							break;
						}
						case 'noitem':{
							echo $this->deleteNoitem($count,$start);
							break;
						}
						case 'badcustomers':{
							echo $this->deleteBadcustomers($count,$start);
							break;
						}
						case 'baditems':{
							echo $this->deleteBaditems($count,$start);
							break;
						}
						case 'objcontent':{
							echo $this->deleteObjectContent($count);
							break;
						}
						case 'objcontentimage':{
							echo $this->deleteObjectContentImage($count);
							break;
						}
						default:{
							echo 'wrong step';
							break;
						}
					}
					break;
				}

				default:{
					echo 'wrong action ';
					break;
				}
			}
		}

		/** Удаляет заказы без номера(имени) */
		public function deleteNoname($limit,$first) {

			global $order_type_id, $connection, $mysqli;
			$start = microtime(true);

			$special_query = "SELECT `id` FROM `cms3_objects` WHERE `name` IS NULL AND `type_id` = {$this->order_type_id} LIMIT {$first},{$limit}";

			$result_rows = $this->result_query($special_query);
			$count = 0;

			foreach ($result_rows as $result_row) {

				$id = $result_row;

				$objColl = umiObjectsCollection::getInstance();

				if (is_numeric($id)) {
					$succ = $objColl->delObject($id);
					if ($succ) {
						$count++;
					}
				}
			}

			$time = microtime(true) - $start;
			return json_encode([$count, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет заказы без покупателя */
		public function deleteNocustomer($limit,$first) {
			$start = microtime(true);

			$special_query = "SELECT `obj_id` FROM `cms3_object_content` WHERE `field_id` = {$this->cust_field_id} AND `rel_val` IS NULL LIMIT {$first},{$limit}";

			$result_rows = $this->result_query($special_query);
			$count = 0;

			foreach ($result_rows as $result_row) {

				$id = $result_row;

				$objColl = umiObjectsCollection::getInstance();

				if (is_numeric($id)) {
					$succ = $objColl->delObject($id);
					if ($succ) {
						$count++;
					}
				}
			}
			$time = microtime(true) - $start;
			return json_encode([$count, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет заказы без товаров */
		public function deleteNoitem($limit,$first) {
			$start = microtime(true);

			$special_query = "SELECT `obj_id` FROM `cms3_object_content` WHERE `field_id` = {$this->ord_items_field_id} AND `rel_val` IS NULL LIMIT {$first},{$limit}";

			$result_rows = $this->result_query($special_query);
			$count = 0;

			foreach ($result_rows as $result_row) {

				$id = $result_row;

				$objColl = umiObjectsCollection::getInstance();

				if (is_numeric($id)) {
					$succ = $objColl->delObject($id);
					if ($succ) {
						$count++;
					}
				}
			}
			$time = microtime(true) - $start;
			return json_encode([$count, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет незарегестрированных покупателей без заказов */
		public function deleteBadcustomers($limit,$first) {
			$start = microtime(true);

			$special_query = "SELECT `id` FROM `cms3_objects` WHERE `type_id` = {$this->cust_type_id} AND `id` NOT IN (SELECT `rel_val` FROM `cms3_object_content` WHERE `field_id` = {$this->cust_field_id}) LIMIT {$first},{$limit}";

			$result_rows = $this->result_query($special_query);
			$count = 0;

			foreach ($result_rows as $result_row) {

				$id = $result_row;

				$objColl = umiObjectsCollection::getInstance();

				if (is_numeric($id)) {
					$succ = $objColl->delObject($id);
					if ($succ) {
						$count++;
					}
				}
			}
			$time = microtime(true) - $start;
			return json_encode([$count, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет элементы заказа не содержащиеся ни в одном заказе */
		public function deleteBaditems($limit,$first) {
			$start = microtime(true);

			$special_query = "SELECT `id` FROM `cms3_objects` WHERE `type_id` = {$this->ord_item_type_id} AND `id` NOT IN (SELECT `rel_val` FROM `cms3_object_content` WHERE `field_id` = {$this->ord_items_field_id}) LIMIT {$first},{$limit}";

			$result_rows = $this->result_query($special_query);
			$count = 0;

			foreach ($result_rows as $result_row) {

				$id = $result_row;

				$objColl = umiObjectsCollection::getInstance();

				if (is_numeric($id)) {
					$succ = $objColl->delObject($id);
					if ($succ) {
						$count++;
					}
				}
			}
			$time = microtime(true) - $start;
			return json_encode([$count, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет пустые поля объектов */
		public function deleteObjectContent($limit) {
			$start = microtime(true);

			$special_query = "DELETE QUICK FROM `cms3_object_content` WHERE `int_val` IS NULL AND `varchar_val` IS NULL AND `text_val` IS NULL AND `rel_val` IS NULL AND `tree_val` IS NULL AND `float_val` IS NULL LIMIT $limit";

			$this->connection->query($special_query);

			$time = microtime(true) - $start;
			$countDel = $this->connection->affectedRows();

			return json_encode([$countDel, round($time,5),$this->connection->errorMessage()]);
		}

		/** Удаляет пустые поля объектов изображений */
		public function deleteObjectContentImage($limit) {
			$start = microtime(true);

			$special_query = "DELETE QUICK FROM `cms3_object_images` WHERE `src` IS NULL AND `alt` IS NULL AND `title` IS NULL AND `ord` IS NULL LIMIT $limit";

			$this->connection->query($special_query);

			$time = microtime(true) - $start;
			$countDel = $this->connection->affectedRows();

			return json_encode([$countDel, round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве пустых полей объектов */
		public function countObjectContent() {
			$start = microtime(true);

			$special_query = "SELECT count(*) FROM `cms3_object_content` WHERE `int_val` IS NULL AND `varchar_val` IS NULL AND `text_val` IS NULL AND `rel_val` IS NULL AND `tree_val` IS NULL AND `float_val` IS NULL";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве пустых полей объектов изображений */
		public function countObjectContentImage() {
			$start = microtime(true);

			$special_query = "SELECT count(*) FROM `cms3_object_images` WHERE `src` IS NULL AND `alt` IS NULL AND `title` IS NULL AND `ord` IS NULL";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве заказов без номера */
		public function countNoname() {
			$start = microtime(true);

			$special_query = "SELECT count(`id`)t FROM `cms3_objects` WHERE `name` IS NULL AND `type_id` = {$this->order_type_id}";

			$total = $this->result_query($special_query);

			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве заказов без покупателя */
		public function countNocustomer() {
			$start = microtime(true);

			$special_query = "SELECT count(`obj_id`) FROM `cms3_object_content` WHERE `field_id` = {$this->cust_field_id} AND `rel_val` IS NULL";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве заказов без товаров */
		public function countNoitem() {
			$start = microtime(true);

			$special_query = "SELECT count(`obj_id`) FROM `cms3_object_content` WHERE `field_id` = {$this->ord_items_field_id} AND `rel_val` IS NULL";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве незарегестрированных поккупателей без заказов */
		public function countBadcustomers() {
			$start = microtime(true);

			$special_query = "SELECT count(`id`) FROM `cms3_objects` WHERE `type_id` = {$this->cust_type_id} AND `id` NOT IN (SELECT `rel_val` FROM `cms3_object_content` WHERE `field_id` = {$this->cust_field_id})";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Возвращает информацию о количестве элементов заказа не содержащихся ни в одном заказе */
		public function countBaditems() {
			$start = microtime(true);

			$special_query = "SELECT count(`id`) FROM `cms3_objects` WHERE `type_id` = {$this->ord_item_type_id } AND `id` NOT IN (SELECT `rel_val` FROM `cms3_object_content` WHERE `field_id` = {$this->ord_items_field_id})";

			$total = $this->result_query($special_query);
			$time = microtime(true) - $start;
			return json_encode([$total[0], round($time,5),$this->connection->errorMessage()]);
		}

		/** Выполняет SQL запрос и возвращает результат в виде массива или false */
		private function result_query($query) {
			$result = $this->connection->queryResult($query);
			$result_arr = [];

			while($row = $result->fetch()) {
				$result_arr[] = $row[0];
			}

			return $result_arr;
		}
	}