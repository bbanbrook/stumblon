(function($){
	jQuery.fn.divDataTableStore = function(ops){
		var self = this;
		self.totalPagenav = null;
		self.pageActive = 1;
		self.options = jQuery.extend({
			pageActive: 1,
			totalItem: 0,
			totalItemPerPage: 1,
			limitMaxPageNumber: 3,
			sortType: 'grid'
		}, ops);
		if (jQuery(self).find('.sort-btn').length <= 0){
			jQuery(self).prepend('<li class="sort-btn"><button type="button" class="store-btn-sort-list" title="View List"></button><button type="button" class="store-btn-sort-grid" title="View Grid"></button></li>');
		}
		jQuery(self).find("button.store-btn-sort-list").click(function(){
			jQuery(self).removeClass('sort-list')
            			.removeClass('sort-grid')
            			.addClass('sort-list');
		});
		jQuery(self).find("button.store-btn-sort-grid").click(function(){
			jQuery(self).removeClass('sort-list')
            			.removeClass('sort-grid')
            			.addClass('sort-grid');
		});
		if (self.options.sortType == 'list'){
			jQuery(self).removeClass('sort-list')
			            .removeClass('sort-grid')
			            .addClass('sort-list');
		}else{
			jQuery(self).removeClass('sort-list')
            			.removeClass('sort-grid')
            			.addClass('sort-grid');
		}
		jQuery(self).find('li.dokan-single-seller').each(function(n){
			jQuery(this).attr("id", "store-item-position-"+self.options.totalItem);
			jQuery(this).css('display', 'none');
			if (n < self.options.totalItemPerPage){
				jQuery(this).css('display', 'block');
			}
			self.options.totalItem++;
		});
		if (self.options.totalItem > 0 && self.options.totalItem > self.options.totalItemPerPage){
			if (!jQuery(self).parents('div.ui-layout-east').next().hasClass('div-store-datatable-pagenav')){
				jQuery(self).parents('div.ui-layout-east').after('<div class="div-store-datatable-pagenav" id="woogeolocation-store-pagenav"></div>');
			}else{
				jQuery('.div-store-datatable-pagenav').html('');
			}
			self.totalPagenav = Math.round(self.options.totalItem/self.options.totalItemPerPage);
			var n = 1;
			if (self.totalPagenav < self.options.limitMaxPageNumber){
				for(var i = 1; i <= self.totalPagenav; i++){
					if (i == 1){
						jQuery('#woogeolocation-store-pagenav').append('<div class="page-item page-active" page-id="'+i+'">'+n+'</div>');
					}else{
						jQuery('#woogeolocation-store-pagenav').append('<div class="page-item" page-id="'+i+'">'+n+'</div>');
					}
					n++;
				}
			}else{
				jQuery('#woogeolocation-store-pagenav').append('<div class="page-item-prev" page-id="page-item-prev">&nbsp;</div>');
				for(var i = 1; i <= self.options.limitMaxPageNumber; i++){
					if (i == 1){
						jQuery('#woogeolocation-store-pagenav').append('<div class="page-item page-active" page-id="'+i+'">'+n+'</div>');
					}else{
						jQuery('#woogeolocation-store-pagenav').append('<div class="page-item" page-id="'+i+'">'+n+'</div>');
					}
					n++;
				}
				jQuery('#woogeolocation-store-pagenav').append('<div class="page-item-null" page-id="null">...</div>');
				jQuery('#woogeolocation-store-pagenav').append('<div class="page-item-next" page-id="page-item-next">&nbsp;</div>');
			}
			jQuery('#woogeolocation-store-pagenav').find('.page-item-prev').click(function(){
				if (self.pageActive > 1){
					self.pageActive--;
					self.activePage(self.pageActive);
				}
			});
			jQuery('#woogeolocation-store-pagenav').find('.page-item-next').click(function(){
				if (self.pageActive < self.totalPagenav){
					self.pageActive++;
					self.activePage(self.pageActive);
				}
			});
			calculator_pagenav_position(1);
			jQuery('#woogeolocation-store-pagenav').find('div.page-item').click(function(){
				jQuery('#woogeolocation-store-pagenav').find('div.page-item').removeClass("page-active");
				jQuery(this).addClass("page-active");
				var page_active = parseInt(jQuery(this).attr("page-id"));
				self.pageActive = page_active;
				self.activePage(page_active);
			});
		};
		self.activePage = function(page_active){
			var from_item   = (page_active-1) * self.options.totalItemPerPage;
			jQuery(self).find('li.dokan-single-seller').css("display", "none");
			for(var i = 0; i < self.options.totalItemPerPage; i++){
				var current_item = from_item + i;
				if (current_item <= self.options.totalItem){
					jQuery(self).find('#store-item-position-'+current_item).css('display', 'block');
				}
			}
			if (self.pageActive > self.options.limitMaxPageNumber){
				jQuery('#woogeolocation-store-pagenav').find('div.page-item').removeClass("page-active");
				if (jQuery('#woogeolocation-store-pagenav').find('.page-item-none').length == 0){
					jQuery('#woogeolocation-store-pagenav').find('.page-item-null').before('<div class="page-item-none page-active" page-id="'+page_active+'">'+page_active+'</div>');
				}else{
					jQuery('#woogeolocation-store-pagenav').find('.page-item-none').text(page_active);
				}
			}else{
				jQuery('#woogeolocation-store-pagenav').find('div.page-item').removeClass("page-active");
				jQuery('#woogeolocation-store-pagenav').find('div.page-item[page-id="'+page_active+'"]').addClass("page-active");
				jQuery('#woogeolocation-store-pagenav').find('.page-item-none').remove();
			}
		};
		/*
		 * Before to destroy this plugin we need to create new one
		 * 
		 * Note: This function can be call it as a route
		 */
		self.buildNewData = function(data){
			/**
			 * Append new data to this HTML plugin
			 */
			//jQuery(self).html(data);
			/**
			 * Create new plugin
			 */
			new_self = jQuery(self).divDataTableStore({
				'totalItemPerPage': 15,
				'sortType': 'list'
			});
			/**
			 * Destroy old plugin
			 */
			self.destroy();
			/**
			 * Return new Plugin
			 */
			return new_self;
		};
		self.destroy = function(){
			/*
			 * Not use self.removeData(); 
			 * 
			 * Note: we only delete all method and variables in this array/object of this plugin
			 */
			for(methods_or_variables in self){
				delete self[methods_or_variables];
			}
			/*
			 * We can only need to delete this only
			 * 
			 * Note: But we will delete each property. 
			 */
			delete self;
		}
		return self;
	};
})(jQuery);