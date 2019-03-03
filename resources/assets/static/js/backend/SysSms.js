/**
 * SysSms JS
 *
 * @author MR.Z <zsh2088@gmail.com>
 * @version 2.0 , 2016-09-18
 */

var SysSms = {
	token : $('input[name=_token]').val(),
	config : {} ,
	init : function () {
		//重新设置菜单
		if ( ! empty(Param.uri.menu) ) {
			Layout.setSidebarMenuActiveLink('set' , 'a[data-uri="' + Param.uri.menu + '"]');
		}

		//初始化ajax 提示框
		loading.initAjax();

		//初始化页面按钮
		this.initBtn();

		//初始化查询form
		this.initSearchForm();

		//初始化数据表
		this.initGrid();
	} ,

	//初始化查询form
	initSearchForm : function () {
		var $searchForm = $('#searchForm');
		$searchForm.reloadForm(Param.query);

		//查询按钮
		$('#searchBtn').on('click' , function (e) {
			e.preventDefault();

			var $dataGrid = $('#dataGrid');
			var param = $dataGrid.TableGrid('getParam');

			param = $.extend({} , param , $('#searchForm').serializeObject());
			param.page = 1;

			$dataGrid.TableGrid('setParam' , param);
			$dataGrid.TableGrid('reload');
		});
	} ,


	//显示 modal
	setPortletShow : function (type) {
		var $addEditModal = $('#addEditModal');

		$addEditModal.modal('show');
		if ( type == 'add' ) {
			$addEditModal.find('.caption-subject').html('新增 ' + Param.pageTitle);
		} else if ( type == 'edit' ) {
			$addEditModal.find('.caption-subject').html('编辑 ' + Param.pageTitle);
		}
	} ,

//关闭 modal
	setPortletHide : function () {
		$('#addEditModal').modal('hide');
	} ,

	//初始化各种按钮
	initBtn : function () {
		var self = this;

		//打开添加框
		$('#addNewBtn').on('click' , function (e) {
			e.preventDefault();
			self.setPortletShow('add');

			var $form = $('#addEditForm');

			$form.reloadForm(Param.defaultRow);


			$form.attr('action' , Param.uri.insert);
		});

		//编辑按钮
		$(document).on('click' , '.editBtn' , function (e) {
			e.preventDefault();
			self.setPortletShow('edit');

			var id = $(this).data('id');
			var row = $('#dataGrid').TableGrid('getRow' , id);
			var $form = $('#addEditForm');

			$form.reloadForm(row);


			$form.attr('action' , Param.uri.update +'/' + row.id);
		});

		//删除一行
		$(document).on('click' , '.destroyBtn' , function (e) {
			e.preventDefault();
			var id = $(this).data('id');
			self.delData(id);
		});

		$('#destroySelectBtn').on('click' , function (e) {
			e.preventDefault();
			var ids = $('.checker:checked').serializeJSON().selectChecker;
			if ( empty(ids) ) {
				tips.error('请选择要删除的记录');
				return;
			}
			self.delData(ids);
		});

		//提交添加编辑窗
		$('#submitFormBtn').on('click' , function (e) {
			e.preventDefault();
			var $form = $('#addEditForm');

			if ( $form.validForm() ) {
				var data = $form.serializeObject();
				data._token = self.token;
				$.post($form.attr('action') , data)
				 .fail(function (res) {
					 tips.error(res.responseText);
				 })
				 .done(function (res) {
					 if ( res.code == 1001 ) {
						 //需要登录
						 tips.error('请先登录');
					 } else if ( res.code != 0 ) {
						 tips.error(res.msg);
					 } else {
						 tips.success(res.msg);
						 $('#dataGrid').TableGrid('reload');
						 self.setPortletHide();
					 }
				 });
			}
		});

		//关闭添加编辑窗
		$('#closePortletBtn').on('click' , function (e) {
			e.preventDefault();
			self.setPortletHide();
		});


	} ,

	delData : function (ids) {
		var self = this;
		var data = {
			ids : ids ,
			_token : this.token,
		};

		sure.init('是否删除?' , function () {

			$.post(Param.uri.destroy , data)
			 .fail(function (res) {
				 tips.error(res.responseText);
			 })
			 .done(function (res) {
				 if ( res.code == 1001 ) {
					 //需要登录
					 tips.error('请先登录');
				 } else if ( res.code != 0 ) {
					 tips.error(res.msg);
				 } else {
					 tips.success(res.msg);
					 $('#dataGrid').TableGrid('reload');
				 }
			 });
		});
	} ,

	//初始化grid
	initGrid : function () {
		var self = this;
		var uri = Param.uri.this + '?' + $.param(Param.query);
		history.replaceState(Param.query , '' , uri);

		$('#dataGrid').TableGrid({
			uri : Param.uri.read ,
			selectAll : true ,
			param : Param.query ,
			rowStyle : function (row) {
				if ( row.status == -1 ) {
					return 'warning';
				}
				if ( row.status == 0 ) {
					return 'info';
				}
			} ,
			loadSuccess : function (rows , settings) {
				var oldUri = window.location.href;
				var uri = Param.uri.this + '?' + $.param(settings.param);
				if ( oldUri == uri ) {
					return false;
				}

				var params = $.getUrlParams(window.location.href);
				history.pushState(params , '' , oldUri);
				history.replaceState(settings.param , '' , uri);
			}
		});
	}

};

//pop state 事件
window.onpopstate = function (event) {
	if ( event && event.state ) {
		$('#searchForm').reloadForm(event.state);
		var $dataGrid = $('#dataGrid');
		$dataGrid.TableGrid('setParam' , event.state);
		$dataGrid.TableGrid('reload');
	}
};

var statusColor = {"-1" : 'default' , "0" : 'primary' , "1" : 'danger'};
var formatStatus = function (value) {
	return '<span class="label label-sm label-' + statusColor[value] + '">' + Param.status[value] + '</span>';
};

var typeColor = {captcha : 'default'};
var formatType = function (value , row) {
	return '<span class="label label-' + typeColor[value] + '">' + Param.type[value] + '</span>';
};