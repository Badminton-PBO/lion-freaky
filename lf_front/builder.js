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


	function playerComparatorFixedIndexInsideTeam(teamType) {
		return function(a, b) {
			return b.fixedIndexInsideTeam(teamType) - a.fixedIndexInsideTeam(teamType);
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
	
	var Meeting = function(hTeam,oTeam,dateTime,locationName) {
		this.hTeam = hTeam;
		this.oTeam = oTeam;
		this.dateTime = dateTime;
		this.locationName = locationName;
		this.date = new Date(dateTime.substring(0,4), dateTime.substring(4,6)-1,dateTime.substring(6,8), dateTime.substring(8,10), dateTime.substring(10,12));
		
		this.fullMeetingLayout = formatDate(this.date) + " : " + this.hTeam + "-" + this.oTeam;
		this.dateLayout = formatDate(this.date);
		this.hourLayout = formatHour(this.date);
		
	}	

	
	var Player = function(firstName,lastName,vblId,gender,fixedRanking,ranking) {
		this.firstName = firstName;
		this.lastName = lastName;
		this.fullName = this.lastName + ' ' + this.firstName ;
		this.vblId = vblId;
		this.gender = gender;
		this.fixedRanking  = fixedRanking;
		this.ranking = ranking;
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
					return this.rankingSingle + ", " + this.rankingDouble +" = "+this.indexInsideTeam(teamType);
				case "D":
					return this.rankingSingle + ", " + this.rankingDouble +" = "+this.indexInsideTeam(teamType);					
				case "G": 
					return this.rankingSingle + ", " + this.rankingDouble + ", " +this.rankingMix +" = "+this.indexInsideTeam(teamType);					
			}
		}
		
		//Validation rules
		this.isAllowedToPlayInTeamGameTypeBasedOnGender = function(teamType) {
			return ((teamType == 'H' && this.gender == 'F') || (teamType == 'D' && this.gender == 'M')) ? false : true;
		}				

	};
	

	var Game = function(id, involvedNumberOfPlayers, x) {
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
		
							
				
	};			
	
	var Team = function(teamName,event,devision,series,baseTeamVblIds) {
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
			
		this.setGames = function(games) {
			this.games(games);
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
			if ((this.teamNumber == 1) && chosenClub.teams.filter(teamEventFilter(this.event)).length ==1)// all players from the club can play in the first team
				return true;
			
			//TODO ART51.5
			
			//STEP1: filter teams from same event G,H,D
			//STEP2: filter teams with equal higher devision
			//STEP3: filter teams where baseTeam contains myPlayer.vblId
			// If such a team is found that is NOT equal to this team, myPlayer is NOT allowed to play	
			
			var foundTeams = chosenClub.teams
							.filter(teamEventFilter(this.event))
							.filter(teamDevisionEqualOrHigherFilter(this.devision))
							.filter(teamContainingPlayerInBaseTeam(myPlayer.vblId));
				
				
			return 	!(foundTeams.length>0 && foundTeams[0].teamName != this.teamName);				
		}
		
		this.isAllowedToPlayBasedOnBaseMaxPlayerIndex = function(myPlayer) {
			var sortedBasePlayerWithSameGender = this.playersInBaseTeam().filter(playerGenderFilter(myPlayer.gender)).sort(playerComparatorFixedIndexInsideTeam(this.teamType));
			//console.log("Checking isAllowedToPlayBasedOnBaseMaxPlayerIndex for "+myPlayer.fullName);
			//return true;
			return myPlayer.fixedIndexInsideTeam(this.teamType) <= sortedBasePlayerWithSameGender[0].fixedIndexInsideTeam(this.teamType);
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

	
				
	function myViewModel(games) {
		var self = this;
		self.games = ko.observableArray(games);			
		//self.chosenClub = ko.observable(sampleClubs[0]);
		self.chosenClub = ko.observable();
		self.chosenTeamName = ko.observable();							
		self.chosenTeam = ko.observable();
		self.chosenMeeting = ko.observable();
		self.sampleClubs = ko.observable();
		
		self.availablePlayers = ko.observableArray();
		self.notAllowedPlayersOtherBaseTeam = ko.observableArray();
		self.notAllowedPlayersOnBaseMaxPlayerIndex = ko.observableArray();
		self.lastError = ko.observable();
		
		self.trash = ko.observableArray([]);
		self.trash.id = "trash";

		self.chosenMeeting.subscribe(function(newMeeting) {
			console.log("Setting new meeting");
		});


		//LOAD CLUBS/TEAMS
		$.get("api/clubsAndTeams", function(data) {
			self.sampleClubs(data);
		});
		

		self.print2pdf = function() {
			var vmjs = $.parseJSON(ko.toJSON(self));
			var resultObject = {"games":vmjs.games, "chosenMeeting": vmjs.chosenMeeting, "chosenTeam": vmjs.chosenTeamName};
			var result = JSON.stringify(resultObject);
			$("#iPrint").contents().find("#print2PdfFormData").val(result);
			$("#iPrint").contents().find("#print2PdfForm").submit();
		};

		self.print2pdfX = function() {			
			console.log($("#printhtml").html());			
			console.log($("#printfull").html());
		};

						
		self.chosenTeamName.subscribe(function(newTeam) {
			if (newTeam !== undefined) {
				//Init new team
				self.chosenTeam(new Team(newTeam.teamName,newTeam.event,newTeam.devision,newTeam.series,newTeam.baseTeam));
				
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
				self.notAllowedPlayersOnBaseMaxPlayerIndex.removeAll();
				
				//LOAD PLAYERS FOR THIS CLUB/TEAM
				$.get("api/teamAndClubPlayers/"+encodeURIComponent(newTeam.teamName), function(data) {
					
					//First set the base team because it has an influence if players are allowed or not
					$.each(data.players, function(index,p) {
						var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking);						
						//Set baseteam players
						if (self.chosenTeam().baseTeamVblIds.indexOf(p.vblId) >=0) {
							console.log("Adding "+p.fullName+" as a baseTeamPlayer of team "+self.chosenTeam().teamName);
							self.chosenTeam().playersInBaseTeam.push(myPlayer);							
						}						
					});

					//Load players and check if they can play in this team
					$.each(data.players, function(index,p) {
						var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking);						
						if (myPlayer.isAllowedToPlayInTeamGameTypeBasedOnGender(self.chosenTeam().teamType)) {
							
							if (self.chosenTeam().isAllowedToPlayBasedOnBaseTeamSubscribtion(self.chosenClub(),myPlayer)) {								
								if(self.chosenTeam().isAllowedToPlayBasedOnBaseMaxPlayerIndex(myPlayer)) {
									self.availablePlayers.push(myPlayer);
								} else {
									self.notAllowedPlayersOnBaseMaxPlayerIndex.push(myPlayer);
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
					self.notAllowedPlayersOnBaseMaxPlayerIndex(self.notAllowedPlayersOnBaseMaxPlayerIndex().sort(playerComparatorBasedSortingValue()));
					
					//self.chosenTeam().calculateAndSetBaseTeamIndex();
				},'json');										
				
			}
		});
		
		//////////////////////////////////////////////////////
		//BEGIN Validation utilities
		//////////////////////////////////////////////////////
		
		this.handleTrash = function(arg,event,ui) {
			  $(".trashItem").fadeOut(1000);
		}
		
		
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
			if ((gameType=="HD" || gameType=="HE") && player.gender !== "M") {
				logError(gameType+" should only contain man",arg);				
				return;
			} 
			if ((gameType=="DD" || gameType=="DE") && player.gender !== "F") {
				logError(gameType+" should only contain women",arg);
				
				return;
			} 
			if (gameType=="GD" && targetGame().length == 1 && player.gender == targetGame()[0].gender) {
				logError(gameType+" should contain one man and one woman",arg);
				return;
			} 
			
			//PLAYERS WITHIN A GAME MUST BE UNIQUE
			if (targetGame().length == 1 && player.vblId == targetGame()[0].vblId) {
				logError("Can not add the same player twice to the same game",arg);
				return;				
			}
			
			//SAME PLAYER CAN ONLY PLAY TWO DOUBLE GAMES
			if ((gameType=="HD" || gameType == "DD") && (gameType != sourceGameType))  {				
				if (self.numberOfGamesPerPlayerPerGameType(player,gameType) == 2) {
					logError("The same player can only play two double games",arg);
					return;									
				}
			}

			//SAME PLAYER CAN ONLY PLAY ONE SINGLE OR ONE MIX GAME
			if ((gameType=="HE" || gameType == "DE" || gameType == "GD") && (gameType != sourceGameType))  {				
				if (self.numberOfGamesPerPlayerPerGameType(player,gameType) == 1) {
					logError("The same player can only play one "+gameType+" game",arg);
					return;									
				}
			}
						
			//GAMES MUST BE ORDERED FROM HIGHEST TO LOWEST INDEX
			var newOrder = self.giveOrderedIndexOfDefinedGamesPerGameTypeAndAddPlayerOnPositionXAndIgnoreGameY(gameType,player,gameTypeIndex,sourceGameTypeIndex);			
			if(!(self.isOrderedIndexArray(newOrder))) {
				logError("Players in game "+gameType+" must be ordered from highest to lowest index.",arg);
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

			// BASE-TEAM-INDEX >= EFFECTIVE-TEAM-INDEX
			if (self.chosenTeam().effectiveTeamIndex() > self.chosenTeam().baseTeamIndex()) {
				logError("Teamindex van de effectieve ploeg mag de teamindex van papieren ploeg niet overschrijden.",arg);
				arg.targetParent.remove(arg.item);
				return;
			}
			
			//Reset error msg after a succesful drop
			self.lastError("");
			$("#error").hide();
			
		};
	};	
	
	
	var vm = new myViewModel(initialGamesEmpty());	
	ko.applyBindings(vm);		
	
	$("#trashCan").append("<div class='label label-default center-block' style='font-size:30px;padding-top:10px'><span class='glyphicon glyphicon-trash'></span></div>");
})(ko, jQuery);
