// 
// USE:
// ---------------------
// jQuery: http://jquery.com/
// CCV: https://github.com/liuliu/ccv
// Fabric.js: https://github.com/kangax/fabric.js/
// FileReader polyfill: https://github.com/Jahdrien/FileReader (polyfill when fileReader API not supported, using flash 10+)
// Modernizr: http://modernizr.com
// 
// 
// REQUIREMENTS
// ---------------------
// - FileReader API (Chrome 6+, Firefox 3.6+, IE10+, Safari 6+, Opera 11.5+, Safari Mobile 6+, Android 3+)
// - canvas: (all except IE >8),
// - data URLs: (* except IE >8, and limite to 32KB in IE8)
// - camera + (GetUserMedia or Media Capture API) for taking pics (if not available, will only be able to load files): Android3+, Safari Mobile 6+, Chrome 21+, Opera 12+, Firefox Nightly
//
// 
// SUPPORT (08-10-2012):
// ---------------------
// FIREFOX 3.6+
// CHROME 6+
// IE 10+ (or IE6+ via Chrome Frame)
// Android 3+
// Safari 6+
// Safari Mobile 6+
//
//
// TODO:
// ---------------------
// - fix pics orientation when photo taken from camera (iOs?)
// - use worker for face detection (security warning in Chrome on local ????)
// - allow rotate transformation ???
// - crop thumbs to proper square
// - In IE, detect if flash if installed
// - add appcache
// - server fallback when neither, fileReader API nor any polyfill is available 

// Extends default Objects
String.prototype.capitalize = function(){ return this.toLowerCase().replace(/\b[a-z]/g, function(letter) { return letter.toUpperCase(); }); }
String.prototype.ucfirst 	= function(){ return this.substr(0,1).toUpperCase() + this.substr(1,this.length); }
Array.prototype.pad 		= function(s,v){ var l = s - this.length; for (var i = 0; i<l; i++){ this.push(v); } return this; };
Array.prototype.inArray 	= function(val){ var l = this.length, ret = false; for (var i = 0; i < l; i++){ ret = this[i] == val; if ( ret ) { break; } } return ret; }

var app = 
{
	emptyPix: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQAAAAA3bvkkAAAAAnRSTlMAAQGU/a4AAAAKSURBVHjaY2gAAACCAIHaRQg7AAAAAElFTkSuQmCC',
	
	context: '#mainContent',
	state: 'default', 			// default, grayscale, mosaic
	stateSrcCanvas: null,
	srcCanvas: null,
	conf:
	{
		_USE_CHROME_FRAME_FALLBACK: false,
		_USE_FLASH_FALLBACK: true,
	},
	support:
	{
		init: function() { this.fileReading(); this.getUserMedia(); },
		fileReading: function()
		{
			if ( typeof this._fileReading !== 'undefined' ) { return this._fileReading; }
			var ret = !!(window.File && window.FileReader && window.FileList && window.Blob);
			this._fileReading = ret;
			return ret; 
		},
		getUserMedia: function()
		{
			if ( typeof this._getUserMedia !== 'undefined' ) { return this._fileReading; }
			var ret = !!(navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
			if ( ret ) { navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia; } 
			this._getUserMedia = ret;
  			return ret;
		},
		workers: function()
		{
			if ( typeof this._workers !== 'undefined' ) { return this._workers; }
			var ret = !!window.Worker;
			return ret;
		}
	},
	
	_MSG: 
	{
		_UNSUPPORTED_DATAURI_ON_LOCAL: 'This feature is not available in your browser on local files',
		_LOADING_DATAURL_OUTPUT: 'loading... Please wait!',
		_UNSUPPORTED_BROWSER: 'Your browser does not seem to support required html5 features used by this app. Please use a supported browser.',
		_UNSUPPORTED_IE_USE_CHROME_FRAME: 'This app is not compatible with this version of Internet Explorer. Please use a supported browser or install the Google Chrome Frame Plugin for Internet Explorer.',
		_UNSUPPORTED_IE_USE_FLASH: 'This app is not compatible with this version of Internet Explorer. Please use a supported browser or install Adoble Flash Player and reload this page. '
	},
	
	init: function()
	{
//alert(navigator.userAgent);
		
		var self 		= this, 
			$lv 			= $('#level'),
			$lvOutput 		= $('#levelOutput'),
			$menuActions 	= $('.action', 'menu'),
			process = function()
			{		
				// Store a canvas of the source pics
				self.saveSource();
				
				$(document)
					.on('click', 'menu .action:not(.disabled)', function(e){ $menuActions.removeClass('active'); $(this).addClass('active'); })
					.on('click', 'menu .action', function(e)
					{
						e.preventDefault();
						var fn = $(this).attr('id').replace(/Action/,'');
						
//console.log('fn: ' + fn);
						
						if ( typeof self[fn] === 'function' ){ self[fn].apply(self); }
					})
				
				$lv
					.on('change', function()
					{
						if ( typeof self[self.state] === 'function' ){ self[self.state].apply(self); }
						 
						$lvOutput.text($lv.val() + '%');
					})
			}
			
		// Store a reference to the source img 
		this.$img 	= $('img');
		this.img 	= self.$img[0]; 
		
		// Once image has been loaded
		if ( this.img.complete )	{ process(); }
		else 						{ this.$img.on('load', function(){ process(); }) }
		
		this.sniff();
		this.handleOrientation();
		this.ui.init();
		this.support.init();
		this.testSupport();
		this.nav();
		this.handleFiles();
		
		return this;
	},
	
	sniff: function()
	{
		var that 		= this,
			ua 			= navigator.userAgent || 'unknown',
			classes 	= '',
			platforms 	= ['iPhone','iPad','iPod','android','Android','Windows Phone','Windows','BlackBerry','Bada','webOS'],
			engines 	= {'AppleWebKit':'Webkit','Gecko':'Gecko','Trident':'Trident','MSIE':'Trident','Presto':'Presto','BlackBerry':'Mango','wOSBrowser':'Webkit'},
			browsers 	= {'Chrome':'Chrome','CriOS':'Chrome','Firefox':'Firefox','Safari':'Safari','Opera':'Opera','IEMobile':'IE Mobile','MSIE':'IE','Dolfin':'Dolfin'}, 
			version 	= {'full': '?', 'major': '?', 'minor': '?', 'build': '?', 'revision': '?'},
			vRegExp 	= {
				'default': '.*(default)\\/([0-9\\.]*)\\s?.*',
				'ie': '.*(MSIE)\\s([0-9\\.]*)\\;.*',
				'opera': '.*(Version)\\/([0-9\.]*)\\s?.*',
				'safari': '.*(Version)\\/([0-9\.]*)\\s?.*',
				'blackberry': '.*(BlackBerry[a-zA-Z0-9]*)\\/([0-9\\.]*)\\s.*'
			}

		// Set Default values
		for (var k in ['platform','browser','engine','os','browserVersion']){ app[k] = 'unknown' + k.ucfirst(); }
			
		// Look for platform, browser & engines
		for (var i in platforms)	{ if ( ua.indexOf(platforms[i]) !== -1 ){ app.platform = platforms[i].toLowerCase(); break; } }
		for (var name in browsers)	{ if ( ua.indexOf(name) !== -1 ){ app.browser 	= browsers[name].toLowerCase().replace(/\s/,''); break; } }
		for (var name in engines)	{ if ( ua.indexOf(name) !== -1 ){ app.engine 	= engines[name].toLowerCase(); break; } }

		// Try to get the browser version data
		if ( app.browser !== 'unknownBrowser' )
		{
			var pattern 	= vRegExp[app.browser] || vRegExp['default'].replace('default', app.browser.ucfirst()); 	// Get regex pattern to use 
				p 			= ua.replace(new RegExp(pattern, 'gi'), '$2').split('.'); 					// Split on '.'

				p.unshift(p.join('.') || '?') 	// Insert the full version as the 1st element
				p.pad(5, '?') 					// Force the parts & default version arrays to have same length
			
			// Assoc default version array keys to found values
			app.browserVersion = {'full': p[0], 'major':p[1], 'minor':p[2], 'build':p[3], 'revision':p[4]};
		}
		
		// Look for os
		if 		( ['iphone','ipad','ipod'].inArray(app.platform) )	{ app.os = 'ios'; }
		else if ( app.platform === 'windows phone' )				{ app.os = 'wpos'; }
		else if ( app.plafform !== 'unknownPlatform' ) 				{ app.os = app.platform.toLowerCase(); }
		
		app.device 			= { 'screen':{w:window.screen.width, h:window.screen.height} };
		app.isSimulator 	= ua.indexOf('XDeviceEmulator') > -1;
		app.isStandalone 	= typeof navigator.standalone !== 'undefined' && navigator.standalone;
		app.isRetina 		= (window.devicePixelRatio && window.devicePixelRatio > 1) || false;
		app.isMobile 		= ua.indexOf('Mobile') !== -1;
		
		classes = 
			app.platform + ' ' + app.os + ' ' + app.engine + ' ' + app.browser 
			+ (app.isStandalone ? ' ' : ' no-') + 'standalone' 
			+ (app.isRetina 	? ' ' : ' no-') + 'retina' 
			+ (app.isMobile 	? ' ' : ' no-') + 'mobile';
		
		$('html').addClass(classes).attr(
		{
			'data-platform': app.platform,
			'data-os': app.os,
			'data-browser': app.browser,
			'data-engine': app.engine,
			'data-browserVersion': app.browserVersion,
		})
		.removeClass('no-js');

		return this;
	},
	
	handleOrientation: function()
	{
		window.onorientationchange = function()
		{
			if ( typeof window.orientation == 'undefined' && typeof window.onmozorientation == 'undefined' ){ return this; }
			
			var or 	= window.orientation || window.onmozorientation;
			
			app.orientation = Math.abs(or) === 90 ? 'landscape' : 'portrait';
			
			$('html').removeClass('landscape portrait').addClass(app.orientation);
			
			//window.scrollTo(0,0);
			app.ui.init();
		};
		
		$(window).trigger('orientationchange');
		
		return this;
	},
	
	ui:
	{
		init: function()
		{
			//
			if ( app.platform && !!app.platform.match(/ip(?:ad|hone|od)/) && !location.hash )
			{				
				$('html').css({'height': app.device.screen[app.orientation === 'landscape' ? 'w' : 'h']});
				
				setTimeout(function() { window.scrollTo(0, 1) }, 100);
			}
			
			return this;
		},
	},
	
	notifier:
	{
		init: function()
		{
			return this;
		},
		
		add: function()
		{
			var args 	= arguments,
				o 		= $.extend(
				{
					type: 'info', 	// error, warning, info, success
					actions:{}, 	// params: url, label, title, id, target
					modal: false,	// 
					msg: null
				},args[0] || {})
			
			if ( !o.msg ){ return this; }
			
			alert(o.msg);
			
			return this;	
		}
	},
	
	testSupport: function()
	{
		var self = this;
		
		// Do not continue any longer if fileReader API is supported (meaning )
		if ( self.support.fileReading() ){ return this; }
		
		//
		if ( app.browser === 'ie' && self.conf._USE_CHROME_FRAME_FALLBACK && typeof CFInstall !== 'undefined' )
		{
			//alert(self._MSG._UNSUPPORTED_IE_USE_CHROME_FRAME);
			self.notifier.add(
			{
				type : 'info',
				msg: self._MSG._UNSUPPORTED_IE_USE_CHROME_FRAME,
			});
			CFInstall.check();
		}
		// TODO: detect if flash if installed
		//else if ( app.browser === 'ie' && self.conf._USE_FLASH_FALLBACK && app.support.flash )
		else if ( app.browser === 'ie' && self.conf._USE_FLASH_FALLBACK )
		{
		
			/*	
			//alert(self._MSG._UNSUPPORTED_IE_USE_FLASH);
			self.notifier.add(
			{
				type : 'info',
				msg:self._MSG._UNSUPPORTED_IE_USE_FLASH,
				action: 'http://get.adobe.com/fr/flashplayer',
				id: 'getFlashAction',
				target: '_blank',
				modal: true
			});*/
			
			var jslibs = 'public/js/libs/';
			
			Modernizr.load(
			{
				load: [jslibs + 'swfobject.js', jslibs + 'jquery-ui-1.9.0.custom.min.js', jslibs + 'jquery.FileReader.min.js'],
				complete: function(){ self.handleFilesViaPolyfill(); }
			});
		}
		else
		{
			//alert(self._MSG._UNSUPPORTED_BROWSER);
			self.notifier.add(
			{
				type: 'error',
				msg:self._MSG._UNSUPPORTED_BROWSER
			});
		}
		
		return this;
	},
	
	nav: function()
	{
		var self 			= this,
			$header 		= $('#header'),
			$body 			= $('#body');
			
		$(document)
			//.on('click', '#mainNav input', function(){ $(this).closest('.clickThrough').find('.action').addClass('loading'); })
			.on('click', '#menuAction', function(e)
			{
				e.preventDefault(); 
				$header.addClass('active'); 
				$body.removeClass('active').addClass('inactive');
			})
			.on('click', '#loadSelectedAction', function(e)
			{
console.log('loadSelectedAction clicked')
				e.preventDefault();
				
				// Mark app content as inited (files loaded)
				$header.removeClass('active loading'); 
				$body.removeClass('notinited inactive').addClass('inited active');
			});
		
		return this;
	},
	
	handleFilesViaPolyfill: function()
	{
console.log('handleFilesViaPolyfill');
		var self 			= this,
			$inputs 		= $('input[type="file"]', '#mainNav');
		
		/*
		$inputs
			.fileReader(
			{
				id: 'fileReaderSWFObject',
				filereader: 'public/flash/filereader.swf',
				expressInstall: 'public/flash/expressInstall.swf',
				debugMode: false,
				callback: function()
				{
console.log('fileReader is ready');
				},
				accept:'image/*'
			});*/
		
		return this;
	},
	
	handleFiles: function()
	{
		var self 			= this,
			$header 		= $('#header'),
			$body 			= $('#body'), 					
			$inputs 		= $('input[type="file"]', '#mainNav'),
			$btns 			= $('#mainNav').find('.actions'),
			$output 		= $('#loadedFiles'),
			$loadFilesBtn 	= $('#loadFilesAction'),
			humanizeSize 	= function(bsize, precision)
			{
				var ret = (bsize/1024).toFixed(precision); 
				if 		(ret < 0)	{ ret = ret/1000 + ' octets' } 
				else if (ret < 1000){ ret = ret + ' Ko'; }
				else if (ret > 10e6){ ret = ret/10e6 + ' Mo'; }
				return ret;
			}
		

		
		$inputs
			.on('change', function(e)
			{
				var $this 		= $(this),
					$clickedBtn = $this.closest('.clickThrough').find('.action'),
					files 		= e.target.files || e.dataTransfer.files || [], 										// Try to get loaded files
					$newOutput 	= files.length ? $('<output />', {id:'loadedFiles'}) : null; 	// Prepare a new element to be populated with found files list
				
//alert('loading files: ' + files.length);
console.log('loading files: ' + files.length);
				
				// Do not continue any longer if there's no file
				if ( !files.length ){ $btns.removeClass('loading'); return; }
				
//console.log($header.length);
				
				// Add 'loading' states
				$header.addClass('loading');
				$clickedBtn.addClass('loading');
				
				// Loop over the loaded files
				$.each(files, function(i,file)
				{
//console.log(file);
					var $article 	= $('<article />'),
						$img 		= $('<img />', {
							src: app.emptyPix, 
							'class': 'loading',
							load: function()
							{ 
								var $this = $(this); 
								$this.siblings('.dimensions').text($this[0].naturalWidth + 'x' + $this[0].naturalHeight);
								$this.removeClass('loading');
							}
						}),
						reader 		= new FileReader();
						
					// Try to get file dataURL
					reader.readAsDataURL(file);
					reader.onload = (function(file, img){ return function(e){ img.src = e.target.result; } })(file, $img[0]);	

					// Create a new entry for every file
					$article.append($img);
					$article.append($('<span />', {'class':'name', text:file.name}))
					$article.append($('<span />', {'class':'dimensions', text:''}))
					$article.append($('<span />', {'class':'size', text:humanizeSize(file.size, 2)}))
					$newOutput.append($article);
				})
				
				// Finally, inject the files list into the DOM 
				$output.replaceWith($newOutput)
				
				// If only 1 file has been selected, directly select it
				if ( files.length === 1 )
				{
					$newOutput.find('article:first').find('img').trigger('click');
					$('#loadSelectedAction').trigger('click');
				}
				
				// Update reference
				$output = $('#loadedFiles');
				
				// Remove 'loading' states
				//$header.removeClass('loading');
				$clickedBtn.removeClass('loading');
			})
		
		$(document)
			.on('keyup', function(e)
			//.on('keyup', '#loadedFiles article', function(e)
			{
				var $articles 	= $output.find('article'), 						// Get articles
					$active 	= $articles.filter('.selected'); 				// Find selected article (if any)
				
				// Handle UP & DOWN arrows				
				if ( e.keyCode === 38 || e.keyCode === 40 )
				{
					var dir 		= e.keyCode === 38 ? 'U' : 'D', 				// Direction: up/down ?
						$new 		= $active.length ? $active[(dir === 'U' ? 'prev' : 'next')]() : $articles.filter(dir === 'U' ? ':last' : ':first'); 	// Get the new selected item
					
					// Active the new selected item
					$new.addClass('selected').trigger('click');
					
					// And disactive the others
					$new.siblings().removeClass('selected');
				}
				// Handle page UP & page DOWN arrows				
				else if ( e.keyCode === 33 || e.keyCode === 34 )
				{
					var dir 		= e.keyCode === 33 ? 'U' : 'D', 				// Direction: up/down ?
						$new 		= $articles.filter(dir === 'U' ? ':last' : ':first');
					
					// Active the new selected item
					$new.addClass('selected').trigger('click');
					
					// And disactive the others
					$new.siblings().removeClass('selected');
				} 
			})
			.on('click', '#loadedFiles article', function()
			{
				var $this 		= $(this),
					$img 		= $(this).find('img'),
					img 		= $img[0]
					onloaded 	= function()
					{
						$('#sourceTn').attr('src', img.src);
						self.img 	= img;
						self.$img 	= $img;
						self.saveSource();
						self.copy1();
					}
				
				$header.removeClass('notinited').addClass('inited');
				$body.removeClass('notinited').addClass('inited');
				
				$this.addClass('selected').siblings().removeClass('selected');
				
				if ( img.complete && img.src !== app.emptyPix ) { onloaded(); }
				else 											{ $img.on('load', function(){ onloaded(); }); }
				
				
			})
		
		$(document)
			.on('click', '#takePics', function(e)
			{
//$(this).css('border','1px solid red')
				
				self.handleCallToCamera(e);
			})
		
		return this;
	},
	
	handleCallToCamera: function(e)
	{
		var self 		= this
			video 		= $('<video />')[0];
		
//alert('handleCallToCamera');

//alert(navigator.userAgent);
		
		window.URL = window.URL || window.webkitURL;
		
//alert('getUserMediasupported: ' + app.support._getUserMedia);
		
		if ( app.support._getUserMedia )
		{
			// !! 
			navigator.getUserMedia({video: true},
		        function successCallback(stream)
		        {
		        	e.preventDefault(); alert('success'); 
		        	
		            // Replace the source of the video element with the stream from the camera
		            /*
		            if ( navigator.getUserMedia == navigator.mozGetUserMedia )
		            { 
		            	video.src = stream;
		            }
		            else
		            {
		                video.src = window.URL.createObjectURL(stream) || stream;
		            }*/
		            
		            video.src = (navigator.getUserMedia == navigator.mozGetUserMedia) ? stream : (window.URL.createObjectURL(stream) || stream);
		             
		            video.play();
		        },
        	function(){ alert('this feature is not available.'); });
		}
		// Otherwise, try to fallback on loading pics (using input file with accept + capture)
		
		return this;
	},
	
	saveSource: function()
	{
		var self 	= this,
			img 	= self.$img[0],
			//c 		= $('<canvas />', {prop: {width: img.width, height:img.height}})[0];
			c 		= $('<canvas />', {prop: {width: img.naturalWidth, height:img.naturalHeight}})[0];
		
		c.getContext('2d').drawImage(img,0,0);
		
		 self.srcCanvas = c; 
		
		return this;
	},
	
	setState: function(state)
	{
		// Do not continue if the new state is the same as the current one
		if ( this.state === state ){ return; }
		
console.log('setState: ' + state);
		
		var self 		= this,
			$actions 	= $('.action', 'menu');
		
		// Set current state
		self.state = state;
		
		// Update actions 'active' states if needed
		$actions.filter('.active').not('.' + state + 'Action').removeClass('active');
		$actions.filter('.' + state + 'Action').not('.active').addClass('active');
		
		// Save new state initial canvas
		self.stateSrcCanvas = $('#dest')[0];
		
		return this;
	},
	
	saveState: function($canvas)
	{
		var self 	= this,
			c 		= $canvas[0];
		
		return c.getContext('2d').getImageData(0,0,c.width,c.height);
	},
	
	tests: function()
	{
		var self = this;
		
		// blur
		// brightness
		// constrast
		// hue
		
		// Face detection
		
		// Draw triangles above pics
		
		// Allow moving triangle corners
		
		// Showing/hiding layers (history + undo???)
		// restore() = history -1 when used with save()
		
		return this;
	},
	
	copy1: function()
	{
//console.log('copyUsingDrawWithImgElement');
		
		var self = this; 
		
		// Copy on existing canvas
		$('#dest').attr({width:self.img.naturalWidth, height:self.img.naturalHeight})[0].getContext('2d').drawImage(self.img,0,0);
		
		// Or we can also create a new canvas on the fly
		//var $c3 = $('<canvas>', {prop: {width: self.img.width, height:self.img.height}}).appendTo(this.context);
		//$c3[0].getContext('2d').drawImage(self.img,0,0);
		
		self.setState('default');
		return this;
	},
	
	copy2: function()
	{
console.log('copyUsingDataUrlAndDrawImage');
		
		var self 	= this,
			dataurl = null;
		
		// !! Warning !!
		// In Google Chrome, locally, throws a SECURITY_ERR: DOM Exception 18 
		try 		{ dataurl = self.srcCanvas.toDataURL('image/png') } 
		catch(e)
		{
			if ( window.location.href.match(/^file:\/\//) && navigator.userAgent.indexOf('Chrome/') !== -1 ){ alert(self._MSG._UNSUPPORTED_DATAURI_ON_LOCAL); }
console.log(e);
		}

		// Create a new pics, use the datauri as its src and draw it
		$('<img />', {src: dataurl}).on('load', function(){ $('#dest')[0].getContext('2d').drawImage($(this)[0],0,0); });
			
		return this;
	},
	
	copy3: function()
	{
console.log('copyUsingDrawWithCanvas');
		
		var self 	= this;
		
		// Draw the copy using the canvas of the source
		$('#dest')[0].getContext('2d').drawImage(self.srcCanvas,0,0,self.srcCanvas.width,self.srcCanvas.height);
		
		self.setState('default');
		
		return this;
	},
	
	copy4: function()
	{
console.log('copy using getImageData & putImageData');
		
		var self 	= this,
			c 		= self.srcCanvas,
			ctx 	= c.getContext('2d');
		
		// Draw the copy using the image data of the source
		$('#dest')[0].getContext('2d').putImageData(ctx.getImageData(0,0,c.width,c.height),0,0);
		
		
		self.setState('default');
		
		return this;
	},
	
	mosaic: function()
	{
		var self 	= this,
			c 		= $('#dest')[0],
			ctx 	= c.getContext('2d'), 	
			//ratio 	= 0.19, 														// Set resize ratio
			ratio 	= ($('#level').val()/100) || 1, 									// Set resize ratio
			tn 		= {w:self.srcCanvas.width*ratio, h:self.srcCanvas.height*ratio}, 	// Compute resized (thumbnail) dimensions
			cols 	= Math.ceil(c.width/tn.w), 										// number of items per row we have to display
			rows 	= Math.ceil(c.height/tn.h); 										// number of rows we have to display 

//console.log(self.srcCanvas);		
//console.log(self.srcCanvas.width);
//console.log(self.srcCanvas.height);
//console.log(tn);
		
		// Method 1 
		for(var i = 0; i<rows; i++)
		{
			for(var j = 0; j<cols; j++)
			{
				ctx.drawImage(self.srcCanvas,j*tn.w,i*tn.h,tn.w,tn.h);	
			}	
		}
		
		// Method 2
		// TODO: create mosaic using createPattern???
		
		self.setState('mosaic');
			
		return this;
	},
	
	
	saveAsPng: function(){ return this['export']({format:'png'}); },
	saveAsJpg: function(){ return this['export']({format:'jpg'}); },
	saveAsWebp: function()
	{
		// TODO: test support (Chrome, Opera 11.10+, Android 4.0+)
		
		return this['export']({format:'webp'});
	},
	'export': function()
	{
console.log('export')
		
		// Create canvas from the pics
		var args 	= arguments,
			o 		= $.extend({
				format: 'png', // png, jpg
			}, args[0] || {}),
			mime 	= 'image/' + (o.format === 'jpg' ? 'jpeg' : o.format), 
			dataurl = $('#dest')[0].toDataURL(mime);
		
//console.log(o);
//console.log(mime);
		
//console.log(dataurl);

		// Change the mime type to one that the browser don't know to force him to download the pics
		document.location.href = dataurl.replace(new RegExp(mime), 'image/octet-stream');
		//document.location.href = dataurl.replace(new RegExp(mime), 'fake/faketoforcedownload');
		
		return this;
	},
	
	clear: function()
	{
console.log('clear');
		
		var c 		= $('#dest')[0],
			ctx 	= c.getContext('2d');
			
		// Method 1 (also clear all canvas states)
		//c.width = c.width;
			
		// Method 2 (better perf, just redraw)
		ctx.clearRect(0,0,c.width,c.height);
		
		$('#dataUriOutput').remove();
	},
	
	reset: function()
	{	
		$('#level').val($(this).data('defval')).trigger('change');
		
		//return this.copyUsingDrawWithCanvas();
		return this.copy3();
	},
	
	saveToFile: function()
	{
		var self 		= this,
			dataUrl 	= $('#dest')[0].toDataURL('image/png'),
			pluginobj 	= $('#pluginobj')[0];
		
		if (!localStorage.lastSavePath) localStorage.lastSavePath = localStorage.savePath;
      
		pluginobj.SaveScreenshot(
			dataUrl, 'foo', localStorage.lastSavePath,
			function(result, path)
			{
console.log(result);
console.log(path);
				/*
				var message = chrome.i18n.getMessage('save_fail');
				var messageClass = 'tip_failed';
				if (result == 0 && path)
				{
					var i18nMessage = chrome.i18n.getMessage('saved_to_path');
					message = i18nMessage + '<a title="' + path +
  					'" onclick="bg.plugin.openSavePath(\'' +
  					path.replace(/\\/g, '/') + '\');">' + path + '</a>';
  					messageClass = 'tip_succeed';
					localStorage.lastSavePath = path;
				}
				if (result != 2) { photoshop.showTip(messageClass, message, 5000); }
				*/
  			},
  			//chrome.i18n.getMessage("save_image")
  			"save image"
		);

	},
	
	toDataUrlPng: function(){ return this['toDataURL']({format:'png'}); },
	toDataUrlJpg: function(){ return this['toDataURL']({format:'jpg'}); },
	toDataUrlWebp: function()
	{
		// TODO: test support (Chrome, Opera 11.10+, Android 4.0+)
		
		return this['toDataURL']({format:'webp'});
	},
	toDataUrl: function()
	{
console.log('to data url');
		var self 	= this,
			args 	= arguments,
			o 		= $.extend({
				format: 'png', // png, jpg, webp (if supported)
			}, args[0] || {}),
			mime 	= 'image/' + (o.format === 'jpg' ? 'jpeg' : o.format),
			c2 		= $('#dest')[0],
			ctx2 	= c2.getContext('2d'),
			dataurl = null;
			
		// !! Warning !!
		// In Google Chrome, locally, throws a SECURITY_ERR: DOM Exception 18 		
		//try 		{ dataurl = c2.toDataURL('image/png') } 
		try 		{ dataurl = c2.toDataURL(mime) }
		catch(e)
		{
			if ( window.location.href.match(/^file:\/\//) && navigator.userAgent.indexOf('Chrome/') !== -1 ){ alert(self._MSG._UNSUPPORTED_DATAURI_ON_LOCAL); }
		}
		
		if ( !dataurl ){ return this; }
		
		var $out = $('#dataUriOutput');
		
		if ( !$out.length )
		{
			$out = $('<div />', {'class':'ouptut', 'id': 'dataUriOutput'})
				.append($('<textarea />', {rows:5, columns:50, text:self._MSG._LOADING_DATAURL_OUTPUT}))
				.appendTo(self.context);
		}		
		
		$out.find('textarea').text(dataurl);
		
//console.log(dataurl);
		
		return this;
	},
	
	
	grayscale: function()
	{
console.log('grayscale');

		// TODO: use css filters if supported (CHROME 18+, Safari 6+, Safari Mobile 6+, Blackberry 10+)
		
		var self 	= this,
			//c 		= self.srcCanvas,
			source 	= self.state === 'grayscale' ? self.stateSrcCanvas : $('#dest')[0],
			ctx		= source.getContext('2d'),
			iData	= ctx.getImageData(0,0,source.width,source.height), 		// Get image data
			pixels 	= iData.data, 									// Get pixels from image data
			lv 		= ($('#level').val() || 50) * 2 /100;
			
console.log('lv: ' + lv);
		
		// Loop over canvas pixels
		for (var i=0; i<pixels.length; i+=4)
		{
	    	var r = pixels[i],
	    		g = pixels[i+1],
	    		b = pixels[i+2],
	    	
				// CIE luminance for the RGB
				// The human eye is bad at seeing red and blue, so we de-emphasize them.
				// http://en.wikipedia.org/wiki/Luminance_(relative)
				//v = 0.2126*r + 0.7152*g + 0.0722*b;
				v = 0.2126*lv*r + 0.7152*lv*g + 0.0722*lv*b;
			
			pixels[i] = pixels[i+1] = pixels[i+2] = v;
		}
		
		// Update the copy with those new pixels
		$('#dest')[0].getContext('2d').putImageData(iData,0,0)
		//ctx.putImageData(iData,0,0)
		
		// Store the current state (filter) of the copy
		self.setState('grayscale');
		
		return this;
	},
	
	brightness: function()
	{
console.log('brightness');
		
		var self 	= this,
			//c 		= self.srcCanvas,
			source 	= self.state === 'brightness' ? self.stateSrcCanvas : $('#dest')[0],
			ctx		= source.getContext('2d'),
			iData	= ctx.getImageData(0,0,source.width,source.height), 	// Get image data
			pixels 	= iData.data; 											// Get pixels from image data
			adj 	= 40;
			//adj 	= ($('#level').val() || 40);
			
console.log('lv: ' + adj);
		
		// Loop over canvas pixels
		for (var i=0; i<pixels.length; i+=4)
		{
            pixels[i] += adj;
            pixels[i+1] += adj;
			pixels[i+2] += adj;
		}
		
		// Update the copy with those new pixels
		$('#dest')[0].getContext('2d').putImageData(iData,0,0)
		//ctx.putImageData(iData,0,0)
		
		// Store the current state (filter) of the copy
		self.setState('brightness');
		
		return this;
	},
	
	blur: function(){ console.log('blur'); return this.convolute([ 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9, 1/9 ]); },
	convolute: function(pixels, weights, opaque)
	{
		var self = this;
		
console.log('convolute');

var side = Math.round(Math.sqrt(weights.length));
  var halfSide = Math.floor(side/2);
  var src = pixels.data;
  var sw = pixels.width;
  var sh = pixels.height;
  // pad output by the convolution matrix
  var w = sw;
  var h = sh;
  var output = Filters.createImageData(w, h);
  var dst = output.data;
  // go through the destination image pixels
  var alphaFac = opaque ? 1 : 0;
  for (var y=0; y<h; y++) {
    for (var x=0; x<w; x++) {
      var sy = y;
      var sx = x;
      var dstOff = (y*w+x)*4;
      // calculate the weighed sum of the source image pixels that
      // fall under the convolution matrix
      var r=0, g=0, b=0, a=0;
      for (var cy=0; cy<side; cy++) {
        for (var cx=0; cx<side; cx++) {
          var scy = sy + cy - halfSide;
          var scx = sx + cx - halfSide;
          if (scy >= 0 && scy < sh && scx >= 0 && scx < sw) {
            var srcOff = (scy*sw+scx)*4;
            var wt = weights[cy*side+cx];
            r += src[srcOff] * wt;
            g += src[srcOff+1] * wt;
            b += src[srcOff+2] * wt;
            a += src[srcOff+3] * wt;
          }
        }
      }
      dst[dstOff] = r;
      dst[dstOff+1] = g;
      dst[dstOff+2] = b;
      dst[dstOff+3] = a + alphaFac*(255-a);
    }
  }
  //return output;
		
		return this;
	},
	
	detectWrinkles: function()
	{
		var self = this;
		
		// Copy the source image into the canvas
		self.copy1();
		
		function makeCircle(left, top, line1, line2)
		{
			var c = new fabric.Circle(
			{
				left: left,
				top: top,
				strokeWidth: 2,
				radius: 10,
				fill: '#fff',
				stroke: '#666'
			});
			
			c.hasControls = c.hasBorders = false;
			
			c.line1 = line1;
			c.line2 = line2;
			
			return c;
		}
		
		function makeLine(coords) { return new fabric.Line(coords, {fill:'black', strokeWidth:2, selectable:false}); }
			
		var canvas 	= new fabric.Canvas('dest', {selection:true });
			line1 		= makeLine([100, 100, 200, 150]).set('fill','red'),
			line2 		= makeLine([200, 150, 100, 200]).set('fill','green'),
			line3 		= makeLine([100, 200, 100, 100]).set('fill','blue'),
			circle1 	= makeCircle(line1.get('x1'), line1.get('y1'), line1, line3).set('fill','red'),
			circle2 	= makeCircle(line2.get('x1'), line2.get('y1'), line2, line1).set('fill','green'),
			circle3 	= makeCircle(line3.get('x1'), line3.get('y1'), line3, line2).set('fill','blue')
			;
			
		//var bg 		= new fabric.Image.fromURL(self.img.src, function(f){ canvas.add(f).renderAll(); });			
		//var bg 		= new fabric.Image.fromObject(self.img, function(f){ var foo = f.set({left:canvas.width/2, top:canvas.height/2}); canvas.add(foo).renderAll();});
		//var bg 		= new fabric.Image.fromObject(self.img, function(f){ });
		
		canvas.setBackgroundImage(self.img.src, function(){ canvas.renderAll(); });
		
		canvas
			//
			.add(
				line1, line2, line3,
				circle1, circle2, circle3
			)
			.on('object:moving', function(e)
			{
				var p = e.target;
				
console.log(p);
				
				p.line1 && p.line1.set({x1:p.left, y1:p.top});
				p.line2 && p.line2.set({x2:p.left, y2:p.top});
				canvas.renderAll();
			});
		
		return this;
	},
	
	detectFace: function()
	{
console.log('detectFace');
		var self 	= this,
			async 	= true,
			c 		= $('#dest')[0],
			ctx 	= c.getContext('2d'),
			dataurl = c.toDataURL('image/png'),
			image 	= new Image();
		
//console.log(dataurl);
		
		image.onload = function()
		{
console.log('image loaded, should lauch detect');
			
			var scale = 1;
			
			function post(comp)
			{
console.log('on face detected');
console.log(comp);

				//document.getElementById("num-faces").innerHTML = comp.length.toString();
				//document.getElementById("detection-time").innerHTML = Math.round((new Date()).getTime() - elapsed_time).toString() + "ms";
				ctx.lineWidth = 2;
				ctx.strokeStyle = 'rgba(230,87,0,0.8)';
				
				// draw detected area
				for (var i = 0; i < comp.length; i++) {
					ctx.beginPath();
					ctx.arc((comp[i].x + comp[i].width * 0.5) * scale, (comp[i].y + comp[i].height * 0.5) * scale,
							(comp[i].width + comp[i].height) * 0.25 * scale * 1.2, 0, Math.PI * 2);
					ctx.stroke();
				}
			}
			
			// call main detect_objects function
			if (async)
			{
				ccv.detect_objects(
				{
					"canvas": ccv.grayscale(ccv.pre(image)),
					"cascade": cascade,
					"interval": 5,
					"min_neighbors": 1,
					"async": true,
					"worker": 1 
				})(post);
			}
			else
			{
				var comp = ccv.detect_objects(
				{
					"canvas": ccv.grayscale(ccv.pre(image)),
					"cascade": cascade,
					"interval": 5,
					"min_neighbors": 1
				});
				post(comp);
			}
		};
		
		image.src = dataurl;
		
		return this;
	}
};
