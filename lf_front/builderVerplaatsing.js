//Window.console.log not available in IE9 when developper tools are not activated
if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

function getUrlParameter(sParam)
{
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) 
    {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == sParam) 
        {
            return decodeURIComponent(sParameterName[1]);
        }
    }
}


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
					}, 3000));
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
	var ProposedChange = function(meeting,matchCRId,proposedDateTime,acceptedState,requestedByTeam,requestedOn,finallyChosen) {
		this.meeting = meeting;
		this.matchCRId=matchCRId;
		this.proposedDateTime=ko.observable(proposedDateTime);
		this.acceptedState=ko.observable(acceptedState);
		this.requestedByTeam=ko.observable(requestedByTeam);
		this.requestedOn=requestedOn;
		this.finallyChosen=ko.observable(finallyChosen);		
		
		this.proposedDateTimeLayout = moment(this.proposedDateTime(),"YYYYMMDDHHmm").format("ddd DD MMM HH:mm");
		
		this.isCheckFinalAllowed = ko.computed(function(){
			return this.meeting.chosenTeamName == this.meeting.hTeam 
				   && (this.finallyChosen()  
					   || (this.acceptedState() == 'MOGELIJK' 
						   && this.meeting.proposedChanges().filter(proposalFinallyChosenStateFilter(true)).length == 0
						   && this.meeting.chosenTeamName == this.meeting.hTeam)
					   );
		},this);	
		
	}
	
	//Avoiding circular reference when saving to stringify to JSON
	ProposedChange.prototype.toJSON = function() {
		var copy = ko.toJS(this);
		delete copy.meeting;
		return copy;
	}	
		
		
	function giveNewProposedChange(meeting,requestedByTeam,hTeam,oTeam) {
		var newProposedChange =  new ProposedChange(meeting);
		newProposedChange.proposedDateTime(moment().format("YYYYMMDDHHmm"));
		newProposedChange.requestedByTeam(requestedByTeam);
		newProposedChange.finallyChosen(false);
		newProposedChange.acceptedState("-");
		newProposedChange.meeting = meeting;
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

	function proposalFinallyChosenStateFilter(myFinallyChosen) {
		return function(a) {
			return a.finallyChosen() == myFinallyChosen;
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

	var Meeting = function(chosenTeamName,hTeam,oTeam,dateTime,locationName,matchIdExtra) {
		var self= this;
		this.chosenTeamName = chosenTeamName;
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
			} else if(this.proposedChanges().filter(proposalAcceptedStateFilter('MOGELIJK')).length>0) {
				if (this.proposedChanges().filter(proposalAcceptedStateFilter('MOGELIJK')).filter(proposalFinallyChosenStateFilter(true)).length>0) {
					return "OVEREENKOMST";
				}else {
					return "IN AANVRAAG";
				}
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
			} else if (this.status() == "OVEREENKOMST"){				
				return "PBO";
			} else {
				return "-"
			}
		},this);
		
		this.isAddProposalAllowed = ko.computed(function(){
			var proposalRequestedByOtherTeam = this.proposedChanges().filter(proposalRequestedNotByFilter(this.chosenTeamName));
			if (proposalRequestedByOtherTeam.length == proposalRequestedByOtherTeam.filter(proposalAcceptedStateFilter('NIET MOGELIJK')).length) {
				return true;
			} else {
				return false;
			}			
		},this);
	}	
	
	//Avoiding circular reference when saving to stringify to JSON
	Meeting.prototype.toJSON = function() {
		var copy = ko.toJS(this);
		return copy;
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
		self.lastError = ko.observable();
		self.lastSuccess = ko.observable();
		self.selectedStatusses = ko.observableArray();
		
		//console.log("URL team:"+getUrlParameter("team"));
		//console.log("URL cTeam:"+getUrlParameter("cTeam"));
		self.requestedTeamName = getUrlParameter("team");
		self.requestedCounterTeamName = getUrlParameter("cTeam");
		
		//self.proposalAcceptedStates = ['-','NIET MOGELIJK','MOGELIJK','MOGELIJK EN BIJ VOORKEUR'];
		self.proposalAcceptedStates = ['-','NIET MOGELIJK','MOGELIJK'];

		//LOAD CLUBS/TEAMS
		$.get("api/clubsAndTeams", function(data) {
			data.clubs.forEach(function(club) {
				var allTeam = new Team('Allen','','','','');
				club.teams.push(allTeam);
			});
			self.sampleClubs(data.clubs);
			
			//Teamname selected by URL
			if (typeof self.requestedTeamName !== 'undefined') {
				data.clubs.forEach(function(club) {
					club.teams.forEach(function(team) {
						if (team.teamName == self.requestedTeamName) {
							console.log("Found requestedTeamName "+ team.teamName)
							self.chosenClub(club);
							self.chosenTeam(team);
						}
					});
				});
			}

		});
			

		self.resetForm = function() {
			console.log("Resetting form...");
		};

		self.chosenClub.subscribe(function(newClub) {
			self.chosenMeeting(null);
			self.availableMeetings.removeAll();											
			self.chosenTeam(null);
		});
		
		self.chosenTeam.subscribe(function(newTeam) {			
			if (newTeam !== undefined && newTeam !== null) {
				console.log("Team initing...");								
				//LOAD EVENTS FOR THIS CLUB/TEAM
				self.chosenMeeting(null);
				$.get("api/meetingAndMeetingChangeRequest/"+encodeURIComponent(self.chosenClub().clubName)+"/"+encodeURIComponent(newTeam.teamName), function(data) {
					var myMeetings = [];
					$.each(data.meetings, function(index,m) {
						var myMeeting = new Meeting(self.chosenTeam().teamName,m.hTeam,m.oTeam,m.dateTime,m.locationName,m.matchIdExtra);
												
						$.each(m.CRs, function(index,cr) {
							myMeeting.proposedChanges.push(new ProposedChange(myMeeting,cr.matchCRId,cr.proposedDate,cr.acceptedState,cr.requestedByTeam,cr.requestedOn,cr.finallyChosen == '1' ? true : false));
							
						});
						myMeetings.push(myMeeting);						
					});	
					self.availableMeetings(myMeetings);
					
					//Counterteam selected by URL
					if (typeof self.requestedCounterTeamName !== 'undefined') {
						self.availableMeetings().forEach(function(meeting) {
							if (meeting.hTeam == self.requestedCounterTeamName || meeting.oTeam == self.requestedCounterTeamName) {
								self.chosenMeeting(meeting);
							}
						});
					}
									
				});
				
			}
		});
			
		self.addNewProposal = function() {
			console.log("Adding new proposal...");			
			self.chosenMeeting().proposedChanges.push(giveNewProposedChange(self.chosenTeam().teamName,self.chosenTeam().teamName,self.chosenMeeting().hTeam,self.chosenMeeting().oTeam));
		};
		

		self.addNewComment= function() {
			console.log("Adding new comment...");
			self.chosenMeeting().comments.push(giveNewComment(self.chosenTeam().teamName,moment().format("YYYYMMDDHHmm"),self.newCommentText()));
			self.newCommentText("");
		};
		
		self.saveAndSend = function(sendMail) {
			var vmjs = $.parseJSON(ko.toJSON(self));
			var resultObject = {"chosenMeeting": vmjs.chosenMeeting,"sendMail":sendMail};
			var posting = $.post("api/saveMeetingChangeRequest",resultObject, function(data) {
				if (data.processedSuccessfull) {
					$resultText = (sendMail ? "Verplaatsings aanvraag bewaard en e-mail verzonden naar tegenpartij ("+data.mailTo+")" : "Verplaatsings aanvraag bewaard.");
					self.lastSuccess($resultText);
				}else {
					self.lastError("Problemen bij het bewaren van deze verplaatsings aanvraag.");
				}

			});
			
			posting.fail(function(data) {
				self.lastError("Problemen bij het bewaren van deze verplaatsings aanvraag.");
			});			
			
		}
		self.save = function() {
			self.saveAndSend(false);			
		};

		self.send = function() {
			self.saveAndSend(true);
		};
			
		self.availableStatusses = ko.computed(function() {
			return ['A','B','C'];
		},this);
		
	};	
	
	
	var vm = new myViewModel();	
	ko.applyBindings(vm);		

})(ko, jQuery);
