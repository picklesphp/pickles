// @todo Document this file better, it's a mess.

var request = null;

function getForm(form) {
	var params = 'ajax=true';
	var count  = form.elements.length;

	for (var i = 0; i < count; i++) {
		element = form.elements[i];

		switch (element.type) {
			case 'hidden':
			case 'password':
			case 'text':
			case 'textarea':
				// Checks if the field is visible
				// if (element.name == 'referred_by') {
				// 	alert(element.name);
				// 	alert(element.style.display);
				// }
				if (element.style.display != 'none') {
					// Checks if it's required
					if (element.title == 'required' && trim(element.value) == '') {
						alert('Error: The ' + element.name.replace('_', ' ') + ' field is required.');
						element.focus();
						return false;
					}
					// If the field is named email, check it's validity
					else if (element.name == 'email') {
						if (element.value.match(/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i) == null) {
							alert('Error: The email address entered is not valid.');
							element.focus();
							return false;
						}
					}

					params += '&' + element.name + '=' + encodeURI(element.value);
				}
				break;

			case 'checkbox':
			case 'radio':
				if (element.checked) {
					params += '&' + element.name + '=' + encodeURI(element.value);
				}
				break;

			case 'select-one':
				params += '&' + element.name + "=" +  element.options[element.selectedIndex].value;
				break;
		}
	}

	return params;
}

function createRequest() {
    try {
        request = new XMLHttpRequest();
    } catch (trymicrosoft) {
        try {
            request = new ActiveXObject("Msxml12.XMLHTTP");
        } catch (othermicrosoft) {
            try {
                request = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (failed) {
                request = null;
            }
        }
    }

    if (request == null) {
        alert("Error creating request object!");
    }
}

function ajaxRequest(htmlElement, customHandler, placement, url) {
	var params        = '';
	var return_status = '';
	var customHandler = (customHandler == null) ? null     : customHandler;
	var placement     = (placement     == null) ? 'before' : placement;
	var url           = (url           == null) ? null     : url;
	var return_json   = false;

	// If .value is undefined, assumes it's a FORM element
	if (typeof htmlElement.value == 'undefined') {

		if (typeof htmlElement == 'string') {
			method      = 'POST';
			action      = htmlElement;
			return_json = true;
		}
		else {
			params = getForm(htmlElement);
			method = htmlElement.method;
			action = htmlElement.action;

			var formElement = htmlElement;
		}
	}
	else {
		if (htmlElement.id != '') {
			var variable = htmlElement.id;
		}
		else if (htmlElement.name != '') {
			var variable = htmlElement.name;
		}
		else {
			var variable = 'id';
		}

		params = variable + '=' + htmlElement.value;
		method = 'POST';
		action = url;
	
		// Loops up through the parents until it reaches the form element
		while (htmlElement.nodeName != 'FORM') {
			htmlElement = htmlElement.parentNode;
		}

		var formElement = htmlElement;
	}

	// @todo need to decide on a way to request a page w/o any params.
	// if (true) {
	if (params) {
		createRequest();
		request.open(method, action, true);
		
		request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		request.setRequestHeader("Content-length", params.length);
		request.setRequestHeader("Connection", "close");

		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200) {
				var responseElement  = document.createElement('div');
				responseElement.id   = 'ajaxResponse';

				if (request.responseText.substring(0, 1) == '{' && request.responseText.substring(request.responseText.length - 1) == '}') {
					var responseObject = eval( "(" + request.responseText + ")" );

					if (document.getElementById(responseElement.id) != null) {
						//formElement.removeChild(document.getElementById(responseElement.id));
						// @todo
						document.getElementById('ajaxResponse').parentNode.removeChild(document.getElementById('ajaxResponse'));
					}

					if (customHandler) {
						responseElement = window[customHandler](responseObject, responseElement);
					}
					else {
						if (placement == 'inside') {
							if (responseObject.type == 'error') {
								htmlElement.style.backgroundColor = '#C88';
								htmlElement.style.borderColor = '#600';
								htmlElement.style.color = '#600';
								htmlElement.style.fontWeight = 'bold';
								htmlElement.value = responseObject.message;
							}
						}
						else {
							var responseMessage = document.createTextNode(responseObject.message);
							responseElement.className = responseObject.type;
							responseElement.appendChild(responseMessage);
						}
					}
				}
				else {
					responseElement.innerHTML = request.responseText;
				}
			
				if (document.getElementById(responseElement.id) != null) {
					formElement.removeChild(document.getElementById(responseElement.id));
				}

				if (responseElement != false) {
					formElement.insertBefore(responseElement, (placement == 'before') ? formElement.firstChild : formElement.lastChild);
				}

				if (typeof responseObject.type != 'undefined') {
					if (responseObject.type == 'success' && placement == 'inside') {
						formElement.submit();
					}
				}
			}
		}

		request.send(params);
	}
	// @todo need to remember why this is here.
	else if (customHandler) {
		responseElement = window[customHandler]();
		return false;
	}
}

function trim(str) {
	str = str.replace(/^\s+/, '');
	for (var i = str.length - 1; i >= 0; i--) {
		if (/\S/.test(str.charAt(i))) {
			str = str.substr(0, i + 1);
			break;
		}
	}

	return str;
}
