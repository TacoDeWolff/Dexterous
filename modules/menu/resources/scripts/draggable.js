var Draggable = function (ul) {
	var self = this;

	this.ul = $(ul);
	this.draggee = false;
	this.draggee_x_start = 0;
	this.draggee_y_offset = 0;
	this.placeholder = false;

	this.save = new Save(this.ul);

	this.drag = function (e) {
		if (self.draggee !== false) {
			var elements = self.ul.find('li:not(:first)').filter(function () {
				return $(this).css('position') == 'static';
			});

			// restrain Y movement within the list
			var top = e.pageY - self.draggee_y_offset;
			var min_top = elements.first().offset().top + 1;
			var max_top = elements.last().offset().top + 1;
			if (top < min_top) {
				top = min_top;
			} else if (top > max_top) {
				top = max_top;
			}

			// placement
			var places = elements.filter(function () {
				 return !$(this).hasClass('placeholder');
			});

			if (places.length) {
				var y = top + 0.5 * places.first().outerHeight();
				places.each(function () {
					var place = $(this);
					if (y >= place.offset().top && y < place.offset().top + place.outerHeight()) {
						if (top < self.placeholder.offset().top) {
							self.placeholder.insertBefore(place);
						} else {
							self.placeholder.insertAfter(place);
						}
						return false;
					}
				});
			}

			// X movement determines level of the item
			var level = 0;
			var previous = false;
			elements.each(function () {
				var element = $(this);
				if (element.hasClass('placeholder')) {
					return false;
				}
				previous = element;
			});

			if (previous !== false && typeof previous.attr('data-level') !== 'undefined') {
				level = Math.floor((e.pageX - self.draggee_x_start + 20.0) / 40.0);
				var previous_level = previous.attr('data-level');
				if (level >= previous_level + 2) {
					level = previous_level + 1;
				}
			}

			self.draggee.find('.fa-long-arrow-right').hide();
			for (var i = 0; i < level; i++) {
				self.draggee.find('.fa-long-arrow-right').eq(i).show().css('display', 'inline-block');
			}

			// apply CSS
			self.draggee.css('top', top + 'px');
			self.draggee.attr('data-level', level);
		}  else {
			$(document).unbind('mousemove', self.drag);
		}
	};

	this.ul.on('mousedown', '.fa-eye', function (e) {
		e.preventDefault();
		apiStatusClear();

		var li = $(this).closest('li');
		var parent = self.getParent(li);
		if (parent && parent.hasClass('unused') && li.hasClass('unused'))
			return;

		li.toggleClass('unused');
		li.find('input').toggleClass('unused');

		// childs
		var level = li.attr('data-level');
		var elements = li.nextAll('li').filter(function () {
			return !$(this).hasClass('placeholder');
		}).each(function () {
			var element = $(this);
			if (element.attr('data-level') <= level) {
				return false;
			}

			if (element.hasClass('unused') != li.hasClass('unused')) {
				element.toggleClass('unused');
				element.find('input').toggleClass('unused');
			}
		});
		self.save.save();
	});

	this.ul.on('mousedown', '.fa-bars', function (e) {
		e.preventDefault();
		apiStatusClear();

		if (self.draggee === false && e.which == 1) {
			self.draggee = $(this).closest('li');

			self.draggee_x_start = e.pageX - $('.fa-long-arrow-right:visible', self.draggee).length * 40.0;
			self.draggee_y_offset = e.pageY - (self.draggee.offset().top + 1);

			var width = self.draggee.width();
			self.placeholder = $('<li>').addClass('placeholder').insertAfter(self.draggee);
			self.draggee.addClass('draggee').css({
				'top': (self.draggee.offset().top + 1) + 'px',
				'left': self.draggee.offset().left + 'px',
				'width': width + 'px'
			});

			$(document).bind('mousemove', self.drag);
		}
	});

	$('html').mouseup(function (e) {
		if (self.draggee !== false) {
			e.preventDefault();
			$(document).unbind('mousemove', self.drag);

			self.draggee.insertAfter(self.placeholder).animate({
				'top': (self.placeholder.offset().top + 1) + 'px'
			}, 100, function () {
				self.placeholder.remove();
				$(this).removeClass('draggee').css({
					'top': '',
					'left': '',
					'width': ''
				});
			});

			// toggle unused class
			var parent = self.getParent(self.draggee);
			if (parent && parent.hasClass('unused') && !self.draggee.hasClass('unused')) {
				self.draggee.toggleClass('unused');
				self.draggee.find('input').toggleClass('unused');
			}

			// childs
			var level = self.draggee.attr('data-level');
			var elements = self.draggee.nextAll('li').filter(function () {
				return !$(this).hasClass('placeholder');
			}).each(function () {
				var element = $(this);
				if (element.attr('data-level') <= level) {
					return false;
				}

				if (element.hasClass('unused') != self.draggee.hasClass('unused')) {
					element.toggleClass('unused');
					element.find('input').toggleClass('unused');
				}
			});

			self.draggee = false;
			self.save.save();
		}
	});

	this.ul.on('keydown', 'input', function () {
		apiStatusClear();
	});

	this.getParent = function (item) {
		var prev = item.prev('li');
		while (prev.length) {
			if (prev.attr('data-level') < item.attr('data-level')) {
				return prev;
			}
			prev = prev.prev('li');
		}
		return false;
	};
}

$('ul.draggable').each(function (i, ul) {
	new Draggable(ul);
});