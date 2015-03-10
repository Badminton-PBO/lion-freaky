//Window.console.log not available in IE9 when developper tools are not activated
if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

moment.locale("nl");

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

	ko.bindingHandlers.datetimepicker = {
		init: function(element, valueAccessor, allBindingsAccessor) {
			$(element).parent().datetimepicker({
                    locale: 'nl',
                    format: 'ddd DD MMM YYYY, HH:mm',
                    sideBySide : true
             });

			window.ko.utils.registerEventHandler($(element).parent(), "dp.change", function (event) {
				var value = valueAccessor();
				if (window.ko.isObservable(value)) {
					var thedate = $(element).parent().data("DateTimePicker").date();
					value(moment(thedate).format("YYYYMMDDHHmm"));
				}
			});
		},
		update: function(element, valueAccessor) {
			var widget = $(element).parent().data("DateTimePicker");
			//when the view model is updated, update the widget
			var dateInKModel = window.ko.utils.unwrapObservable(valueAccessor()).toString();
			console.log("Changing date from '"+dateInKModel+"' to '"+moment(dateInKModel,"YYYYMMDDHHmm").toDate()+"'.");
			widget.date(moment(dateInKModel,"YYYYMMDDHHmm").toDate());
		}
	};
	
	debug = function (log_txt) {
		if (typeof window.console != 'undefined') {
			console.log(log_txt);
		}
	};


	function formatDate(d) {
		return moment(d).format("ddd DD MMM");
	}
	
	function formatHour(d) {
		return moment(d).format("HH:mm");
	}	
	
	function buildDateTime(dateTime) {
		return moment(dateTime,"YYYYMMDDHHmm").toDate();
	}
	
	var DBLoad = function(dateTime) {		
		this.dateTime = dateTime;
		this.date = buildDateTime(dateTime);		

		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
	}
	var ProposedChange = function(matchCRId,proposedDateTime,acceptedState,requestedByTeam,requestedOn,finallyChosen) {
		this.matchCRId=matchCRId;
		this.proposedDateTime=ko.observable(proposedDateTime);
		this.acceptedState=ko.observable(acceptedState);
		this.requestedByTeam=ko.observable(requestedByTeam);
		this.requestedOn=requestedOn;
		this.finallyChosen=ko.observable(finallyChosen);		
		
		this.proposedDateTimeLayout = moment(this.proposedDateTime(),"YYYYMMDDHHmm").format("ddd DD MMM HH:mm");
	
		this.isCheckFinalAllowed = ko.computed(function(){
			return (this.acceptedState() == 'MOGELIJK' || this.acceptedState() == 'MOGELIJK EN BIJ VOORKEUR');
		},this);
	}
		
	function giveNewProposedChange(requestedByTeam,hTeam,oTeam) {
		var newProposedChange =  new ProposedChange();
		newProposedChange.proposedDateTime(moment().format("YYYYMMDDHHmm"));
		newProposedChange.requestedByTeam(requestedByTeam);
		newProposedChange.finallyChosen(false);
		newProposedChange.acceptedState("-");
		return newProposedChange;
	}


	var Comment = function(owner,dateTime,text) {
		this.owner=owner,
		this.dateTime = dateTime;
		this.text = ko.observable(text);
		this.isNew = false;
	}
	
	function giveNewComment(owner,dateTime,text,isNew) {
		var newComment = new Comment(owner,dateTime,text);
		newComment.isNew = true;
		return newComment;
	}
	
	function proposalAcceptedStateFilter(myAcceptedState) {
		return function(a) {
			return a.acceptedState() == myAcceptedState;
		}
	}	
	function proposalRequestedByFilter(myRequestedBy) {
		return function(a) {
			return a.requestedByTeam() == myRequestedBy;
		}
	}	
	function proposalRequestedNotByFilter(myRequestedBy) {
		return function(a) {
			return a.requestedByTeam() != myRequestedBy;
		}
	}	

	
	var Meeting = function(hTeam,oTeam,dateTime,locationName,matchIdExtra) {
		var self= this;
		//this.vm = vm;
		this.hTeam = hTeam;
		this.oTeam = oTeam;
		this.dateTime = dateTime;
		this.locationName = locationName;
		this.matchIdExtra = matchIdExtra;
		this.date = buildDateTime(dateTime);				
		
		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
		this.proposedChanges = ko.observableArray();
		this.comments = ko.observableArray();
		//this.comments.push(new Comment("Gentse 3G","201502151930","apeoazeipazoeiopaziepazeopaopeiazpeipaziepazpazoieopazieopaziepoaz"));
		//this.comments.push(new Comment("Pluimrukkers 1058G","201502151930","apeoazeipazoeiopaziepazeopaopeiazpeipaziepazpazoieopazieopaziepoaz"));
		this.status = ko.computed(function(){
			//console.log(this.proposedChanges().filter(proposalAcceptedStateFilter('-')));
			if(this.proposedChanges().filter(proposalAcceptedStateFilter('-')).length>0) {
				return "IN AANVRAAG";
			}else {
				return "LAATST VASTGELEGD TIJDSTIP";
			}
		},this);	
		
		this.removeProposal= function(p) {
			console.log("Removing proposal meeting...");
			self.proposedChanges.remove(p);
		};
		
		this.actionFor = ko.computed(function(){
			if (this.status() == "IN AANVRAAG"){
				var unAnsweredProposals=this.proposedChanges().filter(proposalAcceptedStateFilter('-'));
				if (unAnsweredProposals.filter(proposalRequestedByFilter(this.hTeam)).length>0) {
					return this.oTeam;
				} else {
					return this.hTeam;
				}					
			} else {
				return "-"
			}
		},this);
		
		this.isAddProposalAllowed = function(selectedTeam) {
			//Only allowed to add new proposal if all proposals are answers with a "NIET MOGELIJK"
			var proposalRequestedByOtherTeam = self.proposedChanges().filter(proposalRequestedNotByFilter(selectedTeam.teamName));
			console.log(proposalRequestedByOtherTeam.length);
			if (proposalRequestedByOtherTeam.length == proposalRequestedByOtherTeam.filter(proposalAcceptedStateFilter('NIET MOGELIJK')).length) {
				return true;
			} else {
				return false;
			}			
		};
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
		self.chosenTeam = ko.observable();							
		self.availableMeetings = ko.observableArray();
		self.chosenMeeting = ko.observable();
		self.newCommentText = ko.observable();	
		
		self.proposalAcceptedStates = ['-','NIET MOGELIJK','MOGELIJK','MOGELIJK EN BIJ VOORKEUR'];

		//LOAD CLUBS/TEAMS
		$.get("api/clubsAndTeams", function(data) {
			self.sampleClubs(data.clubs);
		});
			

		self.resetForm = function() {
			console.log("Resetting form...");
		};

		self.chosenTeam.subscribe(function(newTeam) {			
			if (newTeam !== undefined && newTeam !== null) {
				console.log("Team initing...");								
				self.availableMeetings.removeAll();				
				//LOAD PLAYERS FOR THIS CLUB/TEAM
				$.get("api/meetingAndMeetingChangeRequest/"+encodeURIComponent(newTeam.teamName), function(data) {
					$.each(data.meetings, function(index,m) {
						var myMeeting = new Meeting(m.hTeam,m.oTeam,m.dateTime,m.locationName,m.matchIdExtra);
						
						$.each(m.CRs, function(index,cr) {
							myMeeting.proposedChanges.push(new ProposedChange(cr.matchCRId,cr.proposedDate,cr.acceptedState,cr.requestedByTeam,cr.requestedOn,cr.finallyChosen == '1' ? true : false));
							
						});
						
						self.availableMeetings.push(myMeeting);
					});							
				});
			}
		});
		
		
		self.addNewProposal = function() {
			console.log("Adding new proposal...");			
			self.chosenMeeting().proposedChanges.push(giveNewProposedChange(self.chosenTeam().teamName,self.chosenMeeting().hTeam,self.chosenMeeting().oTeam));
		};
		

		self.addNewComment= function() {
			console.log("Adding new comment...");
			self.chosenMeeting().comments.push(giveNewComment(self.chosenTeam().teamName,moment().format("YYYYMMDDHHmm"),self.newCommentText()));
			self.newCommentText("");
		};
		
		self.save = function() {
			var vmjs = $.parseJSON(ko.toJSON(self));
			var resultObject = {"chosenMeeting": vmjs.chosenMeeting};
			var posting = $.post("api/saveMeetingChangeRequest",resultObject, function(data) {
				console.log("MeetingChangeRequest saved.");				
			});
			
			posting.fail(function(data) {
				//self.lastError("Problemen bij het opslaan van dit recept! :(");				
			});			
			
		}
		
		self.isAddProposalAllowed = ko.computed(function() {
			if (typeof self.chosenMeeting() === 'undefined') {
				return false;
			} else {
				return self.chosenMeeting().isAddProposalAllowed(self.chosenTeam());
			}
		});		
		
	};	
	
	
	var vm = new myViewModel();	
	ko.applyBindings(vm);		

})(ko, jQuery);
