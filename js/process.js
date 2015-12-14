function Process() {
	this.init = function(){
		var self = this,
		panel = this.panel = $('#process-panel'),
		progress_bar = this.progress_bar = panel.find('.progress-bar'),
		progress_text = this.progress_bar_text = progress_bar.find('.progress-bar-text'),
		progress_color = this.progress_bar_color = progress_bar.find('.progress-bar-color');
		this.book_id = this.panel.attr('bookid');
		this.startReviewsCollecting();
		this.theSamePingCount = 0;
		this.maxSamePingCount = 5;
		this.processedItemsCount = 0;
	}
	
	this.resetProcessCounters = function(){
		this.theSamePingCount = 0;
		this.processedItemsCount = 0;
	}
	
	this.startReviewsCollecting = function(){
		this.resetProcessCounters();
		var self = this;
		$.ajax({
			type: "GET",
			url: "process.php",
			dataType: 'json',
			data: {
				action: 'collect-reviews',
				book_id: this.book_id,
			},
			success: function(response){
				//alert(response);
			}
		});
		this.updateProgressBar(0, 'Reviews collecting started ' + 0 + '%');
		setTimeout(function(){
			self.startReviewsCollectingPing();
		}, 2000);
	}
	
	this.startReviewsCollectingPing = function(){
		var self = this;
		$.ajax({
			type: "GET",
			url: "process.php",
			dataType: 'json',
			data: {
				action: 'collect-reviews-ping',
				book_id: this.book_id,
			},
			success: function(response){
				if(response){
					if(response.status == 1){
						if(response.data){
							if(self.theSamePingCount <= self.maxSamePingCount){
								if(self.processedItemsCount == response.data.current){
									self.theSamePingCount++;
								}else{
									self.processedItemsCount = response.data.current;
									self.theSamePingCount = 0;
								}
								var percents = self.calculateProgress(response.data);
								self.updateProgressBar(percents, 'Reviews collected ' + percents + '%');
							}else{
								//alert('restart process');
								self.startReviewsCollecting();
								return false;
							}
						}
						setTimeout(function(){
							self.startReviewsCollectingPing();
						}, 2000);
					}else{
						if(response.status == 0){
							self.updateProgressBar(100, 'Reviews collecting complete. Starting reviewers update...');
							self.startReviewersUpdate();
						}
					}
				}
			}
		});
	}
	
	this.startReviewersUpdate = function(){
		var self = this;
		$.ajax({
			type: "GET",
			url: "process.php",
			dataType: 'json',
			data: {
				action: 'reviewers-update',
				book_id: this.book_id,
			},
			success: function(response){
				//alert(response);
			}
		});
		this.updateProgressBar(0, 'Reviewers update started ' + 0 + '%');
		setTimeout(function(){
			self.startReviewersUpdatePing();
		}, 2000);
	}
	
	this.startReviewersUpdatePing = function(){
		var self = this;
		$.ajax({
			type: "GET",
			url: "process.php",
			dataType: 'json',
			data: {
				action: 'reviewers-update-ping',
				book_id: this.book_id,
			},
			success: function(response){
				if(response.status == 1 && response.data){
					var percents = self.calculateProgress(response.data);
					self.updateProgressBar(percents, 'Reviewers updated ' + percents + '%');
					setTimeout(function(){
						self.startReviewersUpdatePing();
					}, 5000);
				}else{
					//window.location = 'http://goodreads.kapver.net/index.php?book_id='+self.book_id;
					window.location = 'http://goodreads.localhost/index.php?book_id='+self.book_id;
					//self.updateProgressBar(100, 'Reviewers update complete. Starting reviewers contacts update...');
					//self.startReviewersContactsUpdate();
				}
			}
		});
	}
	
	this.calculateProgress = function(data){
		return Math.round(100/new Number(data.total)*new Number(data.current));
	}
	
	this.updateProgressBar = function(percents, label){
		this.progress_bar_text.html(label);
		this.progress_bar_color.css('width', percents+'%');
	}

}

$(function(){
	var p = new Process();
		p.init();
});