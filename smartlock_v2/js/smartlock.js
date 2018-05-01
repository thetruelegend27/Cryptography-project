// 智能锁控制 2015.12.19 by JiangCat
// ----------------------------------
/*
1) 首先，用(function(){})()抢windows处理js的优先级，“立即”执行。
2) 引入window的namespace，否则变量在function域外不能用。
3) 全局smartLock变量作为namespace，避免和其它可能使用的framework冲突。
4) smartLock是js的变量命名风格，第一个词小写，后面首字母大写。
*/

(function(){
	var smartLock = this.smartLock = {};
	smartLock.Func = {
		getVarsFromURL : function() {
			var url = location.search;
			var theRequest = {};
			if ( url.indexOf("?") != -1 ) {
				var str = url.substr(1);
				strs = str.split("&");
				for ( var i=0; i < strs.length; i++ ) {
					theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
				}
			}
			return theRequest;
		}
	};
})();






// Functions used in register.php page
(function(){
	smartLock.userManager = {
		// Delete an existing user and all related room permissions.
		delUser : function(uid) {
			if ( confirm('Are you sure to delete there user and all related room permission records?') )
				window.location.href = 'manageuser.php?act=del&uid='+uid;
		},
		// Change the current login password of an user.
		pwdUser : function(uid) {
			var newpassword = prompt("Please enter the new password for this user:");
			if ( newpassword === null )
				return;
			newpassword = newpassword.trim();
			if ( !newpassword.length )
				return alert('New password can not be left blank.');
			window.location.href = 'manageuser.php?act=pwd&uid='+uid+'&password='+newpassword;
		},
		// Modify the expire time of a user's permission
		modUser : function(uid, roomnum, oldexpire) {
			var newexpire = prompt("Please enter the new expire time for room #"+roomnum+" formatted as: YYYY-mm-dd HH:mm:ss\nNote: Leave it BLANK for permanent access.", oldexpire);
			if ( newexpire === null )
				return;
			newexpire = newexpire.trim();
			window.location.href = 'manageuser.php?act=mod&uid='+uid+'&roomnum='+roomnum+'&newexpire='+newexpire;
		},
		// Page initialization
		init : function() {
			return;
		}
	};
})();







// Functions used in index.php page
(function(){
	smartLock.loginPage = {
		_CONNECTER : null,
		submitForm : function(salt) {
			console.log('Generating login info...');
			
			var formel = $('loginform');
			
			console.log('Password = '+formel.getElement('input[name=password]').value.trim());
			console.log('Salt = '+salt);
			console.log('RandMask = '+formel.getElement('input[name=randmask]').value);

			var encpassword = smartLock.MD5.get(formel.getElement('input[name=password]').value.trim());
			encpassword = smartLock.MD5.get(encpassword+salt);
			console.log('Server Password = '+encpassword);

			encpassword = smartLock.MD5.get(encpassword+formel.getElement('input[name=randmask]').value);
			console.log('Encrypted Password = '+encpassword);

			formel.getElement('input[name=password]').value = '';
			formel.getElement('input[name=encpassword]').value = encpassword;
			
			console.log('Submitting form...');
			formel.submit();
		},
		// Validate the fields and encrypt password before submitting.
		checkLoginForm : function() {
			console.log('Attempting form submit...');
			var formel = $('loginform');
			if ( !formel.getElement('input[name=mobilenum]').value
				|| !formel.getElement('input[name=password]').value
				|| !formel.getElement('input[name=captcha]').value )
				return false;

			if ( smartLock.loginPage._CONNECTER && smartLock.loginPage._CONNECTER.isRunning() ) {
				smartLock.loginPage._CONNECTER.cancel();
			} else {
				smartLock.loginPage._CONNECTER = new Request({
					url : "login.php",
					method : "post",
					noCache : true,
					onFailure : function() {
						alert('Can not get salt info due to connection issue.');
					},
					onSuccess : function(resptext){
						if ( resptext.substr(0,5) == 'salt=' )
							smartLock.loginPage.submitForm(resptext.substr(5));
						else
							alert(resptext);
					}
				})
			}
			
			console.log('Aquiring salt from server...');
			smartLock.loginPage._CONNECTER.send(Object.toQueryString({
				sid : MySID,
				act : 'getsalt',
				mobilenum : formel.getElement('input[name=mobilenum]').value
			}));
			
			return false;
		},
		// Page initialization
		init : function() {
			return;
		}
	};
})();






