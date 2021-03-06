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
					}, 10000));
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
			widget.date(moment(dateInKModel,"YYYYMMDDHHmm").toDate());
		}
	};
	
	debug = function (log_txt) {
		if (typeof window.console != 'undefined') {
			console.log(log_txt);
		}
	};


	function formatDate(d) {
		return moment(d).format("ddd DD MMM YYYY");
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
		this.requestedOn=ko.observable(requestedOn);
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

        this.proposalAcceptedStates = ko.observableArray(['-','NIET MOGELIJK','MOGELIJK']);

        this.acceptedState.subscribe(function(newAcceptedState){
            //Once chosen, it should not be possible to set it back to undefined.
            if (newAcceptedState!='-' && this.proposalAcceptedStates.indexOf("-") == 0) {
                this.proposalAcceptedStates.shift();
                this.acceptedState(newAcceptedState);
            }
        },this)
		
	}
	
	//Avoiding circular reference when saving to stringify to JSON
	ProposedChange.prototype.toJSON = function() {
		var copy = ko.toJS(this);
		delete copy.meeting;
		return copy;
	}	
		
		
	function giveNewProposedChange(meeting,requestedByTeam,hTeam,oTeam) {
		var newProposedChange =  new ProposedChange(meeting);
		newProposedChange.proposedDateTime(moment().hour(20).minutes(0).format("YYYYMMDDHHmm"));
        newProposedChange.requestedOn(moment().format("YYYYMMDDHHmm"));
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

    function teamByNameFilter(myTeamName) {
        return function(a) {
            return a.teamName == myTeamName;
        }
    }
    function dbStatusLayout(status) {
        return ($.trim(status)) ? status : "LAATST VASTGELEGD TIJDSTIP"
    }
    function dbActionForLayout(actionFor) {
        return ($.trim(actionFor)) ? actionFor : "-"
    }


	var Meeting = function(chosenTeamName,hTeam,oTeam,dateTime,locationName,matchIdExtra,status,actionFor,hTeamComment, oTeamComment) {
		var self= this;
		this.chosenTeamName = chosenTeamName;
		this.hTeam = hTeam;
		this.oTeam = oTeam;
		this.dateTime = dateTime;
		this.locationName = locationName;
		this.matchIdExtra = matchIdExtra;
		this.date = buildDateTime(dateTime);
        this.counterTeamName = this.chosenTeamName == this.hTeam ? this.oTeam : this.hTeam;
        this.hTeamComment = ko.observable(hTeamComment);
        this.oTeamComment = ko.observable(oTeamComment);
		
		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
		this.proposedChanges = ko.observableArray();
		this.comments = ko.observableArray();
        this.dbStatus = ko.observable(dbStatusLayout(status));
		this.dbActionFor = ko.observable(dbActionForLayout(actionFor));
		this.status = ko.computed(function(){
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
            //It should be  possible to add new proposals as long as status!=OVEREENKOMST
            return this.status() != 'OVEREENKOMST';
		},this);

        this.isSaveAndSendAllowed = ko.computed(function(){
            //All proposals from counterTeam must be answered before saving
            var unAnsweredProposals=this.proposedChanges().filter(proposalAcceptedStateFilter('-'));
            return unAnsweredProposals.filter(proposalRequestedByFilter(this.counterTeamName)).length == 0;
        },this);

        this.finalDateTime = ko.computed(function() {
            if (this.proposedChanges().filter(proposalFinallyChosenStateFilter(true)).length > 0) {
                return this.proposedChanges().filter(proposalFinallyChosenStateFilter(true))[0].proposedDateTimeLayout;
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
		
		self.requestedHomeTeamName = getUrlParameter("hteam");
		self.requestedOutTeamName = getUrlParameter("oTeam");
		
		//LOAD CLUBS/TEAMS
		$.get("verplaatsing/clubAndTeams", function(data) {
			self.sampleClubs(data.clubs);
			
			//Teamname selected by URL
			if (typeof self.requestedHomeTeamName !== 'undefined') {
				data.clubs.forEach(function(club) {//Should only contain one club
					club.teams.forEach(function(team) {
						if (team.teamName == self.requestedHomeTeamName || team.teamName == self.requestedOutTeamName) {
							self.chosenClub(club);
							self.chosenTeam(team);
						}
					});
				});
			}
            self.chosenClub(self.sampleClubs()[0]);

		});

		self.resetForm = function() {
			//console.log("Resetting form...");
		};

		self.chosenClub.subscribe(function(newClub) {
			self.chosenMeeting(null);
			self.availableMeetings.removeAll();
		});
		
		self.chosenTeam.subscribe(function(newTeam) {			
			if (newTeam !== undefined && newTeam !== null) {
				//LOAD EVENTS FOR THIS CLUB/TEAM
				self.chosenMeeting(null);
				$.get("verplaatsing/meetingAndMeetingChangeRequest/"+encodeURIComponent(self.chosenClub().clubName)+"/"+encodeURIComponent(newTeam.teamName), function(data) {
					var myMeetings = [];
					$.each(data.meetings, function(index,m) {
						var myMeeting = new Meeting(self.chosenTeam().teamName,m.hTeam,m.oTeam,m.dateTime,m.locationName,m.matchIdExtra,m.status, m.actionFor, m.hTeamComment, m.oTeamComment);
												
						$.each(m.CRs, function(index,cr) {
							myMeeting.proposedChanges.push(new ProposedChange(myMeeting,cr.matchCRId,cr.proposedDate,cr.acceptedState,cr.requestedByTeam,cr.requestedOn,cr.finallyChosen == '1' ? true : false));
							
						});
						myMeetings.push(myMeeting);						
					});	
					self.availableMeetings(myMeetings);
					
					//Counterteam selected by URL
					if (typeof self.requestedHomeTeamName !== 'undefined') {
						self.availableMeetings().forEach(function(meeting) {
							if (meeting.hTeam == self.requestedHomeTeamName && meeting.oTeam == self.requestedOutTeamName) {
								self.chosenMeeting(meeting);
                                //Once selected and viewed, we don't want this to influence further selection any more.
                                self.requestedHomeTeamName = undefined;
                                self.requestedOutTeamName = undefined;
							}
						});
					}
									
				});
				
			}
		});
			
		self.addNewProposal = function() {
			self.chosenMeeting().proposedChanges.push(giveNewProposedChange(self.chosenTeam().teamName,self.chosenTeam().teamName,self.chosenMeeting().hTeam,self.chosenMeeting().oTeam));
		};
		

		self.addNewComment= function() {
			self.chosenMeeting().comments.push(giveNewComment(self.chosenTeam().teamName,moment().format("YYYYMMDDHHmm"),self.newCommentText()));
			self.newCommentText("");
		};
		
		self.saveAndSend = function() {
			var vmjs = $.parseJSON(ko.toJSON(self));
			var resultObject = {"chosenMeeting": vmjs.chosenMeeting,"sendMail":true};

            $('#saveAndSend').button('loading');
            var posting = $.ajax({
                method:"POST",
                url:"verplaatsing/saveMeetingChangeRequest",
                data: JSON.stringify({"chosenMeeting": vmjs.chosenMeeting,"sendMail":true}),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            posting.done(function(data) {
                $('#saveAndSend').button('reset');
                if (data.processedSuccessfull) {
                    $resultText = "Verplaatsings aanvraag bewaard en e-mail verzonden naar : " + data.mailTo ;
                    self.chosenMeeting().dbStatus(dbStatusLayout(data.status));
                    self.chosenMeeting().dbActionFor(dbActionForLayout(data.actionFor));
                    self.lastSuccess($resultText);
                } else {
                    self.lastError("Problemen bij het bewaren van deze verplaatsings aanvraag.");
                }
            });

			posting.fail(function(data) {
                $('#saveAndSend').button('reset');
				self.lastError("Problemen bij het bewaren van deze verplaatsings aanvraag.");
			});			
			
		}

		self.send = function() {
			self.saveAndSend();
		};

        self.giveSaveAndSendButtonText = ko.computed(function(){
            if (this.chosenMeeting() && this.chosenMeeting().status() == 'OVEREENKOMST') {
                return "Bewaar en verstuur naar PBO en "+this.chosenMeeting().counterTeamName;
            } else if (this.chosenMeeting()){
                return "Bewaar en verstuur naar "+this.chosenMeeting().counterTeamName;
            }

        },this);

        self.giveAddNewProposalButtonText = ko.computed(function(){
            if (this.chosenMeeting() && this.chosenMeeting().proposedChanges().length>0) {
                return "Bijkomend tijdstip toevoegen";
            } else {
                return "Verplaatsingsaanvraag toevoegen";
            }

        },this);

        self.goToOpenRequest = function(data) {
            var hTeamNameArray = self.chosenClub().teams.filter(teamByNameFilter(data.hTeamName));
            var clubTeam;
            if (hTeamNameArray.length>0) {
                clubTeam = hTeamNameArray[0];
            } else {
                var oTeamNameArray = self.chosenClub().teams.filter(teamByNameFilter(data.oTeamName));
                clubTeam = oTeamNameArray[0];//must be one
            }
            self.requestedHomeTeamName = data.hTeamName;
            self.requestedOutTeamName = data.oTeamName;
            self.chosenTeam(clubTeam);
        }
        self.selectTeamCaption = function() {
            return (self.chosenClub().openRequests.length > 0) ? "Ontmoetingen die actie vereisen van "+self.chosenClub().clubName: "Selecteer...";
        }

	};	
	
	
	var vm = new myViewModel();	
	ko.applyBindings(vm);		

})(ko, jQuery);
