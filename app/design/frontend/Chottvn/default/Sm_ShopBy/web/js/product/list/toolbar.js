/**
 *
 * SM Shop By - Version 2.0.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */

define([
    "jquery",
    "jquery/ui",
    "Magento_Theme/js/view/messages",
    "ko",
    "Magento_Catalog/js/product/list/toolbar"
], function($, ui, messageComponent, ko) {

    $.widget('mage.productListToolbarForm', $.mage.productListToolbarForm, {
        options:
        {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            pagerControl: '.pages-items a',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            order_custom: 'order_by',
            limit: 'product_list_limit',
            pager: 'p',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            pagerDefault: '1',
			priceSliderWrap:'.price-slider-wrap',
			priceMinMax: 'input[name="price_minimum"], input[name="price_maximum"]',
			price: 'price',
			loadingMark: '.loading-mask-shopby',
			priceDefault : '',
            productsToolbarControl:'.toolbar.toolbar-products',
            productsListBlock: '.products.wrapper',
            productsListBlockListingTabs: '.box-div-category-products',
            productsListBlockListingTabsConfig: '.box-div-category-products .catalog-product-list #ajax_listingtabs_config',
            productsListBlockListingTabsLoading: '.box-div-category-products .catalog-product-list .ltabs-loading-css-focus .ltabs-loading-css',
            productsListBlockListingTabsInnerContent: '.box-div-category-products .catalog-product-list .products-grid',
            productsListBlockListingTabsContent: '.box-div-category-products .catalog-product-list',
            layeredNavigationFilterBlock: '.block.filter',
            filterItemControl: '.block.filter .item a, .block.filter .filter-clear,.block.filter .swatch-option-link-layered',
            url: ''
        },

        _create: function () {
            this._super();
            this._bind($(this.options.pagerControl), this.options.pager, this.options.pagerDefault);
            // remove price min max filter
			//this._bind($(this.options.priceMinMax), this.options.price, this.options.priceDefault);
            this._bind($(this.options.orderControl), this.options.order_custom, this.options.pagerDefault);
            $(this.options.filterItemControl)
                .off('click.'+this.namespace+'productListToolbarForm')
                .on('click.'+this.namespace+'productListToolbarForm', {}, $.proxy(this.applyFilterToProductsList, this))
            ;
        },
		
        _bind: function (element, paramName, defaultValue) {
            var sorter_element = $(this.options.orderControl).get(0).id ? $(this.options.orderControl).get(0).id:'';
            if (element.is("select")) {
                element
                    .off('change.'+this.namespace+'productListToolbarForm')
                    .on('change.'+this.namespace+'productListToolbarForm', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
			} else if(element.is("input") && sorter_element=='sorter'){
				 element
                    .off('change.'+this.namespace+'productListToolbarForm')
                    .on('change.'+this.namespace+'productListToolbarForm', {paramName: paramName, default: defaultValue}, $.proxy(this._processInputSorter, this));
				    //this._processInputSorter();	
            } else if(element.is("input")){
                 element
                    .off('change.'+this.namespace+'productListToolbarForm')
                    .on('change.'+this.namespace+'productListToolbarForm', {paramName: paramName, default: defaultValue}, $.proxy(this._processSlider, this));
                    this._startSlider();    
            } else {
                element
                    .off('click.'+this.namespace+'productListToolbarForm')
                    .on('click.'+this.namespace+'productListToolbarForm', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
        },
		
		_processSlider: function (event) {
            event.preventDefault();
			var that = this;
			var _slider_wrap = $(this.options.priceSliderWrap),
				_slider = $('#price-slider', _slider_wrap),
				_data_rate = Number(_slider.attr('data-rate')),
				_min_standard = Number(_slider.attr('data-min-standard')),
				_max_standard = Number(_slider.attr('data-max-standard')) ,
				_min_price = $('#price_minimum', _slider_wrap), 
				_min_price_value = Number(_min_price.val().replace(/[,]/g,'')),
				_max_price = $('#price_maximum', _slider_wrap) , 
				_max_price_value = Number(_max_price.val().replace(/[,]/g,''));
				_min_price_value = (_min_price_value < _min_standard || !$.isNumeric(_min_price_value)) ? _min_standard : _min_price_value;
				_max_price_value = (_max_price_value > _max_standard || !$.isNumeric(_max_price_value)) ? _max_standard : _max_price_value;
				_min_price.val(_min_price_value);
				_max_price.val(_max_price_value);
				var _max_price_url = Number(_max_price.val().replace(/[,]/g,'') / _data_rate)+0.01;
			this.changeUrl(
                event.data.paramName,
                Number(_min_price.val().replace(/[,]/g,'')/_data_rate).toFixed(2)+'-'+_max_price_url.toFixed(2),
                event.data.default
            );
		},	
		_processLink: function (event) {
            event.preventDefault();
			var _parent = $(event.currentTarget).parents('.sidebar-main , .toolbar-products');
			var _data =  $(event.currentTarget).data('value');

			if (_parent.length){
				if (typeof _data == 'undefined'){
					var _tmp,  urlPaths = $(event.currentTarget).attr('href').split('?'), 
					baseUrl = urlPaths[0],
					urlParams = urlPaths[1] ? urlPaths[1].split('&') : [];
					if (urlParams.length) {
						for(var i = 0 ; i< urlParams.length ; i++){
							if (urlParams[i].search('p=') !== -1){
								_tmp = urlParams[i].split('=');
								this.changeUrl(
									event.data.paramName,
									_tmp[1],
									event.data.default
								);
							}
						}
					}
				}else{
					this.changeUrl(
						event.data.paramName,
						$(event.currentTarget).data('value'),
						event.data.default
					);
				}
			}else{
				 this.changeUrlOriginal(
					event.data.paramName,
					$(event.currentTarget).data('value'),
					event.data.default
				);
			}
        },
		_processSelect: function (event) {
			var _parent = $(event.currentTarget).parents('.sidebar-main, .toolbar-products'); 

			if (_parent.length){
				this.changeUrl(
					event.data.paramName,
					event.currentTarget.options[event.currentTarget.selectedIndex].value,
					event.data.default
				);
			}else{
				this.changeUrlOriginal(
					event.data.paramName,
					event.currentTarget.options[event.currentTarget.selectedIndex].value,
					event.data.default
				);
			}
            
        },
        _processInputSorter: function (event) {
            var _parent = $(event.currentTarget).parents('.sidebar-main, .toolbar-products'); 

            if (_parent.length){
                this.changeUrl(
                    event.data.paramName,
                    event.currentTarget.value,
                    event.data.default
                );
            }else{
                this.changeUrlOriginal(
                    event.data.paramName,
                    event.currentTarget.value,
                    event.data.default
                );
            }
            
        },
		changeUrlOriginal: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters, i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ?
                    decode(parameters[1].replace(/\+/g, '%20')) :
                    '';
            }
            paramData[paramName] = paramValue;

            if (paramValue == defaultValue) { //eslint-disable-line eqeqeq
                delete paramData[paramName];
            }
            paramData = $.param(paramData);

            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        },
		_startSlider : function (){
			var that = this;
			var _slider_wrap = $(this.options.priceSliderWrap),
				_slider = $('#price-slider', _slider_wrap),
				_data_rate = Number(_slider.attr('data-rate')),
				_min_standard = Number(_slider.attr('data-min-standard')),
				_max_standard = Number(_slider.attr('data-max-standard')) ,
				_min_price = $('#price_minimum', _slider_wrap), 
				_min_price_value = Number(_min_price.val().replace(/[,]/g,'')),
				_max_price = $('#price_maximum', _slider_wrap) , 
				_max_price_value = Number(_max_price.val().replace(/[,]/g,''));
				_slider.slider({
					range: true,
					min: _min_standard,
					max: _max_standard,
                    step: 10000,
					values: [_min_price_value, _max_price_value],
					slide: function( event, ui ) {
						// _min_price.val( ui.values[ 0 ]);
						// _max_price.val( ui.values[ 1 ]);
                        //console.log(ui.values[ 0 ]);
                        _min_price.val( that.formatCurrency( ui.values[ 0 ].toString() ) );
                        _max_price.val( that.formatCurrency( ui.values[ 1 ].toString() ) );
					},
					stop: function( event, ui ) {
						var _max_price_url = Number(ui.values[ 1 ]/_data_rate)+0.01;
						that.changeUrl(
							'price',
							Number(_min_price.val().replace(/[,]/g,'')/_data_rate).toFixed(2)+'-'+_max_price_url.toFixed(2),
							null
						);
					}
				});
            var cur_price_minimum = $('input#price_minimum').val();
            $('input#price_minimum').val(this.formatCurrency(cur_price_minimum.replace(/[,]/g,'')));
            var cur_price_maximum= $('input#price_maximum').val();
            $('input#price_maximum').val(this.formatCurrency(cur_price_maximum.replace(/[,]/g,'')));

            $('input#price_minimum').on("keyup", function(event){
                // skip for arrow keys
                if(event.which >= 37 && event.which <= 40){
                    event.preventDefault();
                }
                $(this).val(that.formatCurrency(this.value.replace(/[,]/g,'')));
            });
            $('input#price_maximum').on("keyup", function(event){
                // skip for arrow keys
                if(event.which >= 37 && event.which <= 40){
                    event.preventDefault();
                }
                $(this).val(that.formatCurrency(this.value.replace(/[,]/g,'')));
            });
            // $('input#price_minimum').on('input', function(e){        
            //     $(this).val(that.formatCurrency(this.value.replace(/[,]/g,'')));
            // }).on('keypress',function(e){
            //     if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
            // }).on('paste', function(e){    
            //     var cb = e.originalEvent.clipboardData || window.clipboardData;      
            //     if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
            // });

            // $('input#price_maximum').on('input', function(e){        
            //     $(this).val(that.formatCurrency(this.value.replace(/[,]/g,'')));
            // }).on('keypress',function(e){
            //     if(!$.isNumeric(String.fromCharCode(e.which))) e.preventDefault();
            // }).on('paste', function(e){    
            //     var cb = e.originalEvent.clipboardData || window.clipboardData;      
            //     if(!$.isNumeric(cb.getData('text'))) e.preventDefault();
            // });
		},
        formatCurrency: function(number){
            var n = number.split('').reverse().join("");
            var n2 = n.replace(/\d\d\d(?!$)/g, "$&,");    
            return  n2.split('').reverse().join('');
        },
        applyFilterToProductsList: function (eve) {
            var link = $(eve.currentTarget);
            var urlParts = link.attr('href').split('?');
            this.processAjaxCall(urlParts[0], urlParts[1]);
            eve.preventDefault();
        },
        updateUrl: function (url, paramData) {
            if (!url) {
                return;
            }
            if (paramData && paramData.length > 0) {
                url += '?' + paramData;
            }
            
			url = (url.search('ajax=1&') !== -1) ? url.replace('ajax=1&', '') : url;
            
            if (typeof history.replaceState === 'function') {
                history.replaceState(null, null, url);
            }
        },

        getParams: function (urlParams, paramName, paramValue, defaultValue) {
            var paramData = {},
                parameters;

            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                if (parameters[1] !== undefined) {
                    paramData[parameters[0]] = parameters[1];
                } else {
                    paramData[parameters[0]] = '';
                }
            }

            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }
            return window.decodeURIComponent($.param(paramData).replace(/\+/g, '%20'));
        },
		
        _updateContent: function (content) {
            $(this.options.productsToolbarControl).remove();
            if(content.products_list){
                $(this.options.productsListBlockListingTabsContent).replaceWith(content.products_list);
            }

            if(content.filters){
                $(this.options.layeredNavigationFilterBlock).replaceWith(content.filters);
            }
			if ($(this.options.productsListBlockListingTabsContent).length){
				this._create();
				$('body').trigger('contentUpdated');
			}
        },

        updateContent: function (content) {
            // $('html, body').animate(
            //     {
            //         scrollTop: $(this.options.productsListBlockListingTabs+":first").offset().top
            //     },
            //     100,
            //     'swing',
            //     this._updateContent(content)
            // );
            this._updateContent(content);
        },

        changeUrl: function (paramName, paramValue, defaultValue) {
            var url_origin = this.options.url ? this.options.url:window.location.href;
            var urlPaths = url_origin.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = this.getParams(urlParams, paramName, paramValue, defaultValue);
            this.processAjaxCall(baseUrl, paramData);
        },

        processAjaxCall: function (baseUrl, paramData) {
			var that = this;
            var self = this;
            var result_config = $(this.options.productsListBlockListingTabsConfig).data('config');
            result_config = JSON.parse(result_config);
            var url_origin      = window.location.href;     // Returns full URL (https://example.com/path/example.html)
            
            $.ajax({
                url: baseUrl,
                data: (paramData && paramData.length > 0 ? paramData + '&type_ajax=catalog&config=' + result_config + '&ajax=1' : 'ajax=1' + '&type_ajax=catalog&config=' + result_config),
                type: 'get',
                dataType: 'json',
                cache: true,
				beforeSend: function (){
					$(document).trigger('tap');
					//$(that.options.loadingMark).show();
                    $(that.options.productsListBlockListingTabsLoading).show();
                    $(that.options.productsListBlockListingTabsInnerContent).html('');
				},
                showLoader: false
            }).done(function (response) {
                if (response.success) {
                    self.updateUrl(baseUrl, paramData);
                    self.updateContent(response.html);
                    self.setMessage({
                        type: 'success',
                        text: $.mage.__('Sections have been updated')
                    });
                } else {
                    var msg = response.error_message;
                    if (msg) {
                        self.setMessage({
                            type: 'error',
                            text: msg
                        });
                    }
                }
				$(that.options.loadingMark).hide();
				$(document).trigger("afterAjaxProductsLoaded");
				$(document).trigger("afterAjaxLazyLoad");
            }).fail(function (error) {
                self.setMessage({
                    type: 'error',
                    text: $.mage.__('Sorry, something went wrong. Please try again later.')
                });
				$(that.options.loadingMark).hide();
            });
        },
		
        setMessage: function (obj) {
            var messages = ko.observableArray([obj]);
            messageComponent().messages({
                messages: messages
            });
        }
    });

    return $.mage.productListToolbarForm;
});
