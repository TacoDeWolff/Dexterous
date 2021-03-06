<div class="dex-popup-wrapper">
	<div class="popup">
		<div id="assets">
			<h2><?php echo __('Assets'); ?></h2>
			<form id="upload" method="post" action="/<?php echo $_['base_url']; ?>api/core/assets/" enctype="multipart/form-data">
				<input type="hidden" name="dir" value="">
				<input type="hidden" name="max_width" value="100">
				<div id="drop">
					<span><?php echo __('Drop here'); ?></span><br>
					<a class="inline-button"><i class="fa fa-search"></i>&ensp;<?php echo __('Browse'); ?></a>
					<input type="file" name="upload" multiple>
					<div id="knob">
						<div id="big-knob"><input type="text" value="0" data-width="64" data-height="64" data-thickness=".23" data-fgColor="#477725" data-readOnly="1" data-displayInput=false data-bgColor="#FFFFFF"></div>
						<div id="small-knob"><input type="text" value="0" data-width="48" data-height="48" data-thickness=".25" data-fgColor="#477725" data-readOnly="1" data-displayInput=false data-bgColor="#F0F0F0"></div>
					</div>
				</div>
				<ul></ul>
			</form>

			<div id="breadcrumbs"><a href="#" data-dir=""><?php echo __('Assets'); ?></a></div>
			<ul id="directories-assets" class="table">
				<li>
					<div><?php echo __('File name'); ?></div>
					<div><?php echo __('Size'); ?></div>
					<div></div>
				</li>
				<li id="load_status_directories_assets" class="dex-api load-status">
					<div class="working"><i class="fa fa-cog fa-spin"></i></div>
					<div class="error"><i class="fa fa-times"></i></div>
					<div class="empty"><?php echo __('empty'); ?></div>
				</li>
			</ul>
		</div>
		<div>
			<a href="#" class="back button left"><i class="fa fa-chevron-left"></i>&ensp;<?php echo __('Back'); ?></a>
			<h2><?php echo __('Asset properties'); ?></h2>
			<form>
				<input id="insert_url" type="hidden">
				<p><label><?php echo __('Text'); ?></label><input id="insert_text" type="text" data-tooltip="<?php echo __('Clickable text'); ?>"></p>
				<p><label><?php echo __('Description'); ?></label><input id="insert_title" type="text" data-tooltip="<?php echo __('Shown when hovering'); ?>"></p>
				<input id="insert_submit" type="hidden">
				<a href="#" class="insert button"><i class="fa fa-plus-square"></i>&ensp;<?php echo __('Insert'); ?></a>
			</form>
		</div>
	</div>
</div>

<script id="directory_item" type="text/x-dot-template">
	<li data-name="{{=it.name}}" class="directory">
		<div><img src="/<?php echo $_['base_url']; ?>res/core/images/icons/{{=it.icon}}" width="16" height="16"><a href="#" data-dir="{{=it.dir}}">{{=it.name}}</a></div>
		<div>-</div>
		<div></div>
	</li>
</script>

<script id="asset_item" type="text/x-dot-template">
	<li  data-title="{{=it.title}}" data-url="/<?php echo $_['base_url']; ?>res/{{=it.url}}" class="asset">
		<div><img src="/<?php echo $_['base_url']; ?>res/core/images/icons/{{=it.icon}}" width="16" height="16">{{=it.title}}</div>
		<div>{{=it.size}}</div>
		<div></div>
	</li>
</script>

<script type="text/javascript">
	// preliminaries
	var breadcrumbs = $('#breadcrumbs');
	var directories_assets = $('#directories-assets');

	var directory_item = doT.template($('#directory_item').text());
	var asset_item = doT.template($('#asset_item').text());

	// loading initial data
	function loadDir(dir) {
		directories_assets.find('li:not(:first):not(.load-status)').slideUp(100, function () { $(this).remove(); });

		setTimeout(function () {
			api('/' + base_url + 'api/core/assets/', {
				action: 'get_breadcrumbs',
				dir: dir
			}, function (data) {
				breadcrumbs.find('*:not(a:first)').remove();
				var items = '';
				$.each(data['breadcrumbs'], function (i) {
					items += '<span>&gt;</span><a href="#" data-dir="' + this.dir + '">' + this.name + '</a>';
				});
				breadcrumbs.append(items);
			});

			apiLoadStatusWorking($('#load_status_directories_assets'));
			$('#load_status_directories_assets').show();
			api('/' + base_url + 'api/core/assets/', {
				action: 'get_directories_assets',
				dir: dir
			}, function (data) {
				if (!data['directories'].length && !data['assets'].length) {
					apiLoadStatusEmpty($('#load_status_directories_assets'));
					return;
				}
				$('#load_status_directories_assets').hide();

				var items = '';
				$.each(data['directories'], function () {
					items += directory_item(this);
				});
				$.each(data['assets'], function () {
					items += asset_item(this);
				});
				$(items).hide().appendTo(directories_assets).slideDown(100, function () {
					parent.$.fancybox.update();
				});
			}, function () {
				apiLoadStatusError($('#load_status_directories_assets'));
			});
		}, 100);
	}
	loadDir('');

	// click events on directories, assets and images
	breadcrumbs.on('click', 'a', function () {
		loadDir($(this).attr('data-dir'));
	});

	directories_assets.on('click', '.directory', function (e) {
		e.stopPropagation();
		$(this).find('a').click();
	});

	directories_assets.on('click', '.directory a', function () {
		e.stopPropagation();
		loadDir($(this).attr('data-dir'));
	});

	var popup = $('.popup');
	$('a.back').on('click', function () {
		switchBackPopupFrame(popup);
	});

	directories_assets.on('click', '.asset', function () {
		$('#insert_title').val($(this).attr('data-title'));
		$('#insert_url').val($(this).attr('data-url'));
		if (!$('#insert_text').val().length) {
			$('#insert_text').val($(this).attr('data-title'));
		}
		switchPopupFrame(popup);
	});

	$('html').on('keyup', function (e) {
		if (e.keyCode === 13 && currentPopupFrame === 1) {
			popup.find('a.insert').click();
		}
	});

	popup.on('click', 'a.insert', function () {
		$('#insert_submit').val('1');
		parent.$.fancybox.close();
	});
</script>