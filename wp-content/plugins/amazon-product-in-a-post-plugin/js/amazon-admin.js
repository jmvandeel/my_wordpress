/*
Tooltipster 2.1.4 | 6/1/13
A rockin' custom tooltip jQuery plugin
Developed by: Caleb Jacob - calebjacob.com
Copyright (C) 2013 Caleb Jacob
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
(function(d,f,g,b){var e="tooltipster",c={animation:"fade",arrow:true,arrowColor:"",content:"",delay:200,fixedWidth:0,maxWidth:0,functionBefore:function(m,n){n()},functionReady:function(m,n){},functionAfter:function(m){},icon:"(?)",iconDesktop:false,iconTouch:false,iconTheme:".tooltipster-icon",interactive:false,interactiveTolerance:350,offsetX:0,offsetY:0,onlyOne:true,position:"top",speed:350,timer:0,theme:".tooltipster-default",touchDevices:true,trigger:"hover",updateAnimation:true};function j(n,m){this.element=n;this.options=d.extend({},c,m);this._defaults=c;this._name=e;this.init()}function k(){return !!("ontouchstart" in f)}function a(){var m=g.body||g.documentElement;var o=m.style;var q="transition";if(typeof o[q]=="string"){return true}v=["Moz","Webkit","Khtml","O","ms"],q=q.charAt(0).toUpperCase()+q.substr(1);for(var n=0;n<v.length;n++){if(typeof o[v[n]+q]=="string"){return true}}return false}var l=true;if(!a()){l=false}var h=k();d(f).on("mousemove.tooltipster",function(){h=false;d(f).off("mousemove.tooltipster")});j.prototype={init:function(){var s=d(this.element);var o=this;var r=true;if((o.options.touchDevices==false)&&(h)){r=false}if(g.all&&!g.querySelector){r=false}if(r==true){if((this.options.iconDesktop==true)&&(!h)||((this.options.iconTouch==true)&&(h))){var n=s.attr("title");s.removeAttr("title");var q=o.options.iconTheme;var p=d('<span class="'+q.replace(".","")+'" title="'+n+'">'+this.options.icon+"</span>");p.insertAfter(s);s.data("tooltipsterIcon",p);s=p}var m=d.trim(o.options.content).length>0?o.options.content:s.attr("title");s.data("tooltipsterContent",m);s.removeAttr("title");if((this.options.touchDevices==true)&&(h)&&((this.options.trigger=="click")||(this.options.trigger=="hover"))){s.bind("touchstart",function(u,t){o.showTooltip()})}else{if(this.options.trigger=="hover"){s.on("mouseenter.tooltipster",function(){o.showTooltip()});if(this.options.interactive==true){s.on("mouseleave.tooltipster",function(){var u=s.data("tooltipster");var w=false;if((u!==b)&&(u!=="")){u.mouseenter(function(){w=true});u.mouseleave(function(){w=false});var t=setTimeout(function(){if(w==true){u.mouseleave(function(){o.hideTooltip()})}else{o.hideTooltip()}},o.options.interactiveTolerance)}else{o.hideTooltip()}})}else{s.on("mouseleave.tooltipster",function(){o.hideTooltip()})}}if(this.options.trigger=="click"){s.on("click.tooltipster",function(){if((s.data("tooltipster")=="")||(s.data("tooltipster")==b)){o.showTooltip()}else{o.hideTooltip()}})}}}},showTooltip:function(n){var o=d(this.element);var m=this;if(o.data("tooltipsterIcon")!==b){o=o.data("tooltipsterIcon")}if(!o.hasClass("tooltipster-disable")){if((d(".tooltipster-base").not(".tooltipster-dying").length>0)&&(m.options.onlyOne==true)){d(".tooltipster-base").not(".tooltipster-dying").not(o.data("tooltipster")).each(function(){d(this).addClass("tooltipster-kill");var p=d(this).data("origin");p.data("plugin_tooltipster").hideTooltip()})}o.clearQueue().delay(m.options.delay).queue(function(){m.options.functionBefore(o,function(){if((o.data("tooltipster")!==b)&&(o.data("tooltipster")!=="")){var y=o.data("tooltipster");if(!y.hasClass("tooltipster-kill")){var u="tooltipster-"+m.options.animation;y.removeClass("tooltipster-dying");if(l==true){y.clearQueue().addClass(u+"-show")}if(m.options.timer>0){var r=y.data("tooltipsterTimer");clearTimeout(r);r=setTimeout(function(){y.data("tooltipsterTimer",b);m.hideTooltip()},m.options.timer);y.data("tooltipsterTimer",r)}if((m.options.touchDevices==true)&&(h)){d("body").bind("touchstart",function(D){if(m.options.interactive==true){var F=d(D.target);var E=true;F.parents().each(function(){if(d(this).hasClass("tooltipster-base")){E=false}});if(E==true){m.hideTooltip();d("body").unbind("touchstart")}}else{m.hideTooltip();d("body").unbind("touchstart")}})}}}else{d("body").css("overflow-x","hidden");var z=o.data("tooltipsterContent");var x=m.options.theme;var A=x.replace(".","");var u="tooltipster-"+m.options.animation;var t="-webkit-transition-duration: "+m.options.speed+"ms; -webkit-animation-duration: "+m.options.speed+"ms; -moz-transition-duration: "+m.options.speed+"ms; -moz-animation-duration: "+m.options.speed+"ms; -o-transition-duration: "+m.options.speed+"ms; -o-animation-duration: "+m.options.speed+"ms; -ms-transition-duration: "+m.options.speed+"ms; -ms-animation-duration: "+m.options.speed+"ms; transition-duration: "+m.options.speed+"ms; animation-duration: "+m.options.speed+"ms;";var p=m.options.fixedWidth>0?"width:"+m.options.fixedWidth+"px;":"";var B=m.options.maxWidth>0?"max-width:"+m.options.maxWidth+"px;":"";var w=m.options.interactive==true?"pointer-events: auto;":"";var y=d('<div class="tooltipster-base '+A+" "+u+'" style="'+p+" "+B+" "+w+" "+t+'"></div>');var s=d('<div class="tooltipster-content"></div>');s.html(z);y.append(s);y.appendTo("body");o.data("tooltipster",y);y.data("origin",o);m.positionTooltip();m.options.functionReady(o,y);if(l==true){y.addClass(u+"-show")}else{y.css("display","none").removeClass(u).fadeIn(m.options.speed)}var C=z;var q=setInterval(function(){var D=o.data("tooltipsterContent");if(d("body").find(o).length==0){y.addClass("tooltipster-dying");m.hideTooltip()}else{if((C!==D)&&(D!=="")){C=D;y.find(".tooltipster-content").html(D);if(m.options.updateAnimation==true){if(a()){y.css({width:"","-webkit-transition":"all "+m.options.speed+"ms, width 0ms, height 0ms, left 0ms, top 0ms","-moz-transition":"all "+m.options.speed+"ms, width 0ms, height 0ms, left 0ms, top 0ms","-o-transition":"all "+m.options.speed+"ms, width 0ms, height 0ms, left 0ms, top 0ms","-ms-transition":"all "+m.options.speed+"ms, width 0ms, height 0ms, left 0ms, top 0ms",transition:"all "+m.options.speed+"ms, width 0ms, height 0ms, left 0ms, top 0ms"}).addClass("tooltipster-content-changing");setTimeout(function(){y.removeClass("tooltipster-content-changing");setTimeout(function(){y.css({"-webkit-transition":m.options.speed+"ms","-moz-transition":m.options.speed+"ms","-o-transition":m.options.speed+"ms","-ms-transition":m.options.speed+"ms",transition:m.options.speed+"ms"})},m.options.speed)},m.options.speed)}else{y.fadeTo(m.options.speed,0.5,function(){y.fadeTo(m.options.speed,1)})}}m.positionTooltip()}}if((d("body").find(y).length==0)||(d("body").find(o).length==0)){clearInterval(q)}},200);if(m.options.timer>0){var r=setTimeout(function(){y.data("tooltipsterTimer",b);m.hideTooltip()},m.options.timer+m.options.speed);y.data("tooltipsterTimer",r)}if((m.options.touchDevices==true)&&(h)){d("body").bind("touchstart",function(D){if(m.options.interactive==true){var F=d(D.target);var E=true;F.parents().each(function(){if(d(this).hasClass("tooltipster-base")){E=false}});if(E==true){m.hideTooltip();d("body").unbind("touchstart")}}else{m.hideTooltip();d("body").unbind("touchstart")}})}y.mouseleave(function(){m.hideTooltip()})}});o.dequeue()})}},hideTooltip:function(n){var q=d(this.element);var m=this;if(q.data("tooltipsterIcon")!==b){q=q.data("tooltipsterIcon")}var p=q.data("tooltipster");if(p==b){p=d(".tooltipster-dying")}q.clearQueue();if((p!==b)&&(p!=="")){var r=p.data("tooltipsterTimer");if(r!==b){clearTimeout(r)}var o="tooltipster-"+m.options.animation;if(l==true){p.clearQueue().removeClass(o+"-show").addClass("tooltipster-dying").delay(m.options.speed).queue(function(){p.remove();q.data("tooltipster","");d("body").css("verflow-x","");m.options.functionAfter(q)})}else{p.clearQueue().addClass("tooltipster-dying").fadeOut(m.options.speed,function(){p.remove();q.data("tooltipster","");d("body").css("verflow-x","");m.options.functionAfter(q)})}}},positionTooltip:function(P){var B=d(this.element);var ac=this;if(B.data("tooltipsterIcon")!==b){B=B.data("tooltipsterIcon")}if((B.data("tooltipster")!==b)&&(B.data("tooltipster")!=="")){var ai=B.data("tooltipster");ai.css("width","");var aj=d(f).width();var C=B.outerWidth(false);var ah=B.outerHeight(false);var am=ai.outerWidth(false);var n=ai.innerWidth()+1;var N=ai.outerHeight(false);var ab=B.offset();var aa=ab.top;var w=ab.left;var z=b;if(B.is("area")){var U=B.attr("shape");var ag=B.parent().attr("name");var Q=d('img[usemap="#'+ag+'"]');var o=Q.offset().left;var M=Q.offset().top;var X=B.attr("coords")!==b?B.attr("coords").split(","):b;if(U=="circle"){var O=parseInt(X[0]);var s=parseInt(X[1]);var E=parseInt(X[2]);ah=E*2;C=E*2;aa=M+s-E;w=o+O-E}else{if(U=="rect"){var O=parseInt(X[0]);var s=parseInt(X[1]);var r=parseInt(X[2]);var K=parseInt(X[3]);ah=K-s;C=r-O;aa=M+s;w=o+O}else{if(U=="poly"){var y=[];var af=[];var I=0,H=0,ae=0,ad=0;var ak="even";for(i=0;i<X.length;i++){var G=parseInt(X[i]);if(ak=="even"){if(G>ae){ae=G;if(i==0){I=ae}}if(G<I){I=G}ak="odd"}else{if(G>ad){ad=G;if(i==1){H=ad}}if(G<H){H=G}ak="even"}}ah=ad-H;C=ae-I;aa=M+H;w=o+I}else{ah=Q.outerHeight(false);C=Q.outerWidth(false);aa=M;w=o}}}}if(ac.options.fixedWidth==0){ai.css({width:n+"px","padding-left":"0px","padding-right":"0px"})}var t=0,W=0;var Y=parseInt(ac.options.offsetY);var Z=parseInt(ac.options.offsetX);var q="";function x(){var ao=d(f).scrollLeft();if((t-ao)<0){var an=t-ao;t=ao;ai.data("arrow-reposition",an)}if(((t+am)-ao)>aj){var an=t-((aj+ao)-am);t=(aj+ao)-am;ai.data("arrow-reposition",an)}}function u(ao,an){if(((aa-d(f).scrollTop()-N-Y-12)<0)&&(an.indexOf("top")>-1)){ac.options.position=ao;z=an}if(((aa+ah+N+12+Y)>(d(f).scrollTop()+d(f).height()))&&(an.indexOf("bottom")>-1)){ac.options.position=ao;z=an;W=(aa-N)-Y-12}}if(ac.options.position=="top"){var R=(w+am)-(w+C);t=(w+Z)-(R/2);W=(aa-N)-Y-12;x();u("bottom","top")}if(ac.options.position=="top-left"){t=w+Z;W=(aa-N)-Y-12;x();u("bottom-left","top-left")}if(ac.options.position=="top-right"){t=(w+C+Z)-am;W=(aa-N)-Y-12;x();u("bottom-right","top-right")}if(ac.options.position=="bottom"){var R=(w+am)-(w+C);t=w-(R/2)+Z;W=(aa+ah)+Y+12;x();u("top","bottom")}if(ac.options.position=="bottom-left"){t=w+Z;W=(aa+ah)+Y+12;x();u("top-left","bottom-left")}if(ac.options.position=="bottom-right"){t=(w+C+Z)-am;W=(aa+ah)+Y+12;x();u("top-right","bottom-right")}if(ac.options.position=="left"){t=w-Z-am-12;myLeftMirror=w+Z+C+12;var L=(aa+N)-(aa+B.outerHeight(false));W=aa-(L/2)-Y;if((t<0)&&((myLeftMirror+am)>aj)){var p=parseFloat(ai.css("border-width"))*2;var m=(am+t)-p;ai.css("width",m+"px");N=ai.outerHeight(false);t=w-Z-m-12-p;L=(aa+N)-(aa+B.outerHeight(false));W=aa-(L/2)-Y}else{if(t<0){t=w+Z+C+12;ai.data("arrow-reposition","left")}}}if(ac.options.position=="right"){t=w+Z+C+12;myLeftMirror=w-Z-am-12;var L=(aa+N)-(aa+B.outerHeight(false));W=aa-(L/2)-Y;if(((t+am)>aj)&&(myLeftMirror<0)){var p=parseFloat(ai.css("border-width"))*2;var m=(aj-t)-p;ai.css("width",m+"px");N=ai.outerHeight(false);L=(aa+N)-(aa+B.outerHeight(false));W=aa-(L/2)-Y}else{if((t+am)>aj){t=w-Z-am-12;ai.data("arrow-reposition","right")}}}if(ac.options.arrow==true){var J="tooltipster-arrow-"+ac.options.position;if(ac.options.arrowColor.length<1){var S=ai.css("background-color")}else{var S=ac.options.arrowColor}var al=ai.data("arrow-reposition");if(!al){al=""}else{if(al=="left"){J="tooltipster-arrow-right";al=""}else{if(al=="right"){J="tooltipster-arrow-left";al=""}else{al="left:"+al+"px;"}}}if((ac.options.position=="top")||(ac.options.position=="top-left")||(ac.options.position=="top-right")){var V=parseFloat(ai.css("border-bottom-width"));var A=ai.css("border-bottom-color")}else{if((ac.options.position=="bottom")||(ac.options.position=="bottom-left")||(ac.options.position=="bottom-right")){var V=parseFloat(ai.css("border-top-width"));var A=ai.css("border-top-color")}else{if(ac.options.position=="left"){var V=parseFloat(ai.css("border-right-width"));var A=ai.css("border-right-color")}else{if(ac.options.position=="right"){var V=parseFloat(ai.css("border-left-width"));var A=ai.css("border-left-color")}else{var V=parseFloat(ai.css("border-bottom-width"));var A=ai.css("border-bottom-color")}}}}if(V>1){V++}var F="";if(V!==0){var D="";var T="border-color: "+A+";";if(J.indexOf("bottom")!==-1){D="margin-top: -"+V+"px;"}else{if(J.indexOf("top")!==-1){D="margin-bottom: -"+V+"px;"}else{if(J.indexOf("left")!==-1){D="margin-right: -"+V+"px;"}else{if(J.indexOf("right")!==-1){D="margin-left: -"+V+"px;"}}}}F='<span class="tooltipster-arrow-border" style="'+D+" "+T+';"></span>'}ai.find(".tooltipster-arrow").remove();q='<div class="'+J+' tooltipster-arrow" style="'+al+'">'+F+'<span style="border-color:'+S+';"></span></div>';ai.append(q)}ai.css({top:W+"px",left:t+"px"});if(z!==b){ac.options.position=z}}}};d.fn[e]=function(n){if(typeof n==="string"){var p=this;var m=arguments[1];if(p.data("plugin_tooltipster")==b){var o=p.find("*");p=d();o.each(function(){if(d(this).data("plugin_tooltipster")!==b){p.push(d(this))}})}p.each(function(){switch(n.toLowerCase()){case"show":d(this).data("plugin_tooltipster").showTooltip();break;case"hide":d(this).data("plugin_tooltipster").hideTooltip();break;case"disable":d(this).addClass("tooltipster-disable");break;case"enable":d(this).removeClass("tooltipster-disable");break;case"destroy":d(this).data("plugin_tooltipster").hideTooltip();d(this).data("plugin_tooltipster","").attr("title",p.data("tooltipsterContent")).data("tooltipsterContent","").data("plugin_tooltipster","").off("mouseenter.tooltipster mouseleave.tooltipster click.tooltipster");break;case"update":if(d(this).data("tooltipsterIcon")==b){d(this).data("tooltipsterContent",m)}else{var q=d(this).data("tooltipsterIcon");q.data("tooltipsterContent",m)}break;case"reposition":d(this).data("plugin_tooltipster").positionTooltip();break}});return this}return this.each(function(){if(!d.data(this,"plugin_"+e)){d.data(this,"plugin_"+e,new j(this,n))}var q=d(this).data("plugin_tooltipster").options;if((q.iconDesktop==true)&&(!h)||((q.iconTouch==true)&&(h))){var r=d(this).data("plugin_tooltipster");d(this).next().data("plugin_tooltipster",r)}})};if(h){f.addEventListener("orientationchange",function(){if(d(".tooltipster-base").length>0){d(".tooltipster-base").each(function(){var m=d(this).data("origin");m.data("plugin_tooltipster").hideTooltip()})}},false)}d(f).on("resize.tooltipster",function(){var m=d(".tooltipster-base").data("origin");if((m!==null)&&(m!==b)){m.tooltipster("reposition")}})})(jQuery,window,document);

//admin functions
;(function($){
	$( document ).ready(function($) {
		// based on modal.js Copyright 2014 Thomas Griffin.  http://thomasgriffin.io/
		$( document ).on('click.update-modal-holder','.tgm-plugin-update-modal', function(e){
			e.preventDefault();
			if ( ! $(this).hasClass('thickbox') ) {
				if ( typeof adminpage != 'undefined' && 'update-core-php' != adminpage )
					$('body').addClass('update-core-php');
				$(this).addClass('thickbox').click();
			}
			$('#TB_window').on('tb_unload', function(){
				if ( typeof adminpage != 'undefined' && 'update-core-php' != adminpage )
					$('body').removeClass('update-core-php');
				$('.tgm-plugin-update-modal').removeClass('thickbox');
			});
		});
		$( document ).on('click.createpost-wrapper', '.create-appip-product', function(e){
			if( $('[name="amazon-product-single-asin"]').val() == '' ){
				alert('Please complete the form before submitting.');
				e.preventDefault();
				return false;
			}
		});

		// for add product page. Future
		/*$( document ).on('click.createpost-wrapper', '.create-appip-product', function(e){
			e.preventDefault();
			console.log('clicked');
			if ( ! $(this).hasClass('thickbox') ) {
				if ( typeof adminpage != 'undefined' && 'update-core-php' != adminpage )
					$('body').addClass('update-core-php');
				console.log('clicked2');
				$(this).addClass('thickbox').click();
			}
			$('#TB_window').on('tb_unload', function(){
				if ( typeof adminpage != 'undefined' && 'update-core-php' != adminpage )
					$('body').removeClass('update-core-php');
				$('.tgm-plugin-update-modal').removeClass('thickbox');
			});
		});
		*/
		$('.appiptabs.nav-tab').on('click',function(e){
			e.preventDefault();	
			var thisID = '#'+$(this).attr('id')+'-content';
			var thisTab = $(this);
			var activeContent = '#'+$('.nav-tab-content.active').attr('id');
			//console.log(thisID+'|'+activeContent);
			$( activeContent ).fadeOut('fast',function(){
				$('.appiptabs.nav-tab').removeClass('nav-tab-active');
				thisTab.addClass('nav-tab-active');
				$(thisID).addClass('active').fadeIn();
			}).removeClass('active');
		});
		$('[href^="?page=apipp_plugin-shortcode&tab="]').on('click',function(e){
			e.preventDefault();	
			var thisID = '#'+$(this).attr('class')+'-content';
			var thisTab = '#'+$(this).attr('class');
			var activeContent = '#'+$('.nav-tab-content.active').attr('id');
			//console.log(thisID+'|'+activeContent);
			$( activeContent ).fadeOut('fast',function(){
				$('.appiptabs.nav-tab').removeClass('nav-tab-active');
				$(thisTab).addClass('nav-tab-active');
				$(thisID).addClass('active').fadeIn();
			}).removeClass('active');
		});

		var $appipTOSnotice = $('.appip-tos-price-cache-notice').text();
		$( '#amazonProductInAPostBox1 .appip-tos-price-cache-notice-tooltip' ).attr('title',$appipTOSnotice);
		$( '#amazonProductInAPostBox1 .appip-tos-price-cache-notice-tooltip' ).tooltipster({trigger: 'click'});
		$( window ).keypress(function() {$( '#amazonProductInAPostBox1 .appip-tos-price-cache-notice-tooltip' ).data('plugin_tooltipster').hideTooltip();});
		$( ".appip-content-type" ).live('click',function() {
			var $imgbase = $('.appipexampleimg').attr('data');
			if ($(this).is(":checked")) {
				var group = "input:checkbox[name='" + $(this).attr("name") + "']";
				$(group).prop("checked", false);
				$(this).prop("checked", true);
				var srcval = $(this).val();
				$('.appipexampleimg').attr('src',$imgbase+'example-layout-'+srcval+'.png');
			} else {
				$(this).prop("checked", false);
				$('.appipexampleimg').attr('src',$imgbase+'example-blank.png');
			}
		});
		$('.taxonomy_block_post').show();
		$( ".apip-ptypecb" ).live('click',function() {
			var apptypeval = $(this).val();
			$('.taxonomy_blocks').hide();				
			$('.taxonomy_block_'+apptypeval).show('slow');	
	
		});
		$( ".apip-ptypecb" ).each(function(){
			if($(this).prop("checked") == true){
				var apptypeval = $(this).val();
				$('.taxonomy_blocks').hide();				
				$('.taxonomy_block_'+apptypeval).show('slow');	
			}
		});
		$('#the-list').delegate('.xml-show', 'click', function(e){
			e.preventDefault();
			var $showhidexml = $(this);
			if($showhidexml.hasClass( 'xml-hide' )){
				$showhidexml.next( 'textarea' ).css({ 'display' : 'none' });
				$showhidexml.removeClass( 'xml-hide' ).html( 'show xml cache data' );
			}else{
				$showhidexml.next( 'textarea' ).css({ 'display' : 'block' });
				$showhidexml.addClass( 'xml-hide' ).html( 'hide xml cache data' );
			}
			return;
		});
		$('.amazon-product_page_apipp-cache-page').delegate('.appip-cache-del', 'click', function(e){
			e.preventDefault();
			var r	= confirm( appipData.confirmDel );
			if (r == true){
				var buttonid = $(this).attr('id');
				var appbtnid = buttonid.replace( "appip-cache-", "" );
				$.post(appipData.ajaxURL, {'action' : "appip-cache-del", "appip-cache-id": appbtnid, "appip_nonce": appipData.appip_nonce }, function( data ){
					if(data == "deleted"){
						if(appbtnid == '0'){
							$( ".iedit" ).remove();
						}else{
							$( "." + buttonid + "-row" ).remove();
						}
					}else{
						alert( appipData.deleteMsgErr );
					}
					if( $( "#the-list tr" ).length == 0){
						$( "#the-list" ).html( '<tr class="alternate iedit appip-cache--row"><td colspan="4">'+appipData.noCacheMsg+'</td></tr>' );
					}
				});
			}
			return;
		});
	});
})(jQuery);

