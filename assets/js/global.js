$('body').on('submit', '#formUpload', function(e){
	e.preventDefault();
	var formData = new FormData($(this)[0]);
	$.ajax({
		url : "./assets/scripts/upload.php",
		type: "POST",
		processData: false,
		contentType: false,
		data: formData,
		success: function(data){
			if (data.error) {
				console.info(data.error);

				if ( $("#phenotypicData").val() ) {
					$("#param").append("<option>Select</option>");
				}

				if ( $("#pathwaysGMT").val() ) {
					$("#param2").append("<option>Select</option>");
				}

				console.log( data );

			} else if (data.classes) {

				console.log( data );

				if ( $("#phenotypicData").val() ) {
					$("#param").html("");

					$.each(data.classes, function() {
						$("#param").append("<option>"+this+"</option>");
					});
				}

				if ( $("#pathwaysGMT").val() ) {
					$("#param2").html("");

					$.each(data.classes, function() {
						$("#param2").append("<option>"+this+"</option>");
					});
				}
			}
		}
	});
});

$('input[type=file]').change(function(){
	var a = $(this),
		b = a.val(),
		c = b.substr(b.lastIndexOf('\\') + 1);
	a.next().next().html(c);
	$('#formUpload').submit();
});

/* 

// POST - CREATE PLOT

$('body').on('submit', '#formData', function(e){
    e.preventDefault();    
	var formData = new FormData($(this)[0]);

    $.ajax({
        url : "./assets/scripts/plot.php",
        type: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
        	// ADICIONAR VISUALIZACAO DOS RELATORIOS
            alert("Success");
    		$("html").empty();
    		$("html").append(data)
        }
    });
});
*/