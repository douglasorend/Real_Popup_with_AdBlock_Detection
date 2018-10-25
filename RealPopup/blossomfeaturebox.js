/***********************************************
* Blossom Opt-in feature box (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
* Requires: JQuery 1.5+
* Last modified: Feb 11th, 16 to v1.1
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for this script and 100s more
***********************************************/

var blossomfeaturebox = (function($){


	var KEYCODE_ESC = 27

	var defaults = {
		fxeffect: 'swing',
		displayfreq: 'session',
		displaytype: {duration: 'always', cookiename: 'featurebox'}
	}

	var blossomui = '<div class="blossomfeaturebox">'
								+ '<div class="blossominner">'
								+ '<div class="optincontent2wrapper"></div>'
								+ '<div class="closex" title="Close">Close</div>'
								+ '<div class="errordiv"><span>:-(</span></div>'
								+ '</div>' // end blossominner
								+ '</div>'

	function getCookie(Name){ 
		var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
		if (document.cookie.match(re)) //if cookie found
			return document.cookie.match(re)[0].split("=")[1] //return its value
		return null
	}

	function setCookie(name, value, duration){
		var expirestr='', expiredate=new Date()
		if (typeof duration!="undefined"){ //if set persistent cookie
			var offsetmin=parseInt(duration) * (/hr/i.test(duration)? 60 : /day/i.test(duration)? 60*24 : 1)
			expiredate.setMinutes(expiredate.getMinutes() + offsetmin)
			expirestr="; expires=" + expiredate.toUTCString()
		}
		document.cookie = name+"="+value+"; path=/"+expirestr
	}

	var blossomfeaturebox = {
		featureboxloaded: false,
		$blossomui: null,
		$blossominner: null,
		$errordiv: null,
		$blossomoptinarea: null,
		setting: null,
		
		buildblossomui: function(){
			this.$blossomui = $(blossomui).appendTo(document.body)
			this.$blossominner = this.$blossomui.find('div.blossominner')
			this.$errordiv = this.$blossomui.find('div.errordiv')
			this.$blossomoptinarea = this.$blossomui.find('div.optincontent2wrapper')
			var $closebutt = this.$blossomui.find('div.close')

			$(document).on('keyup', function(e){
				if (blossomfeaturebox.$blossomui.css('display') == 'block'){
					if (e.keyCode == KEYCODE_ESC)
						blossomfeaturebox.hidefeaturebox()
				}
			})

			this.$blossomui.on('click', function(){
				blossomfeaturebox.hidefeaturebox()
			})

			this.$blossomoptinarea.on('click', function(e){
				e.stopPropagation()
			})


		},

		showfeaturebox: function(manualopen){
			if (manualopen){
				if (!this.featureboxloaded){
					this.populatebox(1)
				}
				else{
					this.showfeaturebox()
				}
			}
			else{
				$(document.documentElement).addClass('hidescrollbar')
				setTimeout(function(){
					blossomfeaturebox.$blossomui.addClass('openbox')
				}, 5)
			}
		},

		hidefeaturebox:function(){
			$(document.documentElement).removeClass('hidescrollbar')
			this.$blossomui.removeClass('openbox')
		},

		setupdisplay:function(manualopen){
			this.showfeaturebox()
			if (!manualopen && this.setting.displayfreq.duration != 'always'){
				var duration = (this.setting.displayfreq.duration == 'session')? undefined : this.setting.displayfreq.duration
				setCookie(this.setting.displayfreq.cookiename, 'yes', duration)
				setCookie(this.setting.displayfreq.cookiename + '_freq', this.setting.displayfreq.duration, this.setting.displayfreq.duration)
			}
		},

		populatebox: function(manualopen){
			var dfd = $.Deferred()
			var scrollstagger
			var targetfile = this.setting.optinfile
			if (targetfile.charAt(0) == '#'){
				if ($(targetfile).length == 0){
					dfd.reject()
				}
				else{
					dfd.resolve()
				}
			}
			else{
				$.ajax({
					url: targetfile,
					dataType: 'html',
					error:function(ajaxrequest){
						dfd.reject(ajaxrequest)
					},
					success:function(content){
						dfd.resolve(content)
					}
				})
			}

			dfd.always(function(){ // either success or failure
				blossomfeaturebox.$blossominner.addClass(blossomfeaturebox.setting.fxeffect)
			})

			dfd.then(function(ajaxcontent){ // success fetching content
				blossomfeaturebox.$errordiv.hide()
				if (ajaxcontent){
					blossomfeaturebox.$blossomoptinarea.html(ajaxcontent)
				}
				else{
					$(targetfile).show().appendTo(blossomfeaturebox.$blossomoptinarea)
				}
				var trackLength = $(document).height() - window.innerHeight
				if (trackLength > 10 && blossomfeaturebox.setting.displaytype.indexOf('%') != -1){
					clearTimeout(scrollstagger)
					var pct = parseInt(blossomfeaturebox.setting.displaytype)
					$(window).on('scroll.showoptin', function(){
						clearTimeout(scrollstagger)
						scrollstagger = setTimeout(function(){
							var docheight = $(document).height()
							var scrollTop = $(window).scrollTop()
							var trackLength = docheight - window.innerHeight
							var trackLeft = docheight - (scrollTop + window.innerHeight)
							var pctScrolled = 100 - Math.floor(trackLeft/trackLength * 100)
							if (pctScrolled >= pct){
								$(window).off('scroll.showoptin')
								blossomfeaturebox.setupdisplay()
							}
						}, 50)
					})
					$(window).trigger('scroll.showoptin')
				}
				else if (blossomfeaturebox.setting.displaytype.indexOf('s') != -1){
					setTimeout(function(){
						blossomfeaturebox.setupdisplay()
					}, parseInt(blossomfeaturebox.setting.displaytype) * 1000)
				}
				else{
					blossomfeaturebox.setupdisplay()
				}
				blossomfeaturebox.featureboxloaded = true	
			})

			dfd.fail(function(ajaxrequest){ // failure fetching content
				blossomfeaturebox.$blossomoptinarea.css('display', 'none')
				blossomfeaturebox.$errordiv.attr('title', 'Error loading content: ' + targetfile).show()
				blossomfeaturebox.showfeaturebox()
			})
		},

		init: function(settings){
			var s = $.extend({}, defaults, settings)
			var loadbox = true
			this.setting = s
			var $blossomui = $('div.blossomfeaturebox')
			if ($blossomui.length == 0){ // build feature box UI only once
				blossomfeaturebox.buildblossomui()
			}
			if (s.displayfreq.duration != 'always' && getCookie(s.displayfreq.cookiename)){ //stage 1 check to see if box should not be loaded
				loadbox=false
				if (getCookie(s.displayfreq.cookiename + '_freq') != s.displayfreq.duration){ //stage 2: reset cookie and load box if freq setting has been changed
					setCookie(this.setting.displayfreq.cookiename, '', -1) //delete cookie
					loadbox=true
				}
			}
			if (loadbox){
				this.populatebox()
			}
		}

	}

	return blossomfeaturebox

})(jQuery);