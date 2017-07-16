$(document).ready(function() {
	//var updater = setTimeout();
	//$("#queuerList")
	doUpdate();
	
	var timeout = 10000;
	var updater = setInterval(doUpdate, timeout);
	
	$("#formEnqueue").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.enqueue";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				//console.log(data);
				doUpdate();
				$("#formEnqueue input[name=Name]").val("");
			}
		});	
		return false;
	});
	$("#formCall").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.callnext";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				//console.log(data);
				doUpdate();
			}
		});	
		return false;
	});
	$("#formBreak").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.break";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				//console.log(data);				
				if (data.Status == "OK") {
					_break = serverTime + data.Minutes * 60;
					doUpdate();
				} else if (data.Status == "Fail") {
					alert(data.Error);
				}				
			}
		});
		return false;
	});
	$("#formUnbreak").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.unbreak";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				//console.log(data);
				_break = 0;
				doUpdate();								
			}
		});
		return false;
	});
	
	var breakOk = false;
	var breakChecker = setInterval(function(){
		if (_break <= serverTime && breakOk) {
			$("#breakBlock").show();$("#unbreakBlock,#breakTimeBox").hide();
			breakOk = false;
		} else if (_break > serverTime && !breakOk){
			$("#breakTime").text("");
			$("#unbreakBlock,#breakTimeBox").show();$("#breakBlock").hide();
			breakOk = true;
		}else if (_break > serverTime){
			$("#breakTime").text(prettyTime(_break - serverTime));
		}
	}, 1000);
	
	function prettyTime(time) {
        time = parseInt(time) | 0;
        var d = parseInt(time / 86400);
        var h = parseInt((time - d*86400) / 3600);
        var m = parseInt((time - d*86400 - h*3600) / 60);
        var s = parseInt(time % 60);
        return (d > 0 ? d+" "+declOfNum(d, ["diena", "dienas"])+" " : "")+(h > 0 ? h+" "+declOfNum(h, ["stunda", "stundas"])+" " : "")+(m > 0 ? m+" "+declOfNum(m, ["minūte", "minūtes"])+" " : "")+(((d != 0 || h != 0 || m != 0) && s > 0) > 0 ? s+" "+declOfNum(s, ["sekunde", "sekundes"])+" " : "")+((d == 0 && h == 0 && m == 0 && s > 0) ? "Mazāk kā minūte" : "");
    }
	
    function declOfNum(n, s) {
        return (n%10 == 1 && n%100 !=11) ? s[0] : s[1];
    }
	
	
});

$(document).on("click", "button.call", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.callid" ,"ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});
$(document).on("click", "button.dequeue", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.dequeue" ,"ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});
$(document).on("click", "button.reenqueue", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.reenqueue", "ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});
$(document).on("click", "button.complete", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.complete" ,"ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});
$(document).on("click", "button.purge", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.purge" , "ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});
$(document).on("click", "button.delete", function() {
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.delete" , "ID": $(this).parents("tr").attr("data-id")},
		success: function(data) {
			doUpdate();
		}
	});
});

function doUpdate() {
	$.ajax({
		url: '/handler.php',
		method: "Post",
		data: {"Act": "queue.get" ,"QID": $("#queuerList").attr("data-qid")},
		success: function(data) {
			_break = data.Break;
			displayQueuers(data);			
		}
	});
	//$("#queuerList")
}


	function time_update_txt(timestamp){
		if (!timestamp) return;		
		var d = new Date(serverTime*1000);
		var l = {
			'Befor':'Pirms',
			'Now':'Tikko',
			'sec':'sec',
			'min':'min',
		};
		var returnTime = "Vairāk par 1 stundu";
		if(timestamp > serverTime - 3600){			
			var sec = serverTime - timestamp;	
			if (sec <= 0) {
				returnTime = l['Now'];
			} else if (sec < 60){					
				returnTime = l['sec'];
				returnTime = l['Befor'] + " " + sec + " " + returnTime;						
			} else {
				var min = Math.floor(sec/60);				
				returnTime = l['min'];
				returnTime = l['Befor'] + " " + min + " " + returnTime;
			}			
		} else if(timestamp > serverTime - 3600*10){
			var returnTime = "";
		}	
		return returnTime;
	}
	var time_update_id = {};
	function time_update_set(id,t){
		if(typeof time_update_id[id] != 'undefined' && time_update_id[id].length != 0){
			clearInterval(time_update_id[id]);
		}
		time_update_id[id] = setInterval(function(){			
			if(typeof $("#"+id).html() != 'undefined'){
				$("#"+id).html("<br>").append(time_update_txt(t));
			}else{
				clearInterval(time_update_id[id]);
			}
		},1000);		
	}

