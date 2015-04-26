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

	function playerComparatorIndexInsideTeam(teamType) {
		return function(a, b) {
			return b.indexInsideTeam(teamType) - a.indexInsideTeam(teamType);
		}
	}


	function playerComparatorMaxFixedRankingConvertedToIndexInsideTeam(teamType) {
		return function(a, b) {
			return b.maxFixedRankingConvertedToIndexInsideTeam(teamType) - a.maxFixedRankingConvertedToIndexInsideTeam(teamType);
		}
	}


	function playerComparatorBasedSortingValue() {
		return function(a, b) {
			if (a.sortingValue < b.sortingValue) return -1;
			if (a.sortingValue > b.sortingValue) return 1;
			return 0;
		}
	}

	
	function playerGenderFilter(myGender) {
		return function(a) {
			return a.gender == myGender;
		}
	}
	
	function teamEventFilter(myEvent) {
		return function(a) {
			return a.event == myEvent;
		}
	}

	function teamEventFilterIncludingLiga(myEvent) {
		return function(a) {
			return a.event == 'LI' || a.event == myEvent;
		}
	}


	function teamDevisionEqualOrHigherFilter(myDevision) {
		return function(a) {
			return a.devision <= myDevision;
		}
	}
	
	function teamContainingPlayerInBaseTeam(myVblId) {
		return function(a) {
			return a.baseTeam.indexOf(myVblId) >= 0;
		}
	}	

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
	
	
	var Player = function(firstName,lastName,vblId,gender,fixedRanking,ranking,type) {
		this.firstName = firstName;
		this.lastName = lastName;
		this.fullName = this.firstName  + ' ' + this.lastName;
		this.vblId = vblId;
		this.gender = gender;
		this.fixedRanking  = fixedRanking;
		this.ranking = ranking;
		this.type = type;
		this.sortingValue = ((this.gender == 'M') ? "A" : "B")+this.fullName;
		
		this.rankingToIndex = function(ranking) {
			switch(ranking) {
				case "A": 
					return 20;
				case "B1":
					return 10;
				case "B2":
					return 6;
				case "C1":
					return 4;
				case "C2":
					return 2;
				case "D":
					return 1;				
			}			
		}

		this.indexToRanking = function(index) {
			switch(index) {
				case 20: 
					return "A";
				case 10:
					return "B1";
				case 6:
					return "B2";
				case 4:
					return "C1";
				case 2:
					return "C2";
				case 1:
					return "D";				
			}			
		}		
		//Some definitions			
		this.fixedRankingSingle = fixedRanking[0].toUpperCase();
		this.fixedRankingDouble = fixedRanking[1].toUpperCase();
		this.fixedRankingMix = fixedRanking[2].toUpperCase();

		this.rankingSingle = ranking[0].toUpperCase();
		this.rankingDouble = ranking[1].toUpperCase();
		this.rankingMix = ranking[2].toUpperCase();


		this.rankingOrFixedRanking = function(gameType,isFixed) {
			if(gameType == 'HE' || gameType == 'DE')
				return isFixed ? this.fixedRankingSingle : this.rankingSingle;			

			if(gameType == 'HD' || gameType == 'DD')
				return isFixed ? this.fixedRankingDouble : this.rankingDouble;			

			if(gameType == 'GD')
				return isFixed ? this.fixedRankingMix : this.rankingMix;						
		}

		this.ranking = function(gameType) {
			return this.rankingOrFixedRanking(gameType,false);
		}

		this.fixedRanking = function(gameType) {
			return this.rankingOrFixedRanking(gameType,true);
		}
		
		this.index = function(gameType) {
			return this.rankingToIndex(this.ranking(gameType));
		}

		this.fixedIndex = function(gameType) {
			return this.rankingToIndex(this.fixedRanking(gameType));
		}
		
		this.indexInsideTeam = function(teamType) {
			switch(teamType) {
				case "H":
					return this.index("HE") + this.index("HD");
				case "D":
					return this.index("DE") + this.index("DD");
				case "G":
					switch(this.gender) {
						case "M":
							return this.index("HE") + this.index("HD") + this.index("GD");
						case "F":
							return this.index("DE") + this.index("DD") + this.index("GD");
					}
			}
		}

		
		this.fixedIndexInsideTeam = function(teamType) {
			switch(teamType) {
				case "H":
					return this.fixedIndex("HE") + this.fixedIndex("HD");
				case "D":
					return this.fixedIndex("DE") + this.fixedIndex("DD");
				case "G":
					switch(this.gender) {
						case "M":
							return this.fixedIndex("HE") + this.fixedIndex("HD") + this.fixedIndex("GD");
						case "F":
							return this.fixedIndex("DE") + this.fixedIndex("DD") + this.fixedIndex("GD");
					}
			}
		}
		
		this.maxFixedRankingConvertedToIndexInsideTeam = function(teamType) {
			//to support ART53.2
			switch(teamType) {
				case "H":
					return Math.max(this.fixedIndex("HE"),this.fixedIndex("HD"));
				case "D":
					return Math.max(this.fixedIndex("DE"),this.fixedIndex("DD"));
				case "G":
					switch(this.gender) {
						case "M":
							return Math.max(this.fixedIndex("HE"),this.fixedIndex("HD"),this.fixedIndex("GD"));
						case "F":
							return Math.max(this.fixedIndex("DE"),this.fixedIndex("DD"),this.fixedIndex("GD"));
					}
			}			
		}
		
		this.maxFixedRankingInsideTeam = function(teamType) {
			return this.indexToRanking(this.maxFixedRankingConvertedToIndexInsideTeam(teamType));
		}
		
		this.myFixedIndex = this.fixedIndexInsideTeam("H");
		
		
		this.fixedRankingLayout = function(teamType) {
			switch (teamType) {
				case "H": 
					return this.fixedRankingSingle + ", " + this.fixedRankingDouble +" = "+this.fixedIndexInsideTeam(teamType) ;
				case "D":
					return this.fixedRankingSingle + ", " + this.fixedRankingDouble +" = "+this.fixedIndexInsideTeam(teamType);					
				case "G": 
					return this.fixedRankingSingle + ", " + this.fixedRankingDouble + ", " +this.fixedRankingMix +" = "+this.fixedIndexInsideTeam(teamType);					
			}
		}

		this.rankingLayout = function(teamType) {
			switch (teamType) {
				case "H": 
					return this.rankingSingle + ", " + this.rankingDouble;
				case "D":
					return this.rankingSingle + ", " + this.rankingDouble;					
				case "G": 
					return this.rankingSingle + ", " + this.rankingDouble + ", " +this.rankingMix;					
			}
		}
		
		//Validation rules
		this.isAllowedToPlayInTeamGameTypeBasedOnGender = function(teamType) {
			return ((teamType == 'H' && this.gender == 'F') || (teamType == 'D' && this.gender == 'M')) ? false : true;
		}				

	};
	

	var Game = function(id, involvedNumberOfPlayers, x) {
		var self=this;
		this.playersInGame = ko.observableArray(x);		
		this.id = id;
		this.gameType=id.substring(0,2);
		this.gameTypeIndex = id.substring(2,3);
		
		//Duplicate some stuff to make validation easier
		this.playersInGame.id=id;
		this.playersInGame.gameType = this.gameType;
		this.playersInGame.gameTypeIndex = this.gameTypeIndex;
		
		
		this.involvedNumberOfPlayers = involvedNumberOfPlayers;					
				
		this.allowMorePlayers = ko.computed(function() {
			return this.playersInGame().length < this.involvedNumberOfPlayers;
		},this);
		
		this.isFull = function() {
			return this.playersInGame().length == this.involvedNumberOfPlayers;
		}
		
		this.totalIndex = ko.computed(function() {			
			var totalI=0;				
			var myGameType= this.gameType;
			$.each(this.playersInGame(), function(index,player) {
				totalI += player.index(myGameType);
			});
			return totalI;
		},this);
		
		this.totalIndexWithLayout = ko.computed(function() {
			if (this.isFull()) {
				return this.totalIndex();				
			} else if(this.playersInGame().length > 0 )  {
				return "("+this.totalIndex()+")";
			} else {
				return "-";
			}			
		},this);	
		
		this.gameCss = ko.computed(function() {
			return this.involvedNumberOfPlayers > 1 ? "doubleGame" : "singleGame";
		},this);

		this.gameTitleCss = ko.computed(function() {
			return this.involvedNumberOfPlayers > 1 ? "doubleTitleGame" : "singleTitleGame";
		},this);	
		
		this.removePlayer = function(p) {
			self.playersInGame.remove(p);
		};	
		
		this.allowMorePlayersOfGender = function(gender) {
			return ((this.gameType == 'HE' || this.gameType == 'HD') && gender=='M') ||
				   ((this.gameType == 'DE' || this.gameType == 'DD') && gender=='F') ||
				   (this.gameType == 'GD' && this.playersInGame().length == 0) ||			
				   (this.gameType == 'GD' && this.playersInGame().length == 1 && this.playersInGame()[0].gender=='M' && gender=='F') ||
				   (this.gameType == 'GD' && this.playersInGame().length == 1 && this.playersInGame()[0].gender=='F' && gender=='M')
		}
				
	};			
	
	var Team = function(teamName,event,devision,series,baseTeamVblIds,captainName) {
		this.teamName = teamName;
		this.event = event;
		this.devision = devision;
		this.series = series;
		this.baseTeamVblIds = baseTeamVblIds;
		this.teamType = teamName.slice(-1);
		this.teamNumber  = teamName.slice(-2,-1);
		
		this.playersInBaseTeam = ko.observableArray();
		this.playersInTeam = ko.observableArray();
		this.effectivePlayers = ko.observableArray();
		this.games = ko.observableArray();
		this.meetings = ko.observableArray();
		this.captainName = ko.observable(captainName);
			
		this.setGames = function(games) {
			this.games(games);
		}
	
		this.isMultiSexTeam = function() {
			if (this.teamType=="G") {
				return true;
			} else {
				return false;
			}
		}
		
		this.uniquePlayersInTeam = ko.computed(function() {
			var result=[];
			var vblIds=[];
			$.each(this.games(), function(i,game) {
				$.each(game.playersInGame(), function(j,player) {
					if (vblIds.indexOf(player.vblId) == -1) {
						vblIds.push(player.vblId);
						result.push(player);
					}
				});
			});	
			return result;
					
		},this);
		
		
		this.effectivePlayersInTeam = ko.computed(function() {
			var sortedM = this.uniquePlayersInTeam().filter(playerGenderFilter('M')).sort(playerComparatorIndexInsideTeam(this.teamType));
			var sortedF = this.uniquePlayersInTeam().filter(playerGenderFilter('F')).sort(playerComparatorIndexInsideTeam(this.teamType));
			var result = [];
			
			switch(this.teamType) {
				case "H" : 						
						result = sortedM.slice(0,4);
						break;
				case "D" : 
						result =  sortedF.slice(0,4);
						break;
				case "G" : 
						result = sortedM.slice(0,2);
						$.each(sortedF.slice(0,2), function(index,player) {
							result.push(player);
						});		
						break;			
			}							
			return result.sort(playerComparatorBasedSortingValue());
		},this);	
		
		
		
		this.isAllowedToPlayBasedOnBaseTeamSubscribtion = function(chosenClub,myPlayer) {
			//console.log("Checking if player "+myPlayer.fullName+" is allowed to play in "+this.teamName+" (devision:"+this.devision +",series:"+this.series+",teamnumber:"+this.teamNumber+")");
			
			//ART51.4 : If the club has only 1 team in competition for this event, all players of this club can play in this team
			if (this.teamNumber == 1)// all players from the club can play in the first team
				return true;
			
			//TODO ART51.5
			
			//STEP1: filter teams from same event G,H,D
			//STEP2: filter teams with equal higher devision
			//STEP3: filter teams where baseTeam contains myPlayer.vblId
			// If such a team is found that is NOT equal to this team, myPlayer is NOT allowed to play	
			
			var foundTeams = chosenClub.teams
							.filter(teamEventFilterIncludingLiga(this.event))
							.filter(teamDevisionEqualOrHigherFilter(this.devision))
							.filter(teamContainingPlayerInBaseTeam(myPlayer.vblId));
				
				
			return 	!(foundTeams.length>0 && foundTeams[0].teamName != this.teamName);				
		}
		
		this.isAllowedToPlayBasedOnBaseMaxPlayerIndex = function(myPlayer) {
			if (this.teamNumber == 1)// all players from the club can play in the first team
				return true;			
			var sortedBasePlayerWithSameGender = this.playersInBaseTeam().filter(playerGenderFilter(myPlayer.gender)).sort(playerComparatorMaxFixedRankingConvertedToIndexInsideTeam(this.teamType));
			//console.log("Checking isAllowedToPlayBasedOnBaseMaxPlayerIndex for "+myPlayer.fullName);
			return myPlayer.maxFixedRankingConvertedToIndexInsideTeam(this.teamType) <= sortedBasePlayerWithSameGender[0].maxFixedRankingConvertedToIndexInsideTeam(this.teamType);
		}
		

		this.baseTeamIndex = ko.computed(function() {
			var v=0;
			var self=this;
			$.each(this.playersInBaseTeam(), function(i,player) {
				v += player.fixedIndexInsideTeam(self.teamType);	
			})
			return v;			
			
		},this);
		
		this.isFull = ko.computed(function() {
			var isFull =  true;
			$.each(this.games(), function(i,game) {
				isFull = isFull && game.isFull();
			});
			return isFull;			
			//return true;
		},this);
		
		this.effectiveTeamIndex = ko.computed(function() {			
			var v=0;
			var self=this;
			$.each(this.effectivePlayersInTeam(), function(i,player) {
				v += player.fixedIndexInsideTeam(self.teamType);	
			});
			return v;			
		},this);
		
		
		this.rankingLayout = function()  {
			switch(this.teamType) {
				case "H" : 						
						return "E,D"
				case "D" : 
						return "E,D";
				case "G" : 
					return "E,D,G";
			}				
		}	
		
		this.teamTypeLayout = function()  {
			switch(this.teamType) {
				case "H" : 						
						return "Heren";
				case "D" : 
						return "Dames";
				case "G" : 
					return "Gemengd";
			}				
		}	
		
	}
	
	function initialGamesEmpty() {
		return [];
	};
	
	function initialGameMix()  {
			return [
		new Game("HD",  2 , [
		]),
		new Game("DD",  2, [
		]),
		new Game("HE1", 1, [
		]),
		new Game("HE2", 1, [
		]),
		new Game("DE1", 1, [
		]),
		new Game("DE2", 1, [
		]),
		new Game("GD1", 2, [
		]),
		new Game("GD2", 2, [
		])
		];
	}
	
	function initialGamesMen() {
		return [
		new Game("HD1",  2 , [
		]),
		new Game("HD2",  2, [
		]),
		new Game("HD3", 2, [
		]),
		new Game("HD4", 2, [
		]),
		new Game("HE1", 1, [
		]),
		new Game("HE2", 1, [
		]),
		new Game("HE3", 1, [
		]),
		new Game("HE4", 1, [
		])
		];	
	}

	function initialGamesWomen() {
		return [
		new Game("DD1",  2 , [
		]),
		new Game("DD2",  2, [
		]),
		new Game("DD3", 2, [
		]),
		new Game("DD4", 2, [
		]),
		new Game("DE1", 1, [
		]),
		new Game("DE2", 1, [
		]),
		new Game("DE3", 1, [
		]),
		new Game("DE4", 1, [
		])
		];	
	}

	function initialGenderButtons() {
		return [ 
		new Button('Man/Vrouw','ALL',true),
		new Button('Man','M',false),
		new Button('Vrouw','F',false)
		];
	};
	
	
	function initialPlayerTypeButtons() {
		return [ 
		 new Button('Allen','ALL',false), 
         new Button('Competitie','C',true),
         new Button('Recreant','R',false),
         new Button('Jeugd','J',false)
		];
	};	

	
				
	function myViewModel(games) {
		var self = this;
		self.games = ko.observableArray(games);			
		self.chosenClub = ko.observable();
		self.chosenTeamName = ko.observable();							
		self.chosenTeam = ko.observable();
		self.chosenMeeting = ko.observable();
		self.sampleClubs = ko.observable();
		self.dbload = ko.observable();
		
		self.availablePlayers = ko.observableArray();
		self.notAllowedPlayersOtherBaseTeam = ko.observableArray();
		self.notAllowedPlayersMaxPlayerRanking = ko.observableArray();
		self.lastError = ko.observable();
		
		self.trash = ko.observableArray([]);
		self.trash.id = "trash";
		self.selectedGameId =ko.observable("");
		self.genderButtons = ko.observableArray(initialGenderButtons());
		self.selectedGenderButton = ko.observable(self.genderButtons()[0]);
		self.selectGenderButton = function(button) {
			//console.log("Selecting "+button.name);
			if (self.selectedGenderButton()){
				 self.selectedGenderButton().selected(false); 
			 }
			
			self.selectedGenderButton(button);
			self.selectedGenderButton().selected(true);
		};

		self.playerTypeButtons = ko.observableArray(initialPlayerTypeButtons());
		self.selectedPlayerTypeButton = ko.observable(self.playerTypeButtons()[1]);
		self.selectPlayerTypeButton = function(button) {
			//console.log("Selecting "+button.name);
			if (self.playerTypeButtons()){
				 self.selectedPlayerTypeButton().selected(false); 
			 }
			
			self.selectedPlayerTypeButton(button);
			self.selectedPlayerTypeButton().selected(true);
		};


		self.selectedGame = ko.computed(function() {
			if (self.games()) {				
				var x = self.games().filter(function(g) {
					 return g.id == self.selectedGameId();
				});
				return x[0];
			}
		},self);


		self.filteredGenderButtons = ko.computed(function() {			
			return ko.utils.arrayFilter(self.genderButtons(), function(genderButton) {
				return (typeof self.selectedGame() != 'undefined') && self.selectedGame().allowMorePlayersOfGender(genderButton.buttonValue());
			});
			
		},self);


		//LOAD CLUBS/TEAMS
		$.get("api/clubsAndTeams", function(data) {
			self.sampleClubs(data.clubs);
			self.dbload(new DBLoad(data.DBLOAD[0].date));
		});
		
		self.filteredAvailablePlayers = ko.computed(function() {
			//return this.availablePlayers();
			return ko.utils.arrayFilter(self.availablePlayers(), function(player) {
				return (self.selectedGenderButton().buttonValue() == 'ALL' || player.gender == self.selectedGenderButton().buttonValue()) &&
					   (self.selectedPlayerTypeButton().buttonValue() == 'ALL' || player.type == self.selectedPlayerTypeButton().buttonValue());
			});
			
		},self);
			

		self.resetForm = function() {
			console.log("Resetting form...");
			//Load the games this teamType
			switch(self.chosenTeam().teamType) {
				case "G": 
					self.games(initialGameMix());break;
				case "H":
					self.games(initialGamesMen());break;
				case "D":
					self.games(initialGamesWomen());break;
			}			
			//Attach loaded games to this team
			self.chosenTeam().setGames(self.games());			
		};
	

		self.print2pdf = function() {
			var vmjs = $.parseJSON(ko.toJSON(self));
			var resultObject = {"games":vmjs.games, "chosenMeeting": vmjs.chosenMeeting, "chosenTeam": vmjs.chosenTeam};
			var result = JSON.stringify(resultObject);
			$.get("api/logEvent/print2pdf/"+encodeURIComponent(self.chosenTeam().teamName), function(data) {
				console.log("print2pdf logged");
			});
			$("#iPrint").empty();
			$("#iPrint").contents().find('html').html("<form id='print2PdfForm' method='post' action='print2Pdf.php'><input id='print2PdfFormData' type='hidden' name='data'/></form>");
			$("#iPrint").contents().find("#print2PdfFormData").val(result);
			$("#iPrint").contents().find("#print2PdfForm").submit();
		};

		self.chosenClub.subscribe(function(newClub) {
			self.chosenMeeting(null);
			self.games(null);
			self.chosenTeamName(null);
			self.availablePlayers.removeAll();
			self.notAllowedPlayersOtherBaseTeam.removeAll();
			self.notAllowedPlayersMaxPlayerRanking.removeAll();
			self.chosenTeam(null);										
		});
		
			
		//START PRINT LOGGING
		var beforePrint = function() {
			$.get("api/logEvent/print/"+encodeURIComponent(self.chosenTeam().teamName), function(data) {
				console.log("print logged");
			});
		};

		if (window.matchMedia) {
			var mediaQueryList = window.matchMedia('print');
			mediaQueryList.addListener(function(mql) {
				if (mql.matches) {
					beforePrint();
				}
			});
		}
		window.onbeforeprint = beforePrint;
		//END PRINT LOGGING
			
								
		self.chosenTeamName.subscribe(function(newTeam) {			
			if (newTeam !== undefined && newTeam !== null) {
				console.log("Team initing...");
				//Init new team
				self.chosenTeam(new Team(newTeam.teamName,newTeam.event,newTeam.devision,newTeam.series,newTeam.baseTeam,newTeam.captainName));
				
				//Make not allowedPlayers invisible when chosing new team
				$("#nonplayerstable").hide();
				
				//Reset player gender filter
				self.selectGenderButton(self.genderButtons()[0]);
								
				//Load the games this teamType
				switch(self.chosenTeam().teamType) {
					case "G": 
						self.games(initialGameMix());break;
					case "H":
						self.games(initialGamesMen());break;
					case "D":
						self.games(initialGamesWomen());break;
				}
				
				//Attach loaded games to this team
				self.chosenTeam().setGames(self.games());
				
				
				self.availablePlayers.removeAll();				
				self.notAllowedPlayersOtherBaseTeam.removeAll();
				self.notAllowedPlayersMaxPlayerRanking.removeAll();
				
				
				//LOAD PLAYERS FOR THIS CLUB/TEAM
				$.get("api/teamAndClubPlayers/"+encodeURIComponent(newTeam.teamName), function(data) {
					//First set the base team because it has an influence if players are allowed or not
					$.each(data.players, function(index,p) {
						var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking,p.type);						
						//Set baseteam players
						if (self.chosenTeam().baseTeamVblIds.indexOf(p.vblId) >=0) {
							console.log("Adding "+myPlayer.fullName+" as a baseTeamPlayer of team "+self.chosenTeam().teamName);
							self.chosenTeam().playersInBaseTeam.push(myPlayer);							
						}						
					});

					//Load players and check if they can play in this team
					$.each(data.players, function(index,p) {
						var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking,p.type);						
						if (myPlayer.isAllowedToPlayInTeamGameTypeBasedOnGender(self.chosenTeam().teamType)) {
							
							if (self.chosenTeam().isAllowedToPlayBasedOnBaseTeamSubscribtion(self.chosenClub(),myPlayer)) {								
								if(self.chosenTeam().isAllowedToPlayBasedOnBaseMaxPlayerIndex(myPlayer)) {
									self.availablePlayers.push(myPlayer);
								} else {
									self.notAllowedPlayersMaxPlayerRanking.push(myPlayer);
								}								
							} else {
								self.notAllowedPlayersOtherBaseTeam.push(myPlayer);
							}							
						}												
					});
					console.log("Loading meetings...");
					//LOAD EVENTS LINKED TO THIS TEAM
					$.each(data.meetings, function(index,m) {
						//console.log("Adding new meeting "+m.dateTime);
						self.chosenTeam().meetings.push(new Meeting(m.hTeam,m.oTeam,m.dateTime,m.locationName));						
					});						
										
					//Sort the arrays of players thtat are shown in UI
					self.chosenTeam().playersInBaseTeam(self.chosenTeam().playersInBaseTeam().sort(playerComparatorBasedSortingValue()));
					self.availablePlayers(self.availablePlayers().sort(playerComparatorBasedSortingValue()));
					self.notAllowedPlayersOtherBaseTeam(self.notAllowedPlayersOtherBaseTeam().sort(playerComparatorBasedSortingValue()));
					self.notAllowedPlayersMaxPlayerRanking(self.notAllowedPlayersMaxPlayerRanking().sort(playerComparatorBasedSortingValue()));
					
					//self.chosenTeam().calculateAndSetBaseTeamIndex();
				},'json');										
				
			}
		});
		
		//////////////////////////////////////////////////////
		//BEGIN Validation utilities
		//////////////////////////////////////////////////////
		self.numberOfGamesPerPlayerPerGameType = function(myPlayer,myGameType)  {
			var gameCount=0;
			$.each(self.games(), function(index1,game) {					
				if (game.gameType == myGameType) {
					$.each(game.playersInGame(), function(index2,otherPlayer) {
						if (otherPlayer.vblId == myPlayer.vblId)
							gameCount++;
					});				
				}
			});
			return gameCount;
		}
		
		self.giveOrderedIndexOfDefinedGamesPerGameTypeAndAddPlayerOnPositionXAndIgnoreGameY = function(myGameType,myPlayer,indexPositionX, indexPositionY) {
			var result=[];
			$.each(self.games(), function(position,game) {					
				if (game.gameType == myGameType ) {					
					 if (game.gameTypeIndex == indexPositionX) {						 						
						if(myGameType == 'HE' || myGameType == 'DE') {
							result.push(myPlayer.index(myGameType));
						} else if (game.playersInGame().length == 1){ // toghether with the given myPlayer this will form a double-couple
							result.push(myPlayer.index(myGameType) + game.playersInGame()[0].index(myGameType));
						}						
					 } else if ((game.gameTypeIndex !== indexPositionY) && game.isFull()){
						if(myGameType == 'HE' || myGameType == 'DE') { 
							result.push(game.playersInGame()[0].index(myGameType));
						} else {
							result.push(game.playersInGame()[0].index(myGameType) + game.playersInGame()[1].index(myGameType));
						}
					 }					
				}
			});					
			return result;	
		}


		
		self.isOrderedIndexArray = function(subjectArray) {			
			// Sort the array in a new array and check if that sorted array is exactly the same as the original one
			clonedSubjectArray = subjectArray.slice(0)
			clonedSubjectArray.sort(function(a, b){return b-a});//sort descending
			
			var result = true;
			$.each(subjectArray,function(index,x) {
				result = result && (x == clonedSubjectArray[index]);
			});
			return result;
			
		}
		
		//////////////////////////////////////////////////////
		//END Validation utilities
		//////////////////////////////////////////////////////
		
		this.verifyAssignments = function(arg,event,ui) {
			var player = arg.item;
			var targetGame = arg.targetParent;				
			var sourceGame = arg.sourceParent;
			var gameId= targetGame.id;
			var gameType = targetGame.gameType;
			var gameTypeIndex = targetGame.gameTypeIndex;
		
			
			var sourceGameType = (sourceGame && sourceGame.id) ? sourceGame.id.substring(0,2) : "xx";
			var sourceGameTypeIndex = (sourceGame && sourceGame.gameTypeIndex) ? sourceGame.gameTypeIndex : "xx";	
						
			var logError = function(msg,arg) {
				self.lastError(msg);
				arg.cancelDrop = true;
			};
			
			console.log("Validating drop of "+player.fullName+" in game:"+gameId);
			
			//VALIDATE GENDER
			if (gameType=="HE" && player.gender !== "M") {
				logError(gameType+" kan enkel gespeeld worden door een man.",arg);				
				return;
			} 

			if (gameType=="HD" && player.gender !== "M") {
				logError(gameType+" kan enkel gespeeld worden door twee mannen",arg);				
				return;
			} 
			if (gameType=="DE" && player.gender !== "F") {
				logError(gameType+" kan enkel gespeeld worden door een vrouw",arg);				
				return;
			} 
			if (gameType=="DD" && player.gender !== "F") {
				logError(gameType+" kan enkel gespeeld worden door twee vrouwen",arg);				
				return;
			} 
			
			if (gameType=="GD" && targetGame().length == 1 && player.gender == targetGame()[0].gender) {
				logError(gameType+" kan enkel gespeeld worden door combinatie man/vrouw",arg);
				return;
			} 
			
			//PLAYERS WITHIN A GAME MUST BE UNIQUE
			if (targetGame().length == 1 && player.vblId == targetGame()[0].vblId) {
				logError("1 titularis kan maar 1 maal in dezelfde wedstrijd ingevoerd worden",arg);
				return;				
			}
			
			//SAME PLAYER CAN ONLY PLAY TWO DOUBLE GAMES
			if ((gameType=="HD" || gameType == "DD") && (gameType != sourceGameType))  {				
				if (self.numberOfGamesPerPlayerPerGameType(player,gameType) == 2) {
					logError("Eénzelfde titularis kan slechts 2 dubbelwedstrijden spelen (C320 art.52.7)",arg);
					return;									
				}
			}

			//SAME PLAYER CAN ONLY PLAY ONE SINGLE OR ONE MIX GAME
			if ((gameType=="HE" || gameType == "DE" || gameType == "GD") && (gameType != sourceGameType))  {				
				if (self.numberOfGamesPerPlayerPerGameType(player,gameType) == 1) {
					logError("Eénzelfde speler/titularis kan slechts 1 "+gameType+" spelen (C320 art.52.7)",arg);
					return;									
				}
			}
						
			//GAMES MUST BE ORDERED FROM HIGHEST TO LOWEST INDEX
			var newOrder = self.giveOrderedIndexOfDefinedGamesPerGameTypeAndAddPlayerOnPositionXAndIgnoreGameY(gameType,player,gameTypeIndex,sourceGameTypeIndex);			
			if(!(self.isOrderedIndexArray(newOrder))) {
				if ((gameType=="HE" || gameType == "DE")) {
					logError("De titularissen voor de enkelwedstrijden "+gameType+" moeten gerangschikt staan van hoogste naar laagste enkel-index (C320 art. 52.8)",arg);
				} else if ((gameType=="HD" || gameType == "DD")) { 
					logError("De titularissen voor de dubbelwedstrijden "+gameType+" moeten gerangschikt staan van hoogste naar laagste samengestelde dubbel-index (C320 art. 52.11)",arg);
				} else {
					logError("De titularissen voor de gemengde wedstrijden "+gameType+" moeten gerangschikt staan van hoogste naar laagste samengestelde mix-index (C320 art. 52.11)",arg);
				}
				return;														
			}
		}
		
		this.verifyAssignmentsAfterMove = function(arg,event,ui) {
			var player = arg.item;
			var targetGame = arg.targetParent;				
			var sourceGame = arg.sourceParent;
			var gameId= targetGame.id;
			var gameType = targetGame.gameType;
			var gameTypeIndex = targetGame.gameTypeIndex;
			
			
			var logError = function(msg,arg) {
				self.lastError(msg);
			};

			// BASE-TEAM-INDEX >= EFFECTIVE-TEAM-INDEX  (exception: ART51.4 everybody can play in first team)
			if (self.chosenTeam().teamNumber != 1 && self.chosenTeam().effectiveTeamIndex() > self.chosenTeam().baseTeamIndex()) {
				logError("Teamindex van de effectieve ploeg mag de teamindex van papieren ploeg niet overschrijden (C320 art. 53.2)",arg);
				arg.targetParent.remove(arg.item);
				return;
			}
			
			//Reset error msg after a succesful drop
			self.lastError("");
			$("#error").hide();
			
		};
		
		
		//SUPPORT FOR SELECTING PLAYERS USING MODAL(touchscreen users)
		$('body').on('show.bs.modal','div.selectPlayersModal', function (event) {
		  var button = $(event.relatedTarget); 
		  var gameId = button.data('game-id');  	  
		  //console.log("Selected gameId in modal "+gameId);
		  self.selectedGameId(gameId);		
		  self.selectGenderButton(self.filteredGenderButtons()[0]);  		  
		});			  
			
		self.addPlayer = function(p) {
			console.log("Need to add "+p.fullName+" to game "+self.selectedGameId());			
			$('#selectPlayersModal').modal('hide');
			addPlayerArg = {
				item : p,
				targetParent: self.selectedGame().playersInGame,
				cancelDrop: false
			}				
			self.verifyAssignments(addPlayerArg,null,null);
			if (!(addPlayerArg.cancelDrop)) {
				self.selectedGame().playersInGame.push(p);
				self.verifyAssignmentsAfterMove(addPlayerArg,null,null);
			}
			
		};			
		
	};	
	
	
	var vm = new myViewModel(initialGamesEmpty());	
	ko.applyBindings(vm);		

})(ko, jQuery);
