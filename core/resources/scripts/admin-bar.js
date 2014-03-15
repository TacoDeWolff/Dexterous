$(function() {
	$('#publish-site').click(function() {
		$.fancybox.open({
			content: '<textarea id="console" readonly></textarea>'
		});

		apiStatusWorking('Publishing site...');
		apiUpdateConsole($('#console'));
		api('/' + base_url + 'api/core/publish-site/', {
		}, function(data) {
			apiStopConsole();
			apiStatusSuccess('Published site');
		}, function() {
			apiStopConsole();
			apiStatusError('Publishing site failed');
			return false;
		});
	});

	$('#edit').on('click', 'a', function(e) {
		location.hash = "edit"
		$('#edit').fadeOut('fast', function() {
			$('article').attr('contenteditable', 'true');
			grande.bind(document.querySelectorAll("article"));
			initializeUpload('[contenteditable="true"]');
			$('#save').fadeIn('fast');
		});
		return false;
	});

	initializeUploadDone(function(data) {
		if (!data['file'].is_image)
		{
			var item = asset_item(data['file']);
			if (directories_assets.find('li.asset').length)
				addAlphabetically(directories_assets.find('li.asset'), item, data['file']['name']);
			else
				$(item).hide().insertAfter(directories_assets.find('.directory:last')).slideDown('fast');
		}
		else
		{
			var item = image_item(data['file']);
			if (images.find('li').length)
				addAlphabetically(images.find('li'), item, data['file']['name']);
			else
				$(item).hide().appendTo(images).slideDown('fast');
		}
	});

	$('#save').on('click', 'a', function() {
		$('#save').fadeOut('fast', function () {
			$('#edit').fadeIn('fast');
		});
		$('article').attr('contenteditable', 'false');
		apiStatusWorking('Saving page...');
		var item = $(this);
		api('/' + base_url + 'api/template/static/index/', {
			action: 'save_page',
			link_id: link_id,
			content: $('article').html()
		}, function() {
			apiStatusSuccess('Saved page <span data-time></span>');
		}, function() {
			apiStatusError('Saving page failed');
		});
	});

	$('article').on('keydown', function() {
		apiStatusClear();
	});

	$('#log-out').click(function() {
		apiStatusWorking('Logging out...');
		api('/' + base_url + 'api/core/users/', {
			'action': 'logout'
		}, function(data) {
			$('#api_fatal').fadeOut().remove();
			$('#api_status').fadeOut().remove();
			$('#admin-bar').slideUp(function() {
				this.remove();
				$('#edit, #save').remove();
			});
			$('article').attr('contenteditable', 'false');

			$('body').animate({
				'padding-top': '0'
			});
		}, function() {
			apiStatusError('Logging out failed');
			return false;
		});
	});

	if (window.location.hash === "#edit") {
		$('#edit a').trigger('click');
	}
});
