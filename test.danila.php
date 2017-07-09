<html>
    <head>
        <script language="JavaScript" src="/scripts/jquery-2.1.4.min.js"></script>
        <script language="JavaScript">
            var data = [{ID: 3, Title: "Lalka", Text: "tabletka"}, {ID: 3, Title: "Lalka", Text: "tabletka"}];
            $(document).ready(function() {
                $("body").append($("<div>").attr({"data-id": data.ID})
                        .append($("<div>").addClass("title").text(data.Title))
                        .append($("<div>").addClass("text").text(data.Text))
                );
            });
        </script>
    </head>
    <body>

    </body>
</html>