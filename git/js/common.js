var common = {
	strings : {
		first_page : 'първа',
		last_page : 'последна',
		found_records : 'Намерени Записи',
		filtered_records : 'Филтрирани Записи',
		edit : 'редакция',
		remove : 'изтриване',
		confirm_delete_configuration : 'Потвърди изтриване на конфигурация',
	},
	is_filtered_event : false,
	object : 'bottle',
	init : function()
	{
		if (typeof this.session_expire === 'number')
		{
			this.setSessionCounter();

			setInterval(this.setSessionCounter, 1000);
		}

		this.bottle.construct();
		this.configuration.construct();

		this.profile.construct();

		$('div.top-side>a:not(.logout)').on('click', function()
		{
			if (!$(this).hasClass('active'))
			{
				$('div.top-side>a, div.bottom-side>div').removeClass('active');

				let get_tab = $(this).data('tab');

				$(this).addClass('active');
				$(`div[data-tab=${get_tab}]`).addClass('active');

				let object = $(`div[data-tab=${get_tab}]`).data('object');

				if (object != undefined)
				{
					common.object = object;
				}
			}
		});

		$('tr.filter th>input').on('focusin', function()
		{
			common.is_filtered_event = true;
		});

		$('tr.filter th>input').on('focusout', function()
		{
			common.is_filtered_event = false;
		});

		$('table tr th>span').on('click', function()
		{
			if (!$(this).hasClass('active'))
			{
				common[common.object].sorting = {
					name : $(this).parent().data('sort'),
					type : $(this).attr('class')
				};

				$(`div[data-object="${common.object}"] table tr th`).find('span').removeClass('active');
				$(this).addClass('active');

				if (common.object === 'bottle')
				{
					common.bottle.getData();
				}
				else
				{
					common.configuration.setData();
				}
			}
		});
	},
	login : function()
	{
		$('div#right-frame input[type=button]').on('click', function()
		{
			common.setLoading($('div#right-frame'));

			let form = 'form.login';

			$(`${form}>p`).text('');
			$(`${form}>input`).removeClass('error');

			let object = {
				username : $('input[name=username]', form).val(),
				password : $('input[name=password]', form).val()
			};

			$.post({
				url : '/login',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('success'))
					{
						document.cookie = `username=${object.username};`;

						window.location.href = '/dashboard';
					}

					if (response.hasOwnProperty('error'))
					{
						common.setError(response.error, form);
						common.unsetLoading($('div#right-frame'));
					}
				}
			});
		});

		document.cookie = 'username=;';
	},
	setError : function(error, form)
	{
		let array = [];

		for (let i in error)
		{
			array.push(error[i]);

			let selectors = i.split('|');

			for (let s of selectors)
			{
				if ($(`${form} input[name=${s}]`).length)
				{
					$(`${form} input[name=${s}]`).addClass('error');
				}
			}
		}

		$(`${form}>p`).html(array.join('<hr>'));
	},
	setLoading : function(selector)
	{
		selector.css('overflow', 'hidden').prepend('<div class="background"><div class="loading"></div></div>');
	},
	unsetLoading : function(selector)
	{
		selector.css('overflow-y', 'auto').find('div.background').remove();
	},
	setSessionCounter : function()
	{
		let timeout = common.session_expire,
			cookies = document.cookie.split(';'),
			username = '';

		for (let c of cookies)
		{
			if (c.includes('username'))
			{
				username = c.split('=')[1];

				break;
			}
		}

		if (timeout < 1 || username !== common.auth)
		{
			window.location.href = '/logout';

			return;
		}

		--common.session_expire;

		let hours = Math.floor(timeout / 3600);
		hours = (String(hours).length === 1) ? `0${hours}` : hours;

		let minutes = Math.floor((timeout % 3600) / 60);
		minutes = (String(minutes).length === 1) ? `0${minutes}` : minutes;

		let seconds = timeout % 60;
		seconds = (String(seconds).length === 1) ? `0${seconds}` : seconds;

		if (timeout < 600)
		{
			$('span>b', 'div.top-side').addClass('expire');
		}

		$('span>b', 'div.top-side').text(`${hours}:${minutes}:${seconds}`);
	},
	bottle : {
		container : 'div[data-object="bottle"]',
		json : [],
		is_json_ready : false,
		filtering : {},
		sorting : {
			name : 'id',
			type : 'desc'
		},
		page : 1,
		rows : 0,
		limit : 500,
		display_pages : 9,
		pages_around_active : 4,
		construct : function()
		{
			this.getData();

			let container = this.container;

			$('input[name=get_excel]', container).on('click', function()
			{
				let query = [],
					query_string = '';

				$('table tr.filter th>input', container).each(function()
				{
					let input_name = $(this).attr('name'),
						input_value = encodeURIComponent($(this).val().trim());

					if (input_value.length)
					{
						query.push(`${input_name}=${input_value}`);
					}
				});

				$('table tr.filter th>select:not([name="purchase_order"])', container).each(function()
				{
					let select_name = $(this).attr('name'),
						select_value = encodeURIComponent($(this).val().trim());

					if (select_value.length)
					{
						query.push(`${select_name}=${select_value}`);
					}
				});

				if (query.length)
				{
					query_string = '&'+query.join('&');
				}

				window.location.href = `/excel.php?column=${common.bottle.sorting.name}&order=${common.bottle.sorting.type}${query_string}`;
			});

			$('input[name=clear_filtering]', container).on('click', function()
			{
				$('table tr.filter th>input', container).each(function()
				{
					$(this).val(this.defaultValue);
				});

				$('table tr.filter th>select', container).each(function()
				{
					this.selectedIndex = 0;
				});

				common.bottle.getData();
			});

			$('body').on('click', 'div.header>section>a', function()
			{
				let page = $(this).data('page');

				if (typeof page === 'number')
				{
					if (page === common.bottle.page || page < 1 || page > Math.ceil(common.bottle.rows / common.bottle.limit))
					{
						return;
					}
					else
					{
						common.bottle.page = page;
						common.bottle.getData();
					}
				}
			});

			$('table tr.filter th>select[name="purchase_order"]', container).on('change', function()
			{
				$('tr.filter th>input[name="purchase_order"]', container).val($(this).val());

				common.bottle.getData();
			});

			$('table tr.filter th>select:not([name="purchase_order"])', container).on('change', function()
			{
				common.bottle.getData();
			});
		},
		getData : function()
		{
			this.is_json_ready = false;

			common.setLoading($(this.container));
			common.getFiltering('bottle');

			let object = {
				filtering : JSON.stringify(this.filtering),
				sorting : JSON.stringify(this.sorting),
				page : this.page
			};

			$.post({
				url : '/bottle/data',
				dataType : 'json',
				data : object,
				success : function(response)
				{
					if (response.hasOwnProperty('data'))
					{
						common.bottle.rows = response.rows;
						common.bottle.json = response.data;
						common.bottle.is_json_ready = true;
					}
				}
			});

			this.setData();
		},
		setData : function()
		{
			let set_interval = setInterval(function()
			{
				if (common.bottle.is_json_ready)
				{
					clearInterval(set_interval);

					common.bottle.setPagination();

					let tbody = '',
						heading = Object.keys(common.bottle.filtering).length ? common.strings.filtered_records : common.strings.found_records,
						data = common.bottle.json;

					$('div[data-object="bottle"] table caption span.js-heading').text(heading);
					$('div[data-object="bottle"] table caption span.js-count').text(common.bottle.rows);
					$('tbody#js-bottle').html(tbody);

					for (let i in data)
					{
						tbody += `
						<tr>
						<td class="right">${data[i].id}</td>
						<td class="right">${data[i].purchase_order}</td>
						<td class="right">${data[i].palette_id}</td>
						<td class="right">${data[i].palette_c}</td>
						<td class="left">${data[i].palette}</td>
						<td class="right">${data[i].box_id}</td>
						<td class="right">${data[i].box_c}</td>
						<td class="left">${data[i].box}</td>
						<td class="center">${data[i].bottle_type}</td>
						<td class="right">${data[i].banderol_id}</td>
						<td class="left">${data[i].banderol}</td>
						<td class="center">${data[i].created}</td>
						</tr>
						`;
					}

					$('tbody#js-bottle').html(tbody);

					common.unsetLoading($(common.bottle.container));
				}
			}, 100);
		},
		setPagination : function()
		{
			let is_active,
				pagination = '',
				total_pages = Math.ceil(this.rows / this.limit);

			if (total_pages > this.display_pages)
			{
				let start = this.page - this.pages_around_active,
					finish = this.page + this.pages_around_active,
					display_first_page = true,
					display_last_page = true;

				if (this.page - this.pages_around_active <= 1)
				{
					start = 1;
					finish = this.display_pages;
					display_first_page = false;
					display_last_page = true;
				}

				if (this.page + this.pages_around_active >= total_pages)
				{
					start = total_pages - this.display_pages;
					finish = total_pages;
					display_first_page = true;
					display_last_page = false;
				}

				if (display_first_page)
				{
					pagination += `<a data-page="1">${common.strings.first_page}</a>`;
				}

				if (this.page > 1)
				{
					pagination += '<a data-page="'+(this.page - 1)+'">&laquo;</a>';
				}

				for (let i = start; i <= finish; ++i)
				{
					is_active = (i === this.page) ? ' class="active"' : '';

					pagination += `<a data-page="${i}"${is_active}>${i}</a>`;
				}

				if (total_pages > this.page)
				{
					pagination += '<a data-page="'+(this.page + 1)+'">&raquo;</a>';
				}

				if (display_last_page)
				{
					pagination += `<a data-page="${total_pages}">${common.strings.last_page}</a>`;
				}
			}
			else
			{
				for (let i = 1; i <= total_pages; ++i)
				{
					is_active = (i === this.page) ? ' class="active"' : '';

					pagination += `<a data-page="${i}"${is_active}>${i}</a>`;
				}
			}

			$('div.header>section').html(pagination);
		}
	},
	configuration : {
		container : 'div[data-object="configuration"]',
		json : [],
		is_json_ready : false,
		filtering : {},
		sorting : {
			name : 'ename',
			type : 'asc'
		},
		construct : function()
		{
			this.getData();

			let container = this.container,
				form = 'form#js-configuration';

			$('input[name=new_configuration]', container).on('click', function()
			{
				common.configuration.setDialog(0, form);
			});

			$('input[name=clear_filtering]', container).on('click', function()
			{
				$('table tr.filter th>input', container).each(function()
				{
					$(this).val(this.defaultValue);
				});

				common.configuration.getData();
			});

			$('body').on('click', 'a.js-edit-configuration', function()
			{
				common.configuration.setDialog($(this).data('id'), form);
			});

			$('body').on('click', 'a.js-remove-configuration', function()
			{
				let id = $(this).data('id');

				if (confirm(common.strings.confirm_delete_configuration))
				{
					$.getJSON({
						url : `/configuration/remove/${id}`,
						success : function(response)
						{
							if (response.hasOwnProperty('success'))
							{
								common.configuration.getData();
								common.setSuccessMessage(container, response.success);
							}

							if (response.hasOwnProperty('error'))
							{
								common.setError(response.error, form);
							}
						}
					});
				}
			});

			$('body').on('click', `${form} input[type=button]`, function()
			{
				$(`${form}>p`).text('');
				$(`${form}>input`).removeClass('error');

				common.setLoading(common.wrapper);

				let object = {
					id : $(this).data('id'),
					ename : $('input[name=ename]', form).val(),
					val : $('input[name=val]', form).val(),
					note : $('input[name=note]', form).val()
				};

				$.post({
					url : '/configuration/set',
					dataType : 'json',
					data : object,
					success : function(response)
					{
						if (response.hasOwnProperty('success'))
						{
							common.configuration.getData();
							common.wrapper.dialog('destroy');
							common.setSuccessMessage(container, response.success);
						}

						if (response.hasOwnProperty('error'))
						{
							setTimeout(function()
							{
								common.setError(response.error, form);
								common.unsetLoading(common.wrapper);
							}, 200);
						}
					}
				});
			});
		},
		getData : function()
		{
			common.setLoading($(this.container));

			this.is_json_ready = false;

			$.getJSON({
				url : '/configuration/data',
				success : function(response)
				{
					common.configuration.json = response.data;
					common.configuration.is_json_ready = true;
				}
			});

			this.setData();
		},
		setData : function()
		{
			let set_interval = setInterval(function()
			{
				if (common.configuration.is_json_ready)
				{
					clearInterval(set_interval);

					common.getFiltering('configuration');

					let tbody = '',
						data = common.setData(common.configuration.json, common.configuration.filtering, common.configuration.sorting);

					$('tbody#js-configuration').html(tbody);

					for (let i in data)
					{
						let edit_link = `<a data-id="${data[i].id}" class="js-edit-configuration">${common.strings.edit}</a>`,
							remove_link = `<a data-id="${data[i].id}" class="js-remove-configuration">${common.strings.remove}</a>`;

						tbody += `
						<tr>
						<td class="left">${data[i].ename}</td>
						<td class="left">${data[i].val}</td>
						<td class="left">${data[i].note}</td>
						<td class="center">${data[i].created}</td>
						<td>${edit_link}${remove_link}</td>
						</tr>
						`;
					}

					$('tbody#js-configuration').html(tbody);

					common.unsetLoading($(common.configuration.container));
				}
			}, 100);
		},
		setDialog : function(id, form)
		{
			let title = (id == 0) ? common.wrapper.data('new-configuration') : common.wrapper.data('edit-configuration');

			common.wrapper.dialog({
				width : 500,
				position : {my : 'top', at : 'top+150'},
				title : title
			}).html('').load(`/configuration/get/${id}`, function()
			{
				$('input[name=ename]', form).focus();
			});
		}
	},
	getFiltering : function(object)
	{
		let previous_filtering = JSON.stringify(common[object].filtering);

		$(`div[data-object="${object}"] table tr.filter th>input`).each(function()
		{
			let name = $(this).attr('name'),
				value = $(this).val().trim();

			if (value === '')
			{
				if (common[object].filtering.hasOwnProperty(name))
				{
					delete common[object].filtering[name];
				}
			}
			else
			{
				common[object].filtering[name] = (object === 'bottle') ? value : new RegExp(common.escapeString(value), 'im');
			}
		});

		$(`div[data-object="${object}"] table tr.filter th>select:not([name="purchase_order"])`).each(function()
		{
			let name = $(this).attr('name'),
				value = $(this).val().trim();

			if (value === '')
			{
				if (common[object].filtering.hasOwnProperty(name))
				{
					delete common[object].filtering[name];
				}
			}
			else
			{
				common[object].filtering[name] = value;
			}
		});

		let current_filtering = JSON.stringify(common[object].filtering);

		if (common.object === 'bottle' && previous_filtering !== current_filtering)
		{
			common.bottle.page = 1;
		}
	},
	setData : function(json, filtering, sorting)
	{
		let filtered_data = {},
			sorted_data = [],
			data = {};

		for (let j in json)
		{
			let is_found = true;

			for (let f in filtering)
			{
				let text = json[j][f];

				is_found = filtering[f].test(text);

				if (!is_found)
				{
					break;
				}
			}

			if (is_found)
			{
				filtered_data[j] = json[j];
			}
		}

		for (let i in filtered_data)
		{
			let value = (typeof filtered_data[i][sorting.name] === 'string') ? filtered_data[i][sorting.name].toLowerCase() : filtered_data[i][sorting.name];

			sorted_data.push([Number(i), value]);
		}

		let value_types = [];

		for (let i in sorted_data)
		{
			if (Number(sorted_data[i][1]) == sorted_data[i][1] && sorted_data[i][1] != '')
			{
				value_types.push('number');
			}
			else
			{
				value_types.push(typeof sorted_data[i][1]);
			}
		}

		value_types = [...new Set(value_types)];

		let is_convert_to_number = (value_types.length === 1 && value_types[0] === 'number');

		sorted_data.sort(function(a, b)
		{
			let first = a[1],
				second = b[1];

			if (is_convert_to_number)
			{
				first = Number(first);
				second = Number(second);
			}

			if (first < second)
			{
				return (sorting.type === 'asc') ? -1 : 1;
			}

			if (first > second)
			{
				return (sorting.type === 'asc') ? 1 : -1;
			}

			return 0;
		});

		let counter = 0;

		for (let i of sorted_data)
		{
			data[counter++] = filtered_data[i[0]];
		}

		return data;
	},
	setSuccessMessage : function(container, message)
	{
		let id = (new Date()).getTime();

		$('div.header>p', container).attr('data-id', id).addClass('success').text(message).fadeIn();

		setTimeout(function()
		{
			if ($(`div.header>p[data-id=${id}]`, container).length)
			{
				$(`div.header>p[data-id=${id}]`, container).fadeOut().text('');
			}
		}, 10000);
	},
	profile : {
		construct : function()
		{
			let password_form = 'form#js-change-password';

			$('input[name=change_password]', password_form).on('click', function()
			{
				$(`${password_form}>p`).removeClass().text('');
				$(`${password_form}>input`).removeClass('error');

				let object = {
					old_password : $('input[name=old_password]', password_form).val(),
					new_password : $('input[name=new_password]', password_form).val(),
					confirm_new_password : $('input[name=confirm_new_password]', password_form).val()
				};

				$.post({
					url : '/account/change_password',
					dataType : 'json',
					data : object,
					success : function(response)
					{
						if (response.hasOwnProperty('success'))
						{
							common.profile.toggleMessage(password_form, response.success);

							$(`${password_form}`)[0].reset();
						}

						if (response.hasOwnProperty('error'))
						{
							common.setError(response.error, password_form);
						}
					}
				});
			});
		},
		toggleMessage : function(form, message)
		{
			let id = (new Date()).getTime();

			$(`${form}>p`).attr('data-id', id).addClass('success').text(message);

			setTimeout(function()
			{
				if ($(`${form}>p[data-id=${id}]`).length)
				{
					$(`${form}>p[data-id=${id}]`).text('');
				}
			}, 10000);
		}
	},
	escapeString : function(string)
	{
		const escape_characters = '^$?+*.[](){}|\\';

		let escape_string = '';

		for (let i in string)
		{
			if ([...escape_characters].includes(string[i]))
			{
				escape_string += '\\';
			}

			escape_string += string[i];
		}

		return escape_string;
	},
	keydown : function()
	{
		$(document).on('keydown', function(event)
		{
			if (event.which === 13)
			{
				if ($('div#right-frame input[type=button]').length)
				{
					$('div#right-frame input[type=button]').trigger('click').focus();

					return false;
				}

				if (common.is_filtered_event)
				{
					if (common.object === 'bottle')
					{
						common.bottle.page = 1;
						common.bottle.getData();
					}
					else
					{
						common.configuration.setData();
					}

					return false;
				}
			}
		});
	}()
};