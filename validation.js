function validateForm() {
	var textAreaLength = document.getElementById("text").value.length; 
	//for some strange reason, if I enter nothing into text box, default value is 1... 
	if (textAreaLength == 1) {
		alert("Please enter something into the text field!"); 
		return false; 
	}
	document.getElementById('text_form').submit();
	return true; 
}