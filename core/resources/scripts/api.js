function api(url, data, success, error) {
	if (typeof sessionTimeout !== 'undefined') {
		clearTimeout(sessionTimeout);
		sessionTimeout = setTimeout(function () {
			adminBarLogOut();
		}, session_time * 1000);
	}

	if (!url)
		apiFatal('No API URL set');
	else
		$.ajax({
			type: (data == null ? 'GET' : 'POST'),
			url: url,
			data: data,
			dataType: 'json',
			success: function (data) {
				if (typeof data['_error'] !== 'undefined') {
					if (typeof error !== 'undefined' && error && error(data) === false) {
						return;
					}

					apiFatal(data['_error'].join('<br>'));
				} else if (typeof success !== 'undefined' && success)	{
					success(data);
					if (typeof applyTooltips !== 'undefined') {
						applyTooltips();
					}
				}
			},
			error: function (data) {
				if (typeof error !== 'undefined' && error && error(data) === false) {
					return;
				}

				if (typeof data['responseJSON'] !== 'undefined' && typeof data['responseJSON']['_error'] !== 'undefined') { // PHP error but still handled by API
					apiFatal(data['responseJSON']['_error'].join('<br>'));
				} else if (typeof data['responseText'] !== 'undefined') { // Non-JSON response
					apiFatal(escapeHtml(data['responseText']));
				} else if (typeof data['statusText'] !== 'undefined') { // Some XHR thing went wrong
					apiFatal(escapeHtml(data['statusText']));
				} else { // ...shrugs
					apiFatal(escapeHtml(data));
				}
			}
		});
}

function apiFatal(message) {
	if (!$('div.fancybox-wrap').length) {
		$.fancybox.open({
			content: '<h2>Error</h2>' + message,
			autoScale: false,
			beforeShow: function () {
				this.skin.addClass('dex-api-error');
			},
			overlay: {
				closeClick: true,
				locked: false
			}
		});
	}
}

function apiStatusClear() {
	$('.dex-api .status div').stop(true).hide();
}

function apiStatusFade() {
	$('.dex-api .status div').stop(true).fadeOut(100);
}

function apiStatusWorking(message) {
	apiStatusClear();
	$('.dex-api .status div.working').delay(800).fadeIn(100);
	if (typeof message !== 'undefined') {
		$('.dex-api .status div.working span').delay(800).show().html(message);
	} else {
		$('.dex-api .status div.working span').hide();
	}
}

function apiStatusSuccess(message) {
	apiStatusClear();
	$('.dex-api .status div.success').fadeIn(100);
	if (typeof message !== 'undefined') {
		$('.dex-api .status div.success span').show().html(message);
		//setTimeout(apiStatusFade, 5000);
	} else {
		$('.dex-api .status div.success span').hide();
	}
}

function apiStatusError(message) {
	apiStatusClear();
	$('.dex-api .status div.error').fadeIn(100);
	if (typeof message !== 'undefined') {
		$('.dex-api .status div.error span').show().html(message);
	} else {
		$('.dex-api .status div.error span').hide();
	}
}

function apiLoadStatusClear(load) {
	load.find('div').stop(true).hide();
}

function apiLoadStatusWorking(load) {
	apiLoadStatusClear(load);
	load.find('div.working').fadeIn(100);
}

function apiLoadStatusSuccess(load) {
	load.remove();
}

function apiLoadStatusEmpty(load) {
	apiLoadStatusClear(load);
	load.find('div.empty').fadeIn(100);
}

function apiLoadStatusError(load) {
	apiLoadStatusClear(load);
	load.find('div.error').fadeIn(100);
}

var apiUpdateConsoleTimeout;
function apiUpdateConsole(dest) {
	apiUpdateConsoleTimeout = setTimeout(function () {
		api('/' + base_url + 'api/core/console/', {
			action: 'console'
		}, function (data) {
			if (typeof data['status'] !== 'undefined')
			{
				dest.html(data['status']);
				dest[0].scrollTop = dest[0].scrollHeight;
			}
			apiUpdateConsole(dest);
		});
	}, 100);
}

function apiStopConsole() {
	setTimeout(function () {
		clearTimeout(apiUpdateConsoleTimeout);
	}, 1000);
}