// Functions used in home.php page
(function(){
	smartLock.pageIndex = {
		// Open selected floor control page
		openFloorPage : function(f,n) {
			window.location.href = 'selectRoom.php?floortype='+f+'&floor='+n;
		},
		// Page initialization
		init : function() {
			var urlreq = smartLock.Func.getVarsFromURL(),
				iconel = $('floortypeicon');
			$('ftprefix').set('text', urlreq['floortype'].capitalize());
			var ff1 = [], ff2 = [];
			switch ( urlreq['floortype'] ) {
				case 'office'		:	iconel.setProperty('class', 'iconfont bangongshi');
										ff1 = [1,19];
										break;
				case 'residence'	:	iconel.setProperty('class', 'iconfont zhuhu');
										ff1 = [20,29];
										break;
				case 'hotel'		:	iconel.setProperty('class', 'iconfont jiudian');
										ff1 = [30,40];
										break;
				case 'other'		:	iconel.setProperty('class', 'iconfont qitafangjian');
										ff2 = [-3,-2,-1,100,200];
										break;
				default	:	return;
			}
			
			var i;
			if ( ff1.length ) {
				for ( i=ff1[0]; i<=ff1[1]; i++ ) {
					var li = new Element('li', {
						'html' : ('Floor '+i)
					});
					li.store('floor', i);
					li.inject($('floorul'));
				}
			}
			if ( ff2.length ) {
				var litext;
				for ( i=0; i<ff2.length; i++ ) {
					switch ( ff2[i] ) {
						case 100	: litext = 'Roof';			break;
						case 200	: litext = 'Machanic';		break;
						default		: litext = 'Floor '+ff2[i];	break;
					}
					var li = new Element('li', {
						'html' : litext
					});
					li.store('floor', ff2[i]);
					li.inject($('floorul'));
				}
			}

			$('floorul').getChildren('li').each(function(li){
				li.addEvents({
					mouseover : function(){
						this.addClass('over');
					},
					mouseout : function(){
						this.removeClass('over');
					},
					click : function(){
						smartLock.pageIndex.openFloorPage(urlreq['floortype'], this.retrieve('floor'));
					}
				});
				li.inject($('floorul'));
			});
		}
	};
})();

(function(){
	smartLock.floorIndex = {
		// Open selected floor control page
		openFloorPage : function(f) {
			window.location.href = 'selectFloor.php?floortype='+f;
		},
		// Page initialization
		init : function() {
			// Apply mouse events to buttons
			document.id('floorul').getChildren('li').each(function(li){
				// The function store() and retrieve() stores and reads
				// hidden attributes assigned to an DOM element, instead
				// of messing with the id tag
				li.addEvents({
					mouseover : function(){
						this.addClass('over');
					},
					mouseout : function(){
						this.removeClass('over');
					},
					click : function(){
						smartLock.floorIndex.openFloorPage(this.get('text').toLowerCase().split(' ').shift());
					}
				});
			});
		}
	};
})();















// Functions used in selectRoom.php page
(function(){
	smartLock.pageSelectRoom = {
		// Open lock page from room select page
		openLockPage : function(role, n) {
			window.location.href = 'doorKey.php?role='+role+'&roomNum='+n;
		},
		// Page initialization
		init : function() {
			var urlreq = smartLock.Func.getVarsFromURL();
			// Insert LI element into UL as buttons, and applying mouse events.
			lockStatus[0].each(function(roomNum, idx){
				var btn = new Element('li', {
					'id'	: 'btn_'+roomNum
				});
				// Breaking a one-line-mixed-code to seperated vars for easier reading,
				// but more cpu and memory costing. Not recommended in production
				// environemnts.
				var offClass = !lockStatus[1][idx] ? 'off ' : '';
				btn.set('html', '<span class="'+offClass+'iconfont booked"></span> Room '+roomNum);
				btn.addEvents({
					mouseover : function() {
						this.addClass('over');
					},
					mouseout : function() {
						this.removeClass('over');
					},
					click : function() {
						smartLock.pageSelectRoom.openLockPage(urlreq['floortype'], roomNum);
					}
				});
				btn.inject(document.id('roomul'));
			});
		}
	};
})();

