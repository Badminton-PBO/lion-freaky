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

    function teamFilterOfTeamType(myTeamType) {
        return function(a) {
            return a.teamType == myTeamType;
        }
    }

    function teamFilterHavingFixedIndexSmallerThan(myFixedIndex) {
        return function(a) {
            return a.fixedId < myFixedIndex;
        }
    }

    function playerComparatorBasedSortingValue(sortType,sortDirection,teamType) {
        if (sortType=="NAME" && sortDirection == "DOWN") {
            return function(a, b) {
                if (a.sortingValue < b.sortingValue) return -1;
                if (a.sortingValue > b.sortingValue) return 1;
                return 0;
            }
        } else if (sortType=="NAME" && sortDirection == "UP") {
            return function(a, b) {
                if (a.sortingValue < b.sortingValue) return 1;
                if (a.sortingValue > b.sortingValue) return -1;
                return 0;
            }
        } else if (sortType=="FIXED-INDEX" && sortDirection == "DOWN") {
            return function(a, b) {
                if (a.fixedIndexInsideTeam(teamType) < b.fixedIndexInsideTeam(teamType)) return 1;
                if (a.fixedIndexInsideTeam(teamType) > b.fixedIndexInsideTeam(teamType)) return -1;
                return 0;
            }
        } else if (sortType=="FIXED-INDEX" && sortDirection == "UP") {
            return function(a, b) {
                if (a.fixedIndexInsideTeam(teamType) < b.fixedIndexInsideTeam(teamType)) return -1;
                if (a.fixedIndexInsideTeam(teamType) > b.fixedIndexInsideTeam(teamType)) return 1;
                return 0;
            }
        }

    }




    function playerGenderFilter(myGender) {
        return function(a) {
            return a.gender == myGender;
        }
    }

    function playerVblIdFilter(vblId) {
        return function(a) {
            return a.vblId == vblId;
        }
    }

    function teamIsFullFilter() {
        return function(a) {
            return a.isComplete() == true;
        }
    }

    function groupFilter(myGroupType,myGroupEvent,myGroupDevision) {
        return function(a) {
            return a.groupType == myGroupType && a.groupEvent == myGroupEvent && a.groupDevision == myGroupDevision;
        }
    }

    var Button = function(name,value,selected) {
        this.name = name;
        this.buttonValue =  ko.observable(value);
        this.selected = ko.observable(selected);
    }

    var Player = function(firstName,lastName,vblId,gender,fixedRanking,type) {
        this.firstName = firstName;
        this.lastName = lastName;
        this.fullName = this.firstName  + ' ' + this.lastName;
        this.vblId = vblId;
        this.gender = gender;
        this.fixedRanking  = fixedRanking;
        this.sortingValue = this.fullName;
        this.type = type;

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


        this.rankingOrFixedRanking = function(gameType,isFixed) {
            if(gameType == 'HE' || gameType == 'DE')
                return isFixed ? this.fixedRankingSingle : this.rankingSingle;

            if(gameType == 'HD' || gameType == 'DD')
                return isFixed ? this.fixedRankingDouble : this.rankingDouble;

            if(gameType == 'GD')
                return isFixed ? this.fixedRankingMix : this.rankingMix;
        }

        this.fixedRanking = function(gameType) {
            return this.rankingOrFixedRanking(gameType,true);
        }


        this.fixedIndex = function(gameType) {
            return this.rankingToIndex(this.fixedRanking(gameType));
        }

        this.fixedIndexInsideTeam = function(teamType) {
            switch(teamType) {
                case "M":
                    return this.fixedIndex("HE") + this.fixedIndex("HD");
                case "L":
                    return this.fixedIndex("DE") + this.fixedIndex("DD");
                case "MX":
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
                case "M":
                    return Math.max(this.fixedIndex("HE"),this.fixedIndex("HD"));
                case "L":
                    return Math.max(this.fixedIndex("DE"),this.fixedIndex("DD"));
                case "MX":
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
                case "M":
                    return this.fixedRankingSingle + ", " + this.fixedRankingDouble +" = "+this.fixedIndexInsideTeam(teamType) ;
                case "L":
                    return this.fixedRankingSingle + ", " + this.fixedRankingDouble +" = "+this.fixedIndexInsideTeam(teamType);
                case "MX":
                    return this.fixedRankingSingle + ", " + this.fixedRankingDouble + ", " +this.fixedRankingMix +" = "+this.fixedIndexInsideTeam(teamType);
            }
        }

        //Validation rules
        this.isAllowedToPlayInTeamGameTypeBasedOnGender = function(teamType) {
            return ((teamType == 'M' && this.gender == 'F') || (teamType == 'L' && this.gender == 'M')) ? false : true;
        }

    };


    var Team = function(fixedId,teamType,vm) {
        var self=this;
        this.fixedId = fixedId;
        this.teamType = teamType;
        this.playersInTeam = ko.observableArray();
        this.realPlayersInTeam = ko.observableArray();
        self.chosenGroup = ko.observable();

        this.teamNumber = ko.computed(function(){
            return vm.teams().filter(teamFilterOfTeamType(this.teamType)).filter(teamFilterHavingFixedIndexSmallerThan(this.fixedId)).length +1
        },self);

        this.teamTypeToTeamNameSuffix = function(teamType) {
            switch (teamType) {
                case "M":
                    return "H"
                case "L":
                    return "D";
                case "MX":
                    return "G";
            }
        };

        this.teamHtmlId = ko.computed(function(){
            return this.teamNumber()+this.teamTypeToTeamNameSuffix(this.teamType);
        },self);

        this.teamName = ko.computed(function(){
            return vm.teamBaseName()+" "+this.teamNumber()+this.teamTypeToTeamNameSuffix(this.teamType);
        },self);

        //Duplicate some stuff to make validation easier
        this.playersInTeam.team = this;
        this.realPlayersInTeam.team = this;

        this.removePlayer = function(p) {
            self.playersInTeam.remove(p);
        };

        this.removeRealPlayer = function(p) {
            self.realPlayersInTeam.remove(p);
        };

        this.isFull = function() {
            return self.playersInTeam().length==4;
        }


        this.totalFixedIndexInsideTeam = ko.computed(function() {
            var totalI=0;
            $.each(this.playersInTeam(), function(index,player) {
                totalI += player.fixedIndexInsideTeam(teamType);
            });
            return totalI;
        },self);

        this.totalFixedIndexInsideTeamForRealPlayers = ko.computed(function() {
            var totalI=0;
            var sortedPlayers = this.realPlayersInTeam().concat().sort(playerComparatorBasedSortingValue("FIXED-INDEX","DOWN",this.teamType));
            $.each(sortedPlayers, function(index,player) {
                if (index<4) {
                    totalI += player.fixedIndexInsideTeam(teamType);
                }
            });
            return totalI;
        },self);

        this.totalFixedIndexInsideTeamLayout = ko.computed(function() {
            if (this.playersInTeam().length == 4) {
                return this.totalFixedIndexInsideTeam();
            } else {
                return "("+this.totalFixedIndexInsideTeam()+")";
            }
        },this);

        this.isRealPlayerIndexInWarningComparedToFixedIndex = ko.computed(function(){
            return this.isFull() && this.teamNumber() != 1 && (this.totalFixedIndexInsideTeamForRealPlayers() > this.totalFixedIndexInsideTeam())
        },this);


        this.isRealPlayersTeamInWarningMode = ko.computed(function(){
            return this.isRealPlayerIndexInWarningComparedToFixedIndex()
        },this);


        this.realPlayerTeamWarningText = ko.computed(function(){
            if (this.isRealPlayerIndexInWarningComparedToFixedIndex()) {
                return "Opgelet: Teamindex papieren ploeg is kleiner dan Teamindex effectieve ploeg. De effectieve ploeg kan dus niet op volle sterkte ingezet worden.";
            }
        },this);


        this.allowMorePlayers = ko.computed(function() {
            return this.playersInTeam().length < 4;
        },this);

        this.numberOfPlayersOfGender = function(gender) {
            return self.playersInTeam().filter(playerGenderFilter(gender)).length;
        }

        this.numberOfRealPlayersOfGender = function(gender) {
            return self.realPlayersInTeam().filter(playerGenderFilter(gender)).length;
        }

        this.numberOfPlayersWithVblId = function(vblId) {
            return self.playersInTeam().filter(playerVblIdFilter(vblId)).length;
        }

        this.numberOfRealPlayersWithVblId = function(vblId) {
            return self.realPlayersInTeam().filter(playerVblIdFilter(vblId)).length;
        }


        this.isFullWithRealPlayers = function() {
            switch (teamType) {
                case "M":
                    return self.realPlayersInTeam().length>=4;
                case "L":
                    return self.realPlayersInTeam().length>=4;
                case "MX":
                    return self.numberOfRealPlayersOfGender("F")>=2 && self.numberOfRealPlayersOfGender("M")>=2;
            }
            ;
        };

        this.totalFixedIndexInsideTeamForRealPlayersLayout = ko.computed(function() {
            if (this.isFullWithRealPlayers()) {
                return this.totalFixedIndexInsideTeamForRealPlayers();
            } else {
                return "("+this.totalFixedIndexInsideTeamForRealPlayers()+")";
            }
        },this);


    }

    var Group = function(groupType, groupEvent, groupDevision) {
        var self=this;
        this.groupType = groupType;
        this.groupEvent = groupEvent;
        this.groupDevision = groupDevision;

        this.groupLayout = this.groupType + " "+ this.groupEvent + " "+this.groupDevision;

    };

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

    function initialGroups() {
        return [
            new Group("LIGA","M",1),
            new Group("LIGA","M",2),
            new Group("LIGA","M",3),

            new Group("LIGA","L",1),
            new Group("LIGA","L",2),

            new Group("LIGA","MX",1),
            new Group("LIGA","MX",2),

            new Group("PROV","M",1),
            new Group("PROV","M",2),
            new Group("PROV","M",3),
            new Group("PROV","M",4),
            new Group("PROV","M",5),

            new Group("PROV","L",1),
            new Group("PROV","L",2),
            new Group("PROV","L",3),

            new Group("PROV","MX",1),
            new Group("PROV","MX",2),
            new Group("PROV","MX",3),
            new Group("PROV","MX",4)
        ];
    }


    function myViewModel(games) {
        var self = this;
        self.clubName=ko.observable("");
        self.season=ko.observable("2016-2017");
        self.availablePlayers = ko.observableArray();
        self.teams=ko.observableArray();
        self.teamBaseName = ko.observable("");
        self.fixedIdTeamCounter = 0;
        self.lastError = ko.observable();
        self.lastWarning = ko.observable();
        self.lastSuccess = ko.observable();
        self.selectedTeamType=ko.observable("H");
        self.selectedPlayerSortType=ko.observable("NAME");
        self.selectedPlayerSortDirection=ko.observable("DOWN");
        self.transferSearchVblId = ko.observable("");
        self.foundTransferPlayer = ko.observableArray();
        self.justDroppedWithWarning = ko.observable(false);

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

        self.toggleSortPlayers = function(sortType) {
            if (self.selectedPlayerSortType() == sortType) {
                //We need to toggle
                self.selectedPlayerSortDirection()=='DOWN'? self.selectedPlayerSortDirection("UP"):self.selectedPlayerSortDirection("DOWN");
            } else {
                self.selectedPlayerSortDirection("DOWN");//Back to default order
                self.selectedPlayerSortType(sortType);
            }
        }

        self.groups = ko.observableArray(initialGroups());

        //LOAD PLAYERS
        $.get("basisploegen/clubPlayers", function(data) {
            $.each(data.players, function(index,p) {
                //TODO: change to fixed ranking !!!! But currently not available so testing with current ranking
                // var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.type);
                var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.ranking,p.type);
                self.availablePlayers.push(myPlayer);
            });

            //Sort the arrays of players thtat are shown in UI
            self.availablePlayers(self.availablePlayers().sort(playerComparatorBasedSortingValue("NAME","DOWN")));

            self.clubName(data.club.clubName);
            self.teamBaseName(data.club.teamNamePrefix);

        });

        //Load existing teams
        $.get("basisploegen/currentTeams", function(data) {
            $.each(data.teams, function(index,t){
                self.fixedIdTeamCounter++;
                var newTeam = new Team(self.fixedIdTeamCounter, t.teamType,self);

                $.each(t.players, function(index,p){
                    switch (p.role) {
                        case "P":
                            newTeam.playersInTeam.push(new Player(p.firstName, p.lastName, p.vblId, p.gender, p.fixedRanking));
                            break;
                        case "R":
                            newTeam.realPlayersInTeam.push(new Player(p.firstName, p.lastName, p.vblId, p.gender, p.fixedRanking));
                            break;
                    }
                });
                if (t.groupType != null) {
                    newTeam.chosenGroup(self.groups().filter(groupFilter(t.groupType, t.groupEvent, t.groupDevision))[0])   ;
                }
                self.teams.push(newTeam);
            });
            self.showTeams('M');
        });



        self.addTeam = function(teamType) {
            self.fixedIdTeamCounter++;
            debug("Adding team "+teamType+ " "+self.fixedIdTeamCounter);
            $('.nav-tabs a[href="#'+teamType+'"]').tab('show');
            self.showTeams(teamType);
            switch (teamType) {
                case "M":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"M",self));
                case "L":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"L",self));
                case "MX":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"MX",self));
            }
        };

        self.removeTeam = function(team){
            self.teams.remove(team);
        };

        self.filteredTeams= ko.computed(function() {
            return ko.utils.arrayFilter(self.teams(), function(team) {
                return team.teamType == self.selectedTeamType();
            });

        },self);


        self.filteredAndSortedPlayers = ko.computed(function(){
            var filteredPlayers =  ko.utils.arrayFilter(self.availablePlayers(), function(player) {
                return player.isAllowedToPlayInTeamGameTypeBasedOnGender(self.selectedTeamType()) &&
                    (self.selectedPlayerTypeButton().buttonValue() == 'ALL' || player.type == self.selectedPlayerTypeButton().buttonValue()) &&
                    (self.selectedGenderButton().buttonValue() == 'ALL' || player.gender == self.selectedGenderButton().buttonValue());
            });

            return filteredPlayers.sort(playerComparatorBasedSortingValue(self.selectedPlayerSortType(),self.selectedPlayerSortDirection(),self.selectedTeamType()));
        },self);

        self.sortingDirectionGlyphicon = ko.computed(function(){
            return self.selectedPlayerSortDirection() == "DOWN" ? "glyphicon-chevron-down":"glyphicon-chevron-up";
        },self);


        self.showTeams = function(teamType) {
            //Active tab
            $('.nav-tabs a[href="#'+teamType+'"]').tab('show');

            //filter teams
            self.selectedTeamType(teamType);
        };

        self.noTeamsLayout = ko.computed(function() {
            switch (self.selectedTeamType()) {
                case "M":
                    return "Geen herenploegen aangemaakt. Gebruik 'Acties > Herenploeg toevoegen' om een herenploeg toe te voegen.";
                case "L":
                    return "Geen damesploegen aangemaakt. Gebruik 'Acties > Damesploeg toevoegen' om een damessploeg toe te voegen.";
                case "MX":
                    return "Geen gemengde ploegen aangemaakt. Gebruik 'Acties > Gemengde ploeg toevoegen' om een gemengde ploeg toe te voegen.";
            }
        },self);

        self.numberOfTeamsOfTeamType = function(teamType) {
            return ko.computed({
                read:function() {
                    return self.teams().filter(teamFilterOfTeamType(teamType)).length;
                }

            },this);
        };

        this.fixedRankingHeaderLayout = function(teamType) {
            switch (teamType) {
                case "M":
                    return "vaste index E,D" ;
                case "L":
                    return "vaste index E,D";
                case "MX":
                    return "vast index E,D,G";
            }
        }

        this.selectedTeamTypeIsMultiSex = ko.computed(function() {
            return this.selectedTeamType() == 'MX';
        },self);

        this.searchPlayersUsingVblId = function() {
            debug("Searching player with VblId: "+this.transferSearchVblId());
            $.get("basisploegen/searchPlayer/"+this.transferSearchVblId(), function(data) {
                $.each(data.players, function(index,p) {
                     var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,'','');
                    self.foundTransferPlayer.push(myPlayer);
                });

            });
        }




        //////////////////////////////////////////////////////
        //BEGIN Validation utilities
        //////////////////////////////////////////////////////

        self.numberOfPlayersWithVblIdForTeamTypeAndIgnoreTeamY = function(vblId,teamType,teamYFixedId) {
            myResult=0;
            self.teams().forEach(function(myTeam){
                if (myTeam.teamType == teamType && myTeam.fixedId != teamYFixedId) {
                    myTeam.playersInTeam().forEach(function(myPlayer){
                        if (myPlayer.vblId == vblId) {
                            myResult++;
                        }
                    })
                }
            })
            return myResult;
        };


        self.giveOrderedIndexOfFullTeamsPerTeamTypeAndAddPlayerToTeamXAndIgnoreTeamY = function(myTeamType,myPlayer,teamX,teamYFixedId) {
            var result=[];
            $.each(self.teams(), function(position,team) {
                if (team.teamType == myTeamType ) {
                    if (team.fixedId == teamX.fixedId && teamX.playersInTeam().length == 3) {//together with the given myPlayer, this will form a complete team
                        result.push(teamX.totalFixedIndexInsideTeam() + myPlayer.fixedIndexInsideTeam(myTeamType));
                    } else if ((team.fixedId !== teamYFixedId) && team.isFull()){
                        result.push(team.totalFixedIndexInsideTeam());
                    }
                }
            });
            return result;
        };

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
            var targetTeam = arg.targetParent.team;
            var sourceParentArray = arg.sourceParent;
            var sourceParentNode = arg.sourceParentNode;
            var teamType = targetTeam.teamType;

            debug("Validating drop of "+player.fullName+" in team:"+targetTeam.teamName());

            var isDropFromRealPlayerColumn = typeof arg.sourceParentNode !== 'undefined'  &&   typeof arg.sourceParentNode.context !== 'undefined' && typeof arg.sourceParentNode.context.id !== 'undefined' && arg.sourceParentNode.context.id.startsWith("r_");

            var sourceTeamFixedId =  (!isDropFromRealPlayerColumn && sourceParentArray && sourceParentArray.team) ? sourceParentArray.team.fixedId:  "xx";

            var logError = function(msg,arg) {
                self.lastError(msg);
                arg.cancelDrop = true;
            };

            //Sorting inside same box is allowed.
            if (sourceParentArray && typeof sourceParentArray !== 'undefined' ) {
                //Item is coming from a sortable, not a draggable! So player from Papieren of Effectieve ploeg
                if (typeof sourceParentNode.id !== 'undefined') {//sourceParentNode.id is only available when dragging/dropping inside same array
                    arg.cancelDrop = false;
                    return;
                }
            }

            //VALIDATE GENDER
            if (teamType=="M" && player.gender !== "M") {
                logError("Een herenploeg bestaat enkel uit mannen.",arg);
                return;
            }
            if (teamType=="L" && player.gender !== "F") {
                logError("Een damesploeg bestaat enkel uit dames.",arg);
                return;
            }
            if (teamType=="MX" && player.gender=='M' && targetTeam.numberOfPlayersOfGender("M") == 2) {
                logError("Een basis opstelling voor een mixploeg bestaat uit maximaal 2 heren.",arg);
                return;
            }
            if (teamType=="MX" && player.gender=='F' && targetTeam.numberOfPlayersOfGender("F") == 2) {
                logError("Een basis opstelling voor een mixploeg bestaat uit maximaal 2 dames.",arg);
                return;
            }

            //PLAYERS WITHIN A TEAM MUST BE UNIQUE
            if (targetTeam.numberOfPlayersWithVblId(player.vblId)==1) {
                logError("1 basis speler kan maar 1 maal in hetzelfde team worden opgesteld",arg);
                return;
            }

            //PLAYERS MUST BE UNIQUE PER TEAMTYPE
            if (self.numberOfPlayersWithVblIdForTeamTypeAndIgnoreTeamY(player.vblId,teamType,sourceTeamFixedId) == 1){
                logError("1 basis speler kan maar 1 maal binnen dezelfde competitietype (H, D, G) worden opgesteld",arg);
                return;
            }

            //TEAMS (HAVING ALREADY 4 PLAYERS) MUST BE ORDERED FROM HIGHEST TO LOWEST TOTAL INDEX
            var newOrder = self.giveOrderedIndexOfFullTeamsPerTeamTypeAndAddPlayerToTeamXAndIgnoreTeamY(teamType,player,targetTeam,sourceTeamFixedId);
            if(!(self.isOrderedIndexArray(newOrder))) {
                debug(teamType);
                switch (teamType) {
                    case "M":
                        logError("De heren teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;
                    case "L":
                        logError("De dames teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;
                    case "MX":
                        logError("De gemengde teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;

                }
                return;
            }
        };

        this.verifyAssignmentsRealPlayer = function(arg,event,ui) {
            var player = arg.item;
            var targetTeam = arg.targetParent.team;
            var sourceParentArray = arg.sourceParent;
            var sourceParentNode = arg.sourceParentNode;
            var teamType = targetTeam.teamType;

            debug("Validating drop of "+player.fullName+" in real team:"+targetTeam.teamName());

            var logError = function(msg,arg) {
                self.lastError(msg);
                arg.cancelDrop = true;
            };

            var logWarning = function(msg,arg) {
                self.lastWarning(msg);
                arg.cancelDrop = false;
                self.justDroppedWithWarning(true);
            };


            //Sorting inside same box is allowed.
            if (sourceParentArray && typeof sourceParentArray !== 'undefined' ) {
                //Item is coming from a sortable, not a draggable! So player from Papieren of Effectieve ploeg
                if (typeof sourceParentNode.id !== 'undefined') {//sourceParentNode.id is only available when dragging/dropping inside same array
                    arg.cancelDrop = false;
                    return;
                }
            }

            //VALIDATE GENDER
            if (teamType=="M" && player.gender !== "M") {
                logError("Een herenploeg bestaat enkel uit mannen.",arg);
                return;
            }
            if (teamType=="L" && player.gender !== "F") {
                logError("Een damesploeg bestaat enkel uit dames.",arg);
                return;
            }

            //PLAYERS WITHIN A TEAM MUST BE UNIQUE
            if (targetTeam.numberOfRealPlayersWithVblId(player.vblId)==1) {
                logError("1 effectieve speler kan maar 1 maal in hetzelfde team ingevoerd worden",arg);
                return;
            }

            //Ranking baseteam >= ranking realteam, only check when baseTeam if full
            if (!targetTeam.allowMorePlayers()) {
                if (targetTeam.teamNumber()!=1 && targetTeam.totalFixedIndexInsideTeam() < (targetTeam.totalFixedIndexInsideTeamForRealPlayers() + player.fixedIndexInsideTeam(teamType))) {
                    logWarning("Opgelet: Teamindex papieren ploeg is kleiner dan Teamindex effectieve ploeg. De effectieve ploeg kan dus niet op volle sterkte ingezet worden.",arg);
                }
            }
        };

        this.verifyAssignmentsAfterMove = function(arg,event,ui) {
            //Reset error msg after a succesful drop
            if (self.justDroppedWithWarning()) {
                self.justDroppedWithWarning(false);
            } else {
                self.lastError("");
                self.lastWarning("");
                $("#error").hide();
                $("#warning").hide();
            }
        };



        self.save = function() {
            var vmjs = $.parseJSON(ko.toJSON(self));
            var resultObject = {"teams": vmjs.teams};

            $('#saveAndSend').button('loading');
            var posting = $.ajax({
                method:"POST",
                url:"basisploegen/saveTeams",
                data: JSON.stringify(resultObject),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                headers : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            posting.done(function(data) {
                $('#saveAndSend').button('reset');
                if (data.processedSuccessfull) {
                    $resultText = "Basisploegen bewaard." ;
                    //self.chosenMeeting().dbStatus(dbStatusLayout(data.status));
                    //self.chosenMeeting().dbActionFor(dbActionForLayout(data.actionFor));
                    self.lastSuccess($resultText);
                    debug($resultText);
                } else {
                    self.lastError("Problemen bij het bewaren van de basisploegen.");
                    debug("saving failed")
                }
            });

            posting.fail(function(data) {
                $('#saveAndSend').button('reset');
                //self.lastError("Problemen bij het bewaren van deze verplaatsings aanvraag.");
                debug("saving failed")
            });

        }

    };


    var vm = new myViewModel();
    ko.applyBindings(vm);
})(ko, jQuery);
