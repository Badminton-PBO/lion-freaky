//Window.console.log not available in IE9 when developper tools are not activated
if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

(function(ko, $, undefined) {

	ko.bindingHandlers.flash = {
		init: function(element) {
			$(element).hide();
		},
		update: function(element, valueAccessor) {
			var value = ko.utils.unwrapObservable(valueAccessor());
			if (value) {
				$(element).stop().hide().text(value).fadeIn(function() {
					clearTimeout($(element).data("timeout"));
					$(element).data("timeout", setTimeout(function() {
						$(element).fadeOut();
						valueAccessor()(null);
					}, 30000));
				});
			}
		},
		timeout: null
	};	
	
	debug = function (log_txt) {
		if (typeof window.console != 'undefined') {
			console.log(log_txt);
		}
	};


	function formatDate(d) {
		var days = ['Zo','Ma','Di','Woe','Do','Vr','Za'];
		var months = ['Jan','Febr','Ma','April','Mei','Juni','Juli','Aug','Sept','Okt','Nov','Dec'];

		return days[d.getDay()] + " " + d.getDate() + " " + months[d.getMonth()];
	}
	
	function formatHour(d) {
		var hh = d.getHours()
		if ( hh < 10 ) hh = '0' + hh

		var mm = d.getMinutes()
		if ( mm < 10 ) mm = '0' + mm

		return hh+":"+mm;
	}	
	
	function buildDateTime(dateTime) {
		return new Date(dateTime.substring(0,4), dateTime.substring(4,6)-1,dateTime.substring(6,8), dateTime.substring(8,10), dateTime.substring(10,12));
	}
	
	var DBLoad = function(dateTime) {		
		this.dateTime = dateTime;
		this.date = buildDateTime(dateTime);		

		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
	}
	
	var Meeting = function(hTeam,oTeam,dateTime,locationName) {
		this.hTeam = hTeam;
		this.oTeam = oTeam;
		this.dateTime = dateTime;
		this.locationName = locationName;
		this.date = buildDateTime(dateTime);		
		
		this.fullMeetingLayout = formatDate(this.date) + " : " + this.hTeam + "-" + this.oTeam;
		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
	}	

	var Button = function(name,value,selected) {
	  this.name = name;
	  this.buttonValue =  ko.observable(value);
	  this.selected = ko.observable(selected);
	}

	
	var Team = function(teamName,event,devision,series,baseTeamVblIds,captainName) {
		this.teamName = teamName;
		this.event = event;
		this.devision = devision;
		this.series = series;
		this.baseTeamVblIds = baseTeamVblIds;
		this.teamType = teamName.slice(-1);
		this.teamNumber  = teamName.slice(-2,-1);		
	}
	
				
	function myViewModel() {
		var self = this;
		self.sampleClubs = ko.observable();
		self.chosenClub = ko.observable();
		self.chosenTeamName = ko.observable();							
		self.availableMeetings = ko.observableArray();
		self.chosenMeeting = ko.observable();
		
		

		//LOAD CLUBS/TEAMS
		$.get("api/clubsAndTeams", function(data) {
			self.sampleClubs(data.clubs);
		});
			

		self.resetForm = function() {
			console.log("Resetting form...");
		};

		self.chosenTeamName.subscribe(function(newTeam) {			
			if (newTeam !== undefined && newTeam !== null) {
				console.log("Team initing...");				
				
				self.availableMeetings.removeAll();				
				//LOAD PLAYERS FOR THIS CLUB/TEAM
				$.get("api/teamAndClubPlayers/"+encodeURIComponent(newTeam.teamName), function(data) {
					$.each(data.meetings, function(index,m) {
						console.log("Adding new meeting "+m.dateTime);
						self.availableMeetings.push(new Meeting(m.hTeam,m.oTeam,m.dateTime,m.locationName));
					});							
				});
			}
		});
		
		
		
	};	
	
	
	var vm = new myViewModel();	
	ko.applyBindings(vm);		

})(ko, jQuery);
