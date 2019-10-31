
/** Обработка данных контекстного меню */
function genericOnClick(info, tab) {
	/* Отладочный код
	alert(JSON.stringify(info.frameUrl));
	console.log("item " + info.menuItemId + " was clicked");
	console.log("info: " + JSON.stringify(info));
	console.log("tab: " + JSON.stringify(tab));
	if(info.frameUrl == "https://otrs.umisoft.ru/otrs/index.pl?")
		alert(info.selectionText);
	*/
	var selectnum = Number.parseInt(info.selectionText);
	var selectlen = info.selectionText.length;
	var selectfind = "";
	if (selectnum) {
		if (selectlen < 16)
			selectfind = '*' + selectnum;
		else
			selectfind = info.selectionText;
		ajax(selectfind,function(res) {
			if (res) {
				if (elem = res.querySelector("input.Checkbox[name='TicketID']"))
					chrome.tabs.create({url:'http://otrs.umisoft.ru/otrs/index.pl?Action=AgentTicketZoom;TicketID='+elem.value});
			}
		});
		//
	}
}
/** Отправка ajax запроса */
function ajax(findnumber,func) {
	if (ChallengeToken) {
		var xhr = new XMLHttpRequest;
		xhr.open("POST", "http://otrs.umisoft.ru/otrs/index.pl", true);
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		xhr.onreadystatechange = function() { 
				if(xhr.readyState == 4) func(xhr.response);
			};
		xhr.responseType = "document";
		xhr.send('ChallengeToken='+ChallengeToken+'&Action=AgentTicketSearch&Subaction=Search&TicketNumber='+findnumber+'&AttributeOrig=Fulltext');	
	}
}

// Добавление контекстного меню Open TicketNumber
var ChallengeToken = "";
var contexts = ["page","selection","link","editable","image","video",
                "audio"];
var context = "selection";
// var title = "Test '" + context + "' menu item";
var title = "Open TicketNumber";
var id = chrome.contextMenus.create({"title": title, "contexts":[context],
								   "onclick": genericOnClick});
console.log("'" + context + "' item:" + id);

/** Обработка глобальных сообщений */
chrome.runtime.onMessage.addListener(
	function(request, sender, sendResponse) {
		/*console.log(sender.tab ?
					"from a content script:" + sender.tab.url :
					"from the extension");*/
		if (request.msg) {
			if (request.type == "alert") {
				alert(request.msg);
				sendResponse({answer: "done"});
			}
			else if (request.type == "answer") {
				sendResponse({answer: confirm(request.msg)});
			}
		} else if (request.info) {
			ChallengeToken = request.info;
		}
		return true;
	}
);