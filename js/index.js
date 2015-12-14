function getExtension(filename) {
    var parts = filename.split('.');
    return parts[parts.length - 1];
}

function checkFileExt(filename){
	var ext = getExtension(filename);
	if(ext.toLowerCase() == 'csv'){
		return true;
	} return false;
}

function confirmSendMessage(users_count){
	return confirm('Are you sure you want to export ' + users_count + ' reviewers?');
}

$(function(){
	
	var baseurl = $('#request-panel').attr('base:url');
	var users_count = $('#report-panel').attr('users:count');
	var grid = $('#report-panel');
	var form = $('#filter-form');
	var sendSelectedEl = form.find('input.send-selected');
	var sendFileEl = form.find('input.send-file');
	var fileEl = form.find('input.profiles-for-sending');
	
	sendSelectedEl.unbind().bind('click', function(){
		if($(this).attr('checked') == 'checked'){
			sendFileEl.attr('checked', false);
			fileEl.addClass('hidden');
		}
	});
	sendFileEl.unbind().bind('click', function(){
		if($(this).attr('checked') == 'checked'){
			sendSelectedEl.attr('checked', false);
			fileEl.removeClass('hidden');
		} else fileEl.addClass('hidden');
	});
	form.find('input.send-message').unbind().bind('click', function(){
		form.find('input.action-field').val('send-message');
		form.submit();
	});
	
	form.find('input.export').unbind().bind('click', function(){
		form.find('input.action-field').val('export');
		form.submit();
	});
	
	form.find('input.submit').unbind().bind('click', function(el){
		form.find('input.action-field').val('search');
	});
	
	form.bind('submit', function(){
		var action = $(this).find('input.action-field').val();
		if(action == 'send-message'){
			if(form.find('textarea').val()){
				// form.find('input.send-selected').attr('checked') == 'checked' ? true : false;
				if(sendSelectedEl.attr('checked')){
					var sendType = 'selected';
					var ch_sel = [];
					var ch_coll = grid.find('.item-id input');
					ch_coll.each(function(){
						if($(this).attr('checked') == 'checked'){
							ch_sel.push($(this).val());
						}
					});
					if(ch_sel.length > 0){
						form.find('.user-field').val(ch_sel.join(','));
					}else{
						alert('No users selected.');
						return false;
					}
				}else if(sendFileEl.attr('checked')){
					var sendType = 'file';
					var filename = fileEl.val();
					if(!filename){
						alert('File with profiles is required.');
						return false;
					}
					if(checkFileExt(filename) == false){
						alert('File with profiles has wrong format. CSV required.');
						return false;
					}
				}else{
					var sendType = 'all';
					form.find('.user-field').val('all');
				}
			}else{
				alert('Message is required.');
				return false;
			}
			if(sendType != 'file' && users_count > 100){
				if(!confirmSendMessage(users_count)){
					return false;
				}
			}
		}
	});
	
	$('.grid-pager a').each(function(index, el){
		$(el).unbind();
		$(el).bind('click', function(){
			form.find('input.page-field').val($(el).html());
			form.find('input.action-field').val('search');
			form.submit();
			return false;
		});
	});
});