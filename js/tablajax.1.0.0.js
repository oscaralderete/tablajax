/*!
@author Oscar Alderete <me@oscaralderete.com>
@website http://oscaralderete.com
@version 1.0.0
*/
class tablajax {
	constructor(params) {
		this.info = params;
		var goTo = params.current_page,
			table = jQuery('#' + this.info.targetID + ' table'),
			select = jQuery('#' + this.info.targetID + ' tfoot select'),
			searcher = jQuery('#' + this.info.targetID + '_searcher'),
			TDs = jQuery('#' + this.info.targetID + ' td.action'),
			menu = jQuery('#' + this.info.targetID + ' td.action > a'),
			menuIsVisible = 0,
			searcherMode = false,
			sortDirection = -1,
			targetID = this.info.targetID,
			total_pages = params.total_pages;
		this.init = function () {
			jQuery('#' + this.info.targetID + ' .goFirst').click(function () {
				if (goTo !== 1) {
					goTo = 1;
					goPage();
					updateSelect();
				}
			});
			jQuery('#' + this.info.targetID + ' .goPrevious').click(function () {
				if ((goTo - 1) > 0) {
					goTo--;
					goPage();
					updateSelect();
				}
			});
			jQuery('#' + this.info.targetID + ' .goNext').click(function () {
				if ((goTo + 1) <= params.total_pages) {
					goTo++;
					goPage();
					updateSelect();
				}
			});
			jQuery('#' + this.info.targetID + ' .goLast').click(function () {
				if (goTo !== params.total_pages) {
					goTo = params.total_pages;
					goPage();
					updateSelect();
				}
			});
			jQuery('#' + this.info.targetID + ' th.sorter_on').click(function () {
				var $this = jQuery(this),
					$table = table,
					column = $this.data('column') * 1;
				//console.log('data: '+$this.data('type')+' '+$this.data('column'));
				sortDirection = -1 * sortDirection;

				var rows = $table.find('tbody > tr').get();
				rows.sort(function (a, b) {
					var A = $(a).children('td').eq(column).text();
					var B = $(b).children('td').eq(column).text();
					if ($this.data('type') === 'date') {
						A = Date.parse('1 ' + A);
						B = Date.parse('1 ' + B);
					}
					else if ($this.data('type') === 'number') {
						A = parseFloat(A.replace(/^[^\d.]*/, ''));
						A = isNaN(A) ? 0 : A;
						B = parseFloat(B.replace(/^[^\d.]*/, ''));
						B = isNaN(B) ? 0 : B;
					}
					else {
						A = A.toUpperCase();
						B = B.toUpperCase();
					}
					if (A < B)
						return -sortDirection;
					if (A > B)
						return sortDirection;
					return 0;
				});
				$.each(rows, function (index, row) {
					$table.children('tbody').append(row);
				});
			});
			select.change(function () {
				var x = this,
					y = parseInt(jQuery(x).val(), 10);
				goTo = y;
				goPage();
				updateSelect();
			});
			if (searcher.length > 0) {
				var z = searcher.children('input.inputSearch');
				z.val('');
				z.keypress(function (e) {
					if (e.which === 13) {
						search();
						e.preventDefault();
					}
				});
				searcher.children('input.buttonSearch').click(function (e) {
					search();
				});
				searcher.children('input.buttonReset').click(function (e) {
					searchReset();
				});
			};
			jQuery('body').click(function () {
				if (menuIsVisible > 0) {
					menuIsVisible = 0;
					jQuery('#' + params.targetID + ' td.action').removeClass('selected');
				}
			});
			setMenu();
			updateSelect();
		};
		this.searcher_init = function () {
			menuIsVisible = 0;
			jQuery('body').click(function () {
				if (menuIsVisible > 0) {
					menuIsVisible = 0;
					jQuery('#' + params.targetID + ' td.action').removeClass('selected');
				}
			});
			setMenu();
		};
		function goPage() {
			jQuery('*').blur();
			jQuery.ajax({
				url: 'tablajax/' + params.url,
				type: 'post',
				data: {
					paginator: 'true',
					page: goTo
				},
			}).done((response) => {
				jQuery('#' + params.targetID + ' > table tbody').html(response);
				menuIsVisible = 0;
				setMenu();
			}).fail((response) => {
				if (response.status === 200) {
					jQuery('#' + params.targetID + ' > table tbody').html(response.responseText);
					menuIsVisible = 0;
					setMenu();
				}
				else {
					alert('TABLAJAX ERROR\nNo server connection..');
				}
			});
		};
		function updateSelect() {
			var x = jQuery('#' + targetID + ' .firstOnes'),
				y = jQuery('#' + targetID + ' .lastOnes'),
				z = 'disabled';
			select.val(goTo.toString());
			if (goTo === 1) {
				x.addClass(z);
				y.removeClass(z);
			}
			else if (goTo === total_pages) {
				x.removeClass(z);
				y.addClass(z);
			}
			else {
				x.removeClass(z);
				y.removeClass(z);
			}
		};
		function search() {
			var x = searcher.children('input.inputSearch').val();
			if (jQuery.trim(x) !== '') {
				searcherMode = true;
				jQuery.getScript('tablajax/' + params.url + '?s=' + x);
			}
		};
		function searchReset() {
			if (searcherMode) {
				searcherMode = false;
				jQuery('#' + params.targetID).empty();
				searcher.children('input.inputSearch').val('');
				jQuery.getScript('tablajax/' + params.url);
			}
		};
		function setMenu() {
			var el = '#' + params.targetID + ' td.action > a';
			if (!jQuery(el)) {
				return;
			}
			jQuery(el).click(function (e) {
				var x = jQuery(this);
				jQuery('#' + params.targetID + ' td.action').removeClass('selected');
				x.parent().addClass('selected');
				menuIsVisible++;
				e.stopPropagation();
				e.preventDefault();
			});
		}
	}
};