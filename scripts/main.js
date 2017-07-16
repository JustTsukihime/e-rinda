/*
*  DC 2015
*/

$(document).ready(function() {
	var timeout = 30000;
	var doUpdate = true;
    var selected = null;
    var avgTime = null | $("#data").attr("data-avgWait");

	update();
	var updater = setInterval(update, timeout);

	/*
    $("#data").on("click", "div", function() {
        $("#data > div").removeClass("selected");
        $(this).addClass("selected");
        selected = $(this).attr("data-id");
        updateFooter();
    });
	*/
	
    function update() {
        // if checkbox

        $.ajax({
            url: "/update.php",
            method: "POST",
            data: {"QID": $("#data").attr("data-id")},
            success: function(data) {
				_break = data.Break;
                displayData(data);				
            }
        });
    }
		
    function displayData(data) {
        avgTime = data.AvgTime;
        var cnt = $("#data");
        cnt.empty();		
        for (var i in data.Queue) {
            cnt.append($("<div>").attr("data-id", data.Queue[i].ID).addClass(data.Queue[i].Status == "Called" ? "next" : "" )
                    .append($("<span>").text((parseInt(i)+1)+"."))
                    .append($("<span>").text(data.Queue[i].Name))
					.append(function(){						
						if( data.Queue[i].Status == "Waiting" ) {
							return $("<span>").addClass("time").html("<br>").append(prettyTime(( ( _break > serverTime ) ? _break - serverTime : 0 ) + avgTime*(parseInt(i)+1)));
						}
					})
            );
        }
        if (selected) {
            $("#data > div[data-id="+selected+"]").addClass("selected");
            updateFooter();
        }
    }

    function updateFooter() {
        //$("footer .time").text("Ja tavs vārds ir zaļš, tu visdrīzāk tiksi iekšā nākamais");
       // $("footer .time").text("Aptuvenais gaidīšanas laiks: "+prettyTime( avgTime*($("#data > div").index($("#data > div[data-id="+selected+"]")) + 1) ));
    }

	function prettyTime(time) {
        time = parseInt(time) | 0;
        var d = parseInt(time / 86400);
        var h = parseInt((time - d*86400) / 3600);
        var m = parseInt((time - d*86400 - h*3600) / 60);
        var s = parseInt(time % 60);
        return (d > 0 ? d+" "+declOfNum(d, ["diena", "dienas"])+" " : "")+(h > 0 ? h+" "+declOfNum(h, ["stunda", "stundas"])+" " : "")+(m > 0 ? m+" "+declOfNum(m, ["minūte", "minūtes"])+" " : "")+((d == 0 && h == 0 && m == 0 && s > 0) ? "Mazāk kā minūte" : "");
    }

    function declOfNum(n, s) {
        return (n%10 == 1 && n%100 !=11) ? s[0] : s[1];
    }
});