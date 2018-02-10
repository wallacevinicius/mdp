$('body').on('submit', '#form', function(e){
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
				$("#param").append("<option>Select</option>");
			} else if (data.classes) {
				$("#param").html("");
				$.each(data.classes, function() {
					$("#param").append("<option>"+this+"</option>");
				});
			}
		}
	});
});
$('input[type=file]').change(function(){
	var a = $(this),
		b = a.val(),
		c = b.substr(b.lastIndexOf('\\') + 1);
	a.next().next().html(c);
	$('#form').submit();
});
