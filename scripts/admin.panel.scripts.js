$(document).ready(function() {
	//var updater = setTimeout();
	//$("#queuerList")
	doUpdate();

	$("#formEnqueue").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.enqueue";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				doUpdate();
			}
		});		
	});
	$("#formCall").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize();
		data["Act"] = "queue.callnext";
		$.ajax({url: '/handler.php', method: "Post", data: data,
			success: function(data) {
				doUpdate();
			}
		});		
	});
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
	$.ajax({url: '/handler.php', method: "Post", data: {"Act": "queue.reenqueue" ,"ID": $(this).parents("tr").attr("data-id")},
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

function doUpdate() {
	$.ajax({
		url: '/handler.php',
		method: "Post",
		data: {"Act": "queue.get" ,"QID": $("#queuerList").attr("data-qid")},
		success: function(data) {
			displayQueuers(data);
		}
	});
	//$("#queuerList")
}

function displayQueuers(data) {
	$("#queuerList").empty().append($("<table>"));
	var qtbl = $("#queuerList table");
	qtbl.append($("<tr>")
		.append($("<th>").text("ID"))
		.append($("<th>").text("Name"))
		.append($("<th>").text("Enqueued"))
		.append($("<th>").text("Called"))
		.append($("<th>").text("Dequeued"))
		.append($("<th>").text("Action"))
	);
	var statuses = {"Waiting": "waiting", "Called" : "called", "Handling": "dequeued", "Out": "out", "Purged": "purged"};
	for (var i in data.Queue) {
		qtbl.append($("<tr>").attr({"data-id": data.Queue[i].ID}).addClass(statuses[data.Queue[i].Status])
			.append($("<td>").text(data.Queue[i].ID))
			.append($("<td>").text(data.Queue[i].Name))
			.append($("<td>").text(getDate(data.Queue[i].Enqueued)))
			.append($("<td>").text(getDate(data.Queue[i].Called)))
			.append($("<td>").text(getDate(data.Queue[i].Dequeued)))
			.append($("<td>").append(function() {
				var out = [];
				switch (data.Queue[i].Status) {
					case "Waiting":
						out.push($("<button>").addClass("action call").text("Call"));
						out.push($("<button>").addClass("action dequeue").text("Dequeue"));
					break;
					case "Called":
						out.push($("<button>").addClass("action dequeue").text("Dequeue"));
						out.push($("<button>").addClass("action reenqueue").text("Reenqueue"));
					break;
					case "Handling":
						out.push($("<button>").addClass("action reenqueue").text("Reenqueue"));
						out.push($("<button>").addClass("action complete").text("Complete"));
					break;
					case "Out":
						out.push($("<button>").addClass("action reenqueue").text("Reenqueue"));
					break;
				}
				return out;
			}))
		);
	}
}

function getDate(timestamp) {
	if (!timestamp) return;
	var d = new Date(timestamp*1000);
	//return d.toString();
	return d.getDate()+"."+(d.getMonth() + 1)+"."+d.getFullYear()+" "+d.getHours()+":"+d.getMinutes()+":"+d.getSeconds();
}



function enqueue() {}
function dequeue() {}
function call() {}
function login() {}
function logout() {}