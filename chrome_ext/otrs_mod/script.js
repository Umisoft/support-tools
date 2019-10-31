(function() {
	
	// Работа с localStorage >>
	function lsSet(key,data) {
		if (typeof data == "string")
			localStorage.setItem(key,data);
		else {
			textdata = JSON.stringify(data);
			localStorage.setItem(key,textdata);
		}
	}
	
	function lsGet(key) {
		var data = localStorage.getItem(key);
		try {
			return JSON.parse(data);
		} catch (e) {
			return data;
		}		
	}
	
	function lsDel(key) {
		localStorage.removeItem(key)
	}
	
	function lsClear() {
		localStorage.clear();
	}
	
	if (!lsGet("OTRS_mod")) {
		lsSet("OTRS_mod",{currUserId:0,newmsg:{}})
	}
	// << Работа с localStorage
	
	// Инициализация переменных скрипта >>
	console.log('Script run')
	var TicketOverview = document.querySelector("#TicketOverviewLarge");
	var ch = document.querySelector("#ContextSettingsDialog > input[name='ChallengeToken']");
	var ChallengeToken;
	var autoup = {};
	var tiketlist = {};
	var CheckMSG = false;
	var currUserId = lsGet("OTRS_mod").currUserId;
	var OTRS_owners = lsGet("OTRS_owners");
	if (!OTRS_owners) {	
		OTRS_owners = {
			owners: [],
			updatedate: Date.now() + (2*7*24*60*60*1000),
			ownersinfo: {}
		};
		lsSet("OTRS_owners",OTRS_owners);
	}
		
	getOwners();
	var owners, ticks;
	// << Инициализация переменных скрипта
	
	// Получение GET параметров 
	var GetParams = window.location.search.replace('?','').split(';').reduce(
        function(p,e){
            var a = e.split('=');
            p[ decodeURIComponent(a[0])] = decodeURIComponent(a[1]);
            return p;
        },
        {}
    );

	// Второй вариант получения GET параметров
	function GetQuery() {
		var query = {};
		location.search.substr(1).split(';').forEach(function(e) {arr=e.split('=');query[arr[0]] = arr[1];});
		return query;
	}
	
	// Фитча Open access для недоступных тикетов
	if ($(".Error").length && $(".Error").html() == "Insufficient Rights.") {
		var wigdget = $(".Error").parent().parent();
		var access = $("<p class='SpacingTop'><a href='#' id='GetAccess' onclick='return false;'>Get access</a></p>").appendTo(wigdget);
		if (GetParams.TicketID) {
			$(access).click(function() {
				$.ajax({
					type: "GET",
					dataType: "html",
					url: "//otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketWatcher;Subaction=Subscribe;TicketID="+GetParams.TicketID,
					success: function(msg){
						location.reload();
					}
				});
			});
		}
	}

	// Добавление интерфейса на странице ЗАЯВКИ
	var Actions = $(".Actions")[0];
	if (Actions) {
		// Создание кнопочки форматирование
		var li = document.createElement("li");
		li.innerHTML = "<a href='#' onclick='return false;' title='Формат'>Формат</a>";
		Actions.appendChild(li);
		li.onclick = function() {
			var iframe = $("iframe[id^=Iframe]")[0]
			if (iframe) {
				var body = iframe.contentWindow.document.body;
				var pre = $(body).find("pre");
				var text = pre.html();				
				var div = document.createElement("div");
				div.innerHTML = text;
				$(div).insertBefore(pre);
				pre.remove();
			}
		};
		// Создание кнопочки открыть тикет
		var liOpen = document.createElement("li");
		liOpen.innerHTML = "<a href='#' onclick='return false;' title='Открыть'>Открыть</a>";
		Actions.appendChild(liOpen);
		liOpen.onclick = function() {
			OpenTicket();
		};
		// Кнопка передать тикет Ивану Кубышкину
		/*var ivanMsg = document.createElement("li");
		ivanMsg.innerHTML = "<a href='#' onclick='return false;' title='Ivan msg'>Ivan msg</a>";
		Actions.appendChild(ivanMsg);
		ivanMsg.onclick = function() {
			ivanMsgSend();
		};*/
	}
	
	// Вкл/Выкл проверку новых сообщений в заявках
	// //otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketLockedView;Filter=New
	// <h1>Мои заблокированные заявки: Новое сообщение</h1>
	if (~window.location.href.indexOf("index.pl?Action=AgentTicketQueue")) {
		if (!$("ul").is("#ToolBar")) {
			$("#Logo").after("<ul id='ToolBar'></ul>");
		}
		var chmsg = $("<li class='Responsible'><a href='#' onclick='return false;' accesskey='' title='Вкл/Выкл проверку сообещний'>Описание<span class='Gloss'></span></a></li>").appendTo("#ToolBar");
		$(chmsg[0]).find("a").click(function() {
			if (this.dataset.check === undefined)
				this.dataset.check = 0;
			this.dataset.check = +this.dataset.check ? 0 : 1;
			if (+this.dataset.check) {
				console.log("checkNewMsg on");
				CheckMSG = false;
				this.style.boxShadow = "0px 0px 5px 0px #fff";
				checkNewMsg();				
			} else {
				console.log("checkNewMsg off");
				CheckMSG = true;
				this.style.boxShadow = "0px 0px 0px 0px #fff";
			}
		});
	}

	// Создание новых кнопочек в основной менюшке, функция поиска searchAJAX();
	if (TicketOverview && ch.value) {	
		ChallengeToken = ch.value;
		chrome.runtime.sendMessage({info: ChallengeToken});
		newTicjets({
			id: "nav-newTickets",
			title: "Новые",
			func: searchAJAX,
			query: "ChallengeToken=" + ChallengeToken + "&Action=AgentTicketSearch&View=Preview&Subaction=Search&EmptySearch=1&ShownAttributes=%3BLabelTicketNumber%3BLabelQueueIDs%3BLabelTicketCreateTimePoint&Name=&TicketNumber=*&QueueIDs=49&QueueIDs=50&QueueIDs=51&QueueIDs=44&QueueIDs=22&QueueIDs=52&QueueIDs=53&QueueIDs=58&QueueIDs=76&QueueIDs=73&QueueIDs=54&QueueIDs=10&QueueIDs=9&QueueIDs=5&QueueIDs=2&QueueIDs=14&QueueIDs=25&QueueIDs=19&QueueIDs=55&QueueIDs=94&QueueIDs=75&QueueIDs=74&TimeSearchType=TimePoint&TicketCreateTimePointStart=Last&TicketCreateTimePoint=1&TicketCreateTimePointFormat=hour&AttributeOrig=Fulltext&ResultForm=Normal"
		});
		if (currUserId) {
			newTicjets({
				id: "nav-myTickets",
				title: "Мои",
				func: searchAJAX,
				query:"ChallengeToken=" + ChallengeToken + "&Action=AgentTicketSearch&View=Preview&Subaction=Search&EmptySearch=1&ShownAttributes=%3BLabelTicketNumber%3BLabelOwnerIDs%3BLabelTicketCloseTimePoint&Name=&TicketNumber=*&OwnerIDs=" + currUserId + "&CloseTimeSearchType=TimePoint&TicketCloseTimePointStart=Last&TicketCloseTimePoint=8&TicketCloseTimePointFormat=hour&AttributeOrig=Fulltext&ResultForm=Normal"
			});
			/*newTicjets({
				id: "nav-myTest",
				title: "Тест",
				func: OpenTicket,
				query: "ChallengeToken="+ChallengeToken+"&Action=AgentTicketNote&Subaction=Store&TicketID=610263&NewOwnerType=New&NewOwnerID=" + currUserId + "&Subject=Open&Body=%2B&ArticleTypeID=9&NewStateID=4"
			});*/
		}
		newTicjets({
			id: "nav-myStat",
			title: "Stat",
			func: countAJAX,
			query:"ChallengeToken="+ChallengeToken+"&Action=AgentTicketSearch&View=Small&Subaction=Search&EmptySearch=1&ShownAttributes=%3BLabelTicketNumber%3BLabelOwnerIDs%3BLabelTicketCloseTimeSlot&Name=&TicketNumber=*&CloseTimeSearchType=TimeSlot&AttributeOrig=Fulltext&ResultForm=Normal"
		});
		newTicjets({
			id: "nav-mySettings",
			title: "Настройки",
			func: function(){
					var infoSettings = new Msg("Настройки");
					var selectOwners = document.createElement("select");
					selectOwners.name = "OwnerIDs";
					selectOwners.multiple = "multiple";
					selectOwners.style = "height: 224px;";
					var options = "";				
					for(var i in OTRS_owners.owners) {
						var idOwner = OTRS_owners.owners[i][0];
						var nameOwner = OTRS_owners.owners[i][1];					
						options += "<option value='" + idOwner + "' " + (idOwner in OTRS_owners.ownersinfo ? "selected" : "") + ">" + OTRS_owners.owners[i][1] + "</option>"
					}
					//var selectraits = $("#traitslist>option:selected").map(function(){ return this.value }).get();
					selectOwners.innerHTML = options;
					infoSettings.addCallback(function() {
						var selOwners = $(selectOwners).find("option:selected").map(function(){ return this.value; }).get();
						OTRS_owners.ownersinfo = {};
						for(var n in selOwners)
							OTRS_owners.ownersinfo[selOwners[n]] = 1;
						lsSet("OTRS_owners",OTRS_owners);
					});
					infoSettings.addInfo(selectOwners);
					infoSettings.showInfo();
				},
			query:""
		});
		/** Поиск пока не работает */
		/*newTicjets({
			id: "nav-mySearch",
			title: "Поиск",
			func: function(){
					var winSearch = new Msg("Поиск");
					var mainDiv = document.createElement("div");
					var fieldSearch = document.createElement("div");
					fieldSearch.name = "FieldSearch";
					fieldSearch.style = "height: 224px; width: 300px;";
					var inputFulltext = document.createElement("input");
					inputFulltext.type = "text";
					inputFulltext.name = "Fulltext";
					inputFulltext.style = "width: 290px; margin-bottom: 5px;";
					inputFulltext.classList.add("W50pc");			
					fieldSearch.appendChild(inputFulltext);
					var buttSearch  = document.createElement("button");
					buttSearch.innerHTML = "Поиск";
					fieldSearch.appendChild(buttSearch);
					var infoOutput = document.createElement("div");
					fieldSearch.appendChild(infoOutput);
					mainDiv.appendChild(fieldSearch);
					winSearch.addCallback(function() {
						console.log("winSearch.addCallback");
					});
					winSearch.addInfo(mainDiv);
					winSearch.showInfo();
				},
			query:""
		});*/
		newTicjets({
			id: "nav-myTestKey",
			title: "TestGlobalMsg",
			func: function(){
					// Тестирование глобальных оповещений
					chrome.runtime.sendMessage({type: "alert",msg: "Эта кнопка НИЧЕГО не делает! Не надо ее нажимать."}, function(response) {
						if (response.answer)
							console.log("Вы сделали верный выбор!!");
						else
							console.log("Может быть в другой раз. ;)")
					});
				},
			query:""
		});
	}
	
	/** Определение текущего пользователя */
	function currUser(own) {
		var elem = document.querySelector('a[title^="Edit"]');
		if (elem) {
			var nameUser = elem.innerHTML.split(' ').reverse().join(' ');
			for(var u in own.owners) {
				if(~own.owners[u][1].indexOf(nameUser)) {
					currUserId = own.owners[u][0];
					break;
				}
			}
			elem.innerHTML += "("+currUserId+")";
			ldata = lsGet("OTRS_mod");
			ldata.currUserId = currUserId;
			lsSet("OTRS_mod", ldata);
		}	
		console.log(currUserId);
	}

	/** Очистка устаревших записей о новых сообщениях */
	function trashСlean(data, maxtime) {
		for (var j in data) {
			var time = (Date.now() - data[j])/1000;
			if (time > maxtime)
				delete data[j];
		}
		return data;
	}
	
	/** Проверка новых сообщений */
	function checkNewMsg() {
		if (CheckMSG) return;
		$.ajax({
			type: "GET",
			dataType: "html",
			url: "//otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketLockedView;Filter=New",
			success: function(msg){
				if (msg) {
					//console.log("Ответ пришел");
					var doc = getDOM(msg);
					var tickets = $(doc).find("li[id^=TicketID_]");
					var tickcount = tickets.length;
					var newpic = $("#ToolBar > .Locked.New");
					if (newpic.is("li")) {
						newpic.find(".Counter").html(tickcount);
						newpic.find("a").attr("title", "Заблокированные заявки: Новые: "+tickcount+" (k)")
					} else {
						$("<li class='Locked New Even'><a href='/otrs/index.pl?Action=AgentTicketLockedView;Filter=New' accesskey='k' title='Заблокированные заявки: Новые: "+tickcount+" (k)'><span class='Counter'>"+tickcount+"</span> Описание<span class='Gloss'></span></a></li>").prependTo("#ToolBar")				
					}
					ldata = lsGet("OTRS_mod");
					ldata.newmsg = trashСlean(ldata.newmsg, 90);
					tickets.each(function(i,elem) {
							var elemid = /TicketID_(\d{6})/.exec(elem.id)[1];
							if (!(elemid in ldata.newmsg)) {
								ldata.newmsg[elemid] = Date.now();
								// Глобальное оповещение о сообщении в заявке
								chrome.runtime.sendMessage({type: "alert",msg: "Новое сообщение в заявке!"}, function(response) {
									console.log(response.answer);
								});
								//alert("Новое сообщение в заявке!");
							}
						}
					);
					lsSet("OTRS_mod", ldata);
				}
				setTimeout(checkNewMsg, 30000);
			}
		});
		//console.log( "Запрос ушел" );
	}
	
	/** Функция для добавления новых кнопочек */
	function newTicjets(param) {
		var nav = document.getElementById("Navigation");
		var li = document.createElement("li");
		li.id = param.id;
		li.dataset["query"] = param.query;
		li.innerHTML = "<a href='#' onclick='return false;' title='"+param.title+"'>"+param.title+"</a>";		
		nav.insertBefore(li, nav.querySelector("#nav-Customers"));
		document.querySelector("#BulkAction").dataset["dfdsf"];
		autoup[param.id] = 0;
		li.onclick = function() {
			offAutoup();
			autoup[this.id] = true;
			param.func(this);
		};
	}
	
	/** Отключение автообновления страницы для всех кнопочек */
	function offAutoup() {
		var sum = 0;
		for (var i in autoup) {
			if (autoup.hasOwnProperty(i)) {
				autoup[i] = false;
			}
		}
	}
	
	/** Получение DOM дерева из текстовых данных страницы */
	function getDOM(text) {
		if (text) {
			var responseObj = document.implementation.createHTMLDocument(null);
			var body_1=text.toLowerCase().indexOf("<body");
			var body_2=text.toLowerCase().indexOf("<\/body>");
			if (body_1!=-1 && body_2!=-1)
			{
				text=text.substring(body_1,body_2+7);
			}
			responseObj.documentElement.innerHTML = text;
			return responseObj;
		}
		else
			return null;
	}
	
	/** Определение дат начала и конца текущей недели */
	function getWeek(d) {
		d = d || new Date();
		var d1 = new Date(d);
		d1.setDate(d1.getDate() - d1.getDay() + 1);
		var d2 = new Date(d);
		d2.setDate(d2.getDate() + (7 - d2.getDay()));
		return {
			start:{
				day:d1.getDate(),
				month:d1.getMonth() + 1,
				year:d1.getFullYear()
			},
			end:{
				day:d2.getDate(),
				month:d2.getMonth() + 1,
				year:d2.getFullYear()
			}};
	}
	
	/** Обновление списка пользователей OTRS */
	function getOwners() {
		// Если прошло больше недели обновляем
		if (Math.abs(Date.now() - OTRS_owners.updatedate) > (7*24*60*60*1000)) {		
			$.ajax({
				type: "GET",
				dataType: "html",
				url: "//otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketSearch;Subaction=AJAX",
				success: function(msg){
					var doc = getDOM(msg);
					var selectOwners = doc.querySelector("#OwnerIDs");
					var owners = [];
					for(var i = 0; i < selectOwners.options.length; i++) {
						owners.push([selectOwners.options[i].value,selectOwners.options[i].innerText]);
					}
					
					OTRS_owners.owners = owners;
					OTRS_owners.updatedate = Date.now();
					lsSet("OTRS_owners",OTRS_owners);
					currUser(OTRS_owners);
					console.log(OTRS_owners);
				}
			});
		} else {
			currUser(OTRS_owners);
		}
	}
	
	/** Подготовка списка пользователей для сбора статистики */
	function setListOwners() {
		var ownArr = [];
		for(var i in OTRS_owners.owners) {
			var idOwner = OTRS_owners.owners[i][0];
			var nameOwner = OTRS_owners.owners[i][1];		
			if(idOwner in OTRS_owners.ownersinfo)
				ownArr.push([idOwner,nameOwner]);
		}
		owners = ownArr;
		ticks = owners;
	}
	
	// 3 типа тикетов
	var type = [
		"&QueueIDs=50&QueueIDs=51&QueueIDs=44&QueueIDs=22&QueueIDs=52&QueueIDs=10&QueueIDs=9&QueueIDs=5&QueueIDs=2&QueueIDs=14&QueueIDs=19&QueueIDs=55&QueueIDs=74&QueueIDs=116", // все
		"&QueueIDs=62", // umi.ru
		"&QueueIDs=53&QueueIDs=25" // разработка
	];
	
	/** Сбор статистики количества закрытых тикетов за текущую неделю */
	function countAJAX(e,n,own,t,msgWin) {
		msgWin = msgWin || new Msg("Tikets");	
		n = n || 0;
		t = t || 0;
		own = own || 0;
		if(!n && !t && !own) {
			setListOwners();
			msgWin.addTableInfo("<tr><th class='td'>Name</th><th class='td'>Box</th><th class='td'>Cloud</th><th class='td'>Develop</th><th class='td'>Total</th></tr>");
		}
		if (!own)
			ticks = owners;
		if (autoup[e.id] && owners[own]) {
			$("#Navigation").children().removeClass( "Selected Even" );
			$(e).addClass( "Selected Even" );			
			var queryData = e.dataset.query;
			queryData += "&OwnerIDs="+owners[own][0];
			var week = getWeek();
			queryData += "&TicketCloseTimeStartDay=" + week.start.day;
			queryData += "&TicketCloseTimeStartMonth=" + week.start.month;
			queryData += "&TicketCloseTimeStartYear=" + week.start.year;
			queryData += "&TicketCloseTimeStopDay=" + week.end.day;
			queryData += "&TicketCloseTimeStopMonth=" + week.end.month;
			queryData += "&TicketCloseTimeStopYear=" + week.end.year;
			queryData += type[t];
			$.ajax({
				type: "POST",
				dataType: "html",
				url: "//otrs.umisoft.ru/otrs/index.pl",
				data: queryData,
				success: function(msg){
					var doc = getDOM(msg);
					var pag = doc.querySelector(".Pagination").innerText;
					var res = /\d-\d{1,2} из (\d{1,3})/.exec(pag);
					var count = 0;
					if (res) {
						count = +res[1];
					} else {
						var tick = doc.querySelectorAll("[id^=TicketID]");
						if (tick) {
							count = +tick.length;
						}
					}
					ticks[own][t+2] = count;
					switch (t) {
						case 0:
							countAJAX(e, count, own, ++t, msgWin);
							break;
						case 1:
							countAJAX(e, n + count / 3, own, ++t, msgWin);
							break;
						case 2:
							var name = owners[own][1];
							var total = Math.round((n + count * 2) * 100) / 100;
							var list = ticks[own].slice(2).join("</td><td class='td'>");
							var str = "<tr><td class='td'>" + name + "</td><td class='td'>" + list + "</td><td class='td'>" + total + "</td></tr>";
							msgWin.addTableInfo(str);
							console.log(name, total, ticks[own].slice(2));
							if (++own in owners)
								countAJAX(e, 0, own, 0, msgWin);
							else
								msgWin.showInfo();
							break;
					}
				}
			});
		}
	}
	
	/** Отправка и обработка AJAX запросов для обновления страницы */
	function searchAJAX(e) {		
		if (autoup[e.id]) {
			$("#Navigation").children().removeClass( "Selected Even" );
			$(e).addClass( "Selected Even" );
			var queryData = e.dataset.query;
			$.ajax({
				type: "POST",
				dataType: "html",
				url: "//otrs.umisoft.ru/otrs/index.pl",
				data: queryData,
				success: function(msg){
					var doc = getDOM(msg);
					var docticketlist = doc.querySelector("#TicketOverviewLarge");
					if (!docticketlist) {
						docticketlist = doc.querySelector("#FixedTable");
						TicketOverview.innerHTML = docticketlist.outerHTML						
					} else {
						TicketOverview.innerHTML = docticketlist.innerHTML;
					}
					//var docticketlist = doc.querySelector("#OverviewBody");					
					arrScript = doc.querySelectorAll("script");
					var jstext = "";
					for (var i in arrScript) {
						if (arrScript.hasOwnProperty(i)) {
							var code = arrScript[i].innerHTML;
							if (!~(code.indexOf("UnblockEvents")))
								jstext += arrScript[i].innerHTML;
						}
					}
					eval(jstext);
					//console.log("Ответ пришел");
					var time = $('label:contains("Возраст")').parent();
					time.each(function(i,elem) {
						var linka = $("h2 a")[i];
						var title = linka.innerHTML;
						var tID = linka.href.split('=')[2];
						var block = $('label:contains("Блокировка")').next()[i];
						var rest = /Ticket#: (\d{16})/.exec(title);						
						var res = /Возраст\s(\d{1,2}) мин/.exec(elem.innerText);
						if (res && rest && block.innerText == "разблокирован") {
							var numt = rest[1];
							//console.log(res[1]);
							var tiketdiv = $("li:nth-child(1) > div > div.Content.ArticleBody")[i];					
							if (res[1] < 1 && !(numt in tiketlist)) {
								var conftext = tiketdiv.innerText.replace( /\s{2,10}/g, "\n" );
								if (~conftext.indexOf("Ваш вопрос был выделен в отдельную заявку.")) {
									console.log("Созданая заявка");
								} else {
									// Обновленный функционал глобального оповещения о новой заявке
									chrome.runtime.sendMessage({type: "answer",msg: conftext}, function(response) {
										console.log(response.answer);
										if (response.answer) {								
											$.ajax({
											type: "GET",
											url: "//otrs.umisoft.ru/otrs/index.pl",
											data: "Action=AgentTicketLock;Subaction=Lock;TicketID="+tID,
											success: function(result){
													console.log("send");
												}
											});
										} else {
											console.log("Может быть в другой раз.")
										}
									});
								}								
								/*
								//otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketLock;Subaction=Lock;TicketID=553978
								*/
								tiketlist[numt] = true;
							}
						}
					});
				}
			});
			//console.log( "Запрос ушел" );
			setTimeout(searchAJAX, 20000, e);
		}
	}
	
	/** Кнопка открыть тикет */
	function OpenTicket() {
		var ct = document.querySelector("input[name='ChallengeToken']")
		var ChallengeToken = ct.value;
		var query = GetQuery();
		var queryData = "ChallengeToken="+ChallengeToken+"&Action=AgentTicketNote&Subaction=Store&NewOwnerType=New&ArticleTypeID=9";	
		queryData += "&TicketID=" + query['TicketID']; // Номер тикета
		queryData += "&NewOwnerID=" + currUserId; // Новый владелец
		queryData += "&Subject=Open"; // Название
		queryData += "&Body=%2B"; // Описание
		queryData += "&NewStateID=4"; // Следующее состояние (2 закрыт успешно, 6 ожидает напоминания, 4 открытый)		
		$.ajax({
				type: "POST",
				dataType: "html",
				url: "//otrs.umisoft.ru/otrs/index.pl",
				data: queryData,
				success: function(msg){
					location.reload();
				}
		});
	}
	
	/** Кнопка передать тикет Ивану Кубышкину */
	function ivanMsgSend() {
		var ct = document.querySelector("input[name='ChallengeToken']")
		var ChallengeToken = ct.value;
		var query = GetQuery();
		var queryData = "ChallengeToken=" + ChallengeToken + "&Action=AgentTicketNote&Subaction=Store&NewOwnerType=New&ArticleTypeID=9";	
		queryData += "&TicketID=" + query['TicketID']; // Номер тикета
		queryData += "&NewOwnerID=153"; // Новый владелец
		queryData += "&Subject=%D0%97%D0%B0%D0%BC%D0%B5%D1%82%D0%BA%D0%B0"; // Название
		queryData += "&Body=%D0%9F%D0%9E%D0%A7%D0%95%D0%9D%D0%98%20%D0%90"; // Описание
		queryData += "&NewStateID=4"; // Следующее состояние (2 закрыт успешно, 6 ожидает напоминания, 4 открытый)		
		$.ajax({
				type: "POST",
				dataType: "html",
				url: "//otrs.umisoft.ru/otrs/index.pl",
				data: queryData,
				success: function(msg){
					location.reload();
				}
		});
	}
	
	/** 
	 *	Создание окна с информацией
	 *	Пример использования:
	 *	var infoMsg = new Msg("Информация");
	 *	// Можно добавлять текстовую информацию:
	 *	infoMsg.addInfo("Очень важное сообщение!")
	 *	// Или табличку
	 *	infoMsg.addTableInfo("<tr><th class='td'>Name</th><th class='td'>Speed</th></tr>" + 
	 *						"<tr class='td'><td class='td'>Joe</td><td class='td'>10 km/h</td></tr>")
	 *	// Отображаем информацию
	 *	infoMsg.showInfo();	
	*/ 
	function Msg(title) {
		var self = this;
		
		this.close = function() {
			self.msg.remove();
			self.overlay.remove();
		}
		
		this.addInfo = function(elem,add) {
			if(typeof elem == "string")
				if (add) {
					self.infoText.innerHTML += "<p>" + elem + "</p>";
				} else {
					self.infoText.innerHTML = "<p>" + elem + "</p>";
				}
			else if(typeof elem == "object") {
				self.info.appendChild(elem);
			}
		}
		
		this.addCallback = function(func) {
			self.ok.addEventListener("click",func);
		}
		
		this.addTableInfo = function(text) {
			self.infoTable.firstChild.innerHTML += text;
		}
		
		this.showInfo = function() {
			self.loader.style.display = "none";
			self.info.style.display = "";
			self.Center();
		}
		
		this.Center = function() {
			self.msg.style.left = (window.innerWidth - self.msg.clientWidth)/2 + "px";
			self.msg.style.top = (window.innerHeight - self.msg.clientHeight)/2 - 50 + "px";
		}
		
		this.msg = document.createElement('div');
		document.body.appendChild(this.msg);
		this.msg.classList.add("myMsg")
		this.msg.style.cssText = "\
		text-align: left;\
		border: 1px solid #000;\
		z-index: 100000;\
		background-color: #FFF;\
		position: absolute;\
		border-radius: 15px;\
		min-width: 200px;\
		min-height: 150px;";

		this.msg.style.left = (window.innerWidth-this.msg.clientWidth)/2 + "px";
		this.msg.style.top = (window.innerHeight-this.msg.clientHeight)/2 - 50 + "px";

		this.head = document.createElement('div');
		this.head.classList.add("Header")
		this.head.style.cssText = "\
		height: 25px;\
		cursor: move;\
		display: block;\
		position: relative;\
		border-bottom: 1px solid #CCC;\
		background: linear-gradient(to top, #eee, #fff);\
		border-top-left-radius: 12px;\
		border-top-right-radius: 12px;";
		this.h1 = document.createElement('h1');
		this.h1.innerHTML = title || "";
		this.h1.style.cssText = "\
		margin: 0px;\
		padding: 5px 10px;\
		font-weight: normal;\
		font-size: 14px;\
		padding-top: 5px;";

		this.head.appendChild(this.h1);
		this.msg.appendChild(this.head);

		this.content = document.createElement('div');
		this.content.classList.add("Content");
		this.content.style.cssText = "\
		min-height: 71px;\
		padding: 10px;";
		
		this.loader = document.createElement('div');
		this.loader.style.cssText = "\
		margin: 20px;\
		text-align: center;";
		
		this.imgLoader = document.createElement('span');		
		this.imgLoader.classList.add("msgTable");
		this.imgLoader.title = "Загрузка...";
		this.imgLoader.style.cssText = "\
		display: inline-block;\
		width: 16px;\
		height: 16px;\
		margin: 2px;\
		background: url('data:image/gif;base64,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') no-repeat right center;\
		vertical-align: bottom";

		this.loader.appendChild(this.imgLoader);
		this.content.appendChild(this.loader);
		
		this.info = document.createElement('div');
		this.info.style.cssText = "\
		display: none;";
		
		this.infoText = document.createElement('div');
		
		this.info.appendChild(this.infoText);
		
		this.infoTable = document.createElement('table');
		this.infoTable.innerHTML = "<tbody></tbody>"
		this.infoTable.classList.add("msgTable");
		
		this.info.appendChild(this.infoTable);
		
		s = document.createElement('style');
		s.innerText = ".td{border: 1px solid #CCC;padding: 2px 6px;text-align: center;}";
		document.head.appendChild(s);
		
		this.content.appendChild(this.info);		
		this.msg.appendChild(this.content);

		this.footer = document.createElement('div');
		this.footer.style.cssText = "\
		padding: 6px 0 5px;\
		min-height: 20px;\
		border-top: 1px solid #CCC;\
		text-align: center;\
		background: linear-gradient(to top, #eee, #fff);\
		border-bottom-right-radius: 12px;\
		border-bottom-left-radius: 12px;"
		this.ok = document.createElement('button');
		this.ok.style.cssText = "\
		border-radius: 8px;\
		border: 1px solid #9a9a9a;\
		background: linear-gradient(to top, #ccc, #fff);\
		outline: 0px #000;\
		width: 70px;"
		this.ok.innerHTML = "Ok";
		this.ok.onmousedown = function() {
			this.style.background = "linear-gradient(to bottom, #ccc, #fff)";
		}
		this.ok.onmouseup = function() {
			this.style.background = "linear-gradient(to top, #ccc, #fff)";
		}
		this.ok.addEventListener("click",this.close);
		this.footer.appendChild(this.ok);
		this.msg.appendChild(this.footer);

		this.overlay = document.createElement('div');
		this.overlay.style.cssText = "\
		position: absolute;\
		top: 0;\
		left: 0;\
		width: 100%;\
		height: 100%;\
		background-color: #000;\
		opacity: 0.5;\
		z-index: 3500;"
		this.overlay.onclick = this.close;
		document.body.appendChild(this.overlay);
	}
})();