$(function() {
    /**
     * Called whenever a level is selected from the drop-down level selection menu.
     */
    $(".selectMenu").change(function () {
	    var $this = $(this);

	    // Select here doesn't select anything obviously.
	    var val = $this.val();
	    if (val == -1)
		    return;

	    update(val);
    });

    $("#uniqueCheck").change(function() {
        update($(".selectMenu").val());
    });

    function update(val) {
        var menu = $(".levelDataMenu");
        menu.html("<h4>Loading...</h4>");

	    var unique = $("#uniqueCheck").is(":checked");

        $.ajax({
            method: "POST",
            url: (unique ? "./uniqueTimes.php" : "./levelInfo.php"),
            dataType: "json",
            data: { levelID: val }
        }).done(function(data) {
            var cols = data.cols;
            var scores = data.scores;

            //Create a table
            var table = $("<table></table>").addClass("table table-striped");
            var tbody = $("<tbody></tbody>").appendTo(table);

            //Create the header
            var header = $("<tr></tr>").appendTo(tbody);
            $.each(cols, function(index, col) {
                $("<th>" + col + "</th>").appendTo(header);
            });

            //Create all the rows
            $.each(scores, function(index, row) {
                var tr = $("<tr></tr>").appendTo(tbody);
                $.each(row, function(index, col) {
                    $("<td>" + (col == null ? "" : col) + "</td>").appendTo(tr);
                });
            });

            //Update the DOM
            menu.empty();
            menu.append(table);
        });
    }
});