// Functions used in doorKey.php page
(function(){
	smartLock.pageDoorKey = {
		CONNECTER : null,
		// Check door status
		resultLockStatus : function(f) {
			document.id('lockIcon').setStyle('opacity', (f=='Y'?1:0.2));
			document.id('lockStatusText').set('html', (f=='Y'?'Locked':'Unlocked'));
			document.id('areaUnlock').setStyle('display', (f=='Y'?'':'none'));
			document.id('areaLock').setStyle('display', (f=='Y'?'none':''));
		},
		switchBottonLock : function(f) {
			$$('.btninput').each(function(btn){
				btn.store('disabled', (f?1:0));
				btn.setStyle('opacity', (f?0.2:1));
			});
		},
		// Page initialization
		init : function() {
			var urlreq = smartLock.Func.getVarsFromURL();
			
			// Create a new instance of Request object
			smartLock.pageDoorKey.CONNECTER = new Request({
				url : '_lockcontroller.php',
				noCache : true,
				timeout : 10000,
				onTimeout : function() {
					smartLock.pageDoorKey.switchBottonLock(false);
					document.id('lockStatusText').set('html', 'Connection timeout!');
				},
				onRequest : function() {
					smartLock.pageDoorKey.switchBottonLock(true);
					document.id('lockStatusText').set('html', 'Connecting...');
				},
				onFailure : function(xhr) {
					smartLock.pageDoorKey.switchBottonLock(false);
					document.id('lockStatusText').set('html', 'Connection failure!');
				},
				onSuccess : function(responseText, responseXML) {
					smartLock.pageDoorKey.switchBottonLock(false);
					var debuginfo = '', respondrow = '';
					if ( responseText.indexOf("\n") != -1 ) {
						var responseArray = responseText.split("\n");
						respondrow = responseArray.shift();
						debuginfo = responseArray.join("\n");
					} else {
						respondrow = responseText;
					}
					if ( debuginfo.length )
						console.debug(debuginfo);
					if ( respondrow.substr(0,2) == 'e=' ) {
						var edata = respondrow.substr(2).split('|');
						var estring = 'ERROR('+ edata[0] +')';
						if ( edata[1] )
							estring += edata[1];
						document.id('lockStatusText').set('html', estring);
					} else {
						var res = respondrow.split('|');
						if ( !res[1].trim().length ) {
							document.id('lockStatusText').set('html', 'Empty respond!');
							return;
						}
						switch ( res[0] ) {
							case 'checkstatus'	:
							case 'lock'			:
							case 'unlock'		:	smartLock.pageDoorKey.resultLockStatus(res[1]);
													break;
							default				:	document.id('lockStatusText').set('html', 'Unknown respond!');
													break;
						}
					}
				}
			});
			// Apply events to buttons
			$$('.btninput').addEvents({
				mouseover : function() {
					if ( !this.retrieve('disabled') )
						this.addClass('over');
				},
				mouseout : function() {
					this.removeClass('over');
				}
			});
			document.id('btnCheck').addEvent('click', function(){
				if ( smartLock.pageDoorKey.CONNECTER.isRunning() )
					return;
				smartLock.pageDoorKey.CONNECTER.send(Object.toQueryString({
					'sid'		: MySID,
					'action'	: 'checkstatus',
					'roomNum'	: roomNum
				}));
			});
			document.id('btnUnlock').addEvent('click', function(){
				if ( smartLock.pageDoorKey.CONNECTER.isRunning() )
					return;
				smartLock.pageDoorKey.CONNECTER.send(Object.toQueryString({
					'sid'		: MySID,
					'action'	: 'unlock',
					'roomNum'	: roomNum,
					'role'		: urlreq['role']
				}));
			});
			document.id('btnLock').addEvent('click', function(){
				if ( smartLock.pageDoorKey.CONNECTER.isRunning() )
					return;
				smartLock.pageDoorKey.CONNECTER.send(Object.toQueryString({
					'sid'		: MySID,
					'action'	: 'lock',
					'roomNum'	: roomNum,
					'role'		: urlreq['role']
				}));
			});
			// Check the lock status when the page is ready
			smartLock.pageDoorKey.CONNECTER.send(Object.toQueryString({
				'sid'		: MySID,
				'action'	: 'checkstatus',
				'roomNum'	: roomNum
			}));
		}
	};
})();



