function displayQueuers(data) {
	$("#queuerList").empty().append($("<table>"));
	var qtbl = $("#queuerList table");
	qtbl.append($("<tr>")
		.append($("<th>").text("ID"))
		.append($("<th>").text("Vārds un uzvārds"))
		.append($("<th>").text("Pieteicās"))
		.append($("<th>").text("Izsauca"))
		.append($("<th>").text("Noslēdzās"))
		.append(function() { if(logged && (_break <= serverTime || (_break > serverTime && !userStatus))) { return $("<th>").text("Darbības"); } } )
	);
	var statuses = {"Waiting": "waiting", "Called" : "called", "Handling": "dequeued", "Out": "out", "Purged": "purged"};
	for (var i in data.Queue) {
		qtbl.append($("<tr>").attr({"data-id": data.Queue[i].ID}).addClass(statuses[data.Queue[i].Status])
			.append($("<td>").text(data.Queue[i].ID))
			.append($("<td>").text(data.Queue[i].Name))
			.append($("<td>").html(getDate(data.Queue[i].Enqueued)).append(function(){if(data.Queue[i].Status == 'Waiting'){ return $("<span>").html("<br>").append(time_update_txt(data.Queue[i].Enqueued)).attr("id","time-id-"+data.Queue[i].ID);}}))
			.append($("<td>").html(getDate(data.Queue[i].Called)).append(function(){if(data.Queue[i].Status == 'Called'){ return $("<span>").html("<br>").append(time_update_txt(data.Queue[i].Called)).attr("id","time-id-"+data.Queue[i].ID);}}))
			.append($("<td>").html(getDate(data.Queue[i].Dequeued)).append(function(){if(data.Queue[i].Status != 'Waiting' && data.Queue[i].Status != 'Called'){ return $("<span>").html("<br>").append(time_update_txt(data.Queue[i].Dequeued)).attr("id","time-id-"+data.Queue[i].ID);}}))
			.append(function() {
				if(logged && (_break <= serverTime || (_break > serverTime && !userStatus))) {
					return $("<td>").append(function() {
						var out = [];
						switch (data.Queue[i].Status) {
							case "Waiting":
								if (userStatus) {
									out.push($("<button>").addClass("action call").text("Izsaukt"));
									//out.push($("<button>").addClass("action dequeue").text("Pabeigt rindu"));
								} else if (data.Queue[i].Enqueued > serverTime - 60){									
									out.push($("<button>").addClass("action delete").text("Izdzēst"));
								}
							break;
							case "Called":
								if (userStatus) {
									out.push($("<button>").addClass("action dequeue").text("Pabeigt rindu"));
									out.push($("<button>").addClass("action reenqueue").text("Atlikt rindā"));	
									if (data.Queue[i].Called < serverTime - purgeTimeout) {
										out.push($("<button>").addClass("action purge").text("Izmest"));
									}	
								}
							break;
							case "Handling":
								if (userStatus) {
									out.push($("<button>").addClass("action reenqueue").text("Atlikt rindā"));
								} else {
									out.push($("<button>").addClass("action complete").text("Viss OK"));
								}
							break;
							case "Out":
								//out.push($("<button>").addClass("action reenqueue").text("Atlikt rindā"));
							break;
							case "Purged":
								if (!userStatus) {
									out.push($("<button>").addClass("action reenqueue").text("Atlikt rindā"));
								}
							break;
						}
						return out;
					});		
				}
			})
		);
		if (data.Queue[i].Status == 'Waiting' && data.Queue[i].Enqueued > serverTime - 3600*10) {
			time_update_set("time-id-"+data.Queue[i].ID,data.Queue[i].Enqueued);
		} else if (data.Queue[i].Status == 'Called' && data.Queue[i].Called > serverTime - 3600*10) {
			time_update_set("time-id-"+data.Queue[i].ID,data.Queue[i].Called);
		} else if (data.Queue[i].Dequeued > serverTime - 3600*10) {
			time_update_set("time-id-"+data.Queue[i].ID,data.Queue[i].Dequeued);
		}
	}
}

function getDate(timestamp) {
	if (!timestamp) return;
	var d = new Date(timestamp*1000);
	//return d.toString();
	var dTxt = "";
	
	if(d.getHours() < 10) dTxt += "0";
	dTxt += d.getHours();
	dTxt += ":";
	if(d.getMinutes() < 10) dTxt += "0";
	dTxt += d.getMinutes();
	dTxt += ":";
	if(d.getSeconds() < 10) dTxt += "0";
	dTxt += d.getSeconds();	
	
	dTxt += " ";
	
	if(d.getDate() < 10) dTxt += "0";
	dTxt += d.getDate();
	dTxt += ".";
	if(d.getMonth() < 9) dTxt += "0";
	dTxt += (d.getMonth() + 1);
	//dTxt += ".";
	//dTxt += d.getFullYear();
	
	return dTxt;
}

function enqueue() {}
function dequeue() {}
function call() {}
function login() {}
function logout() {}