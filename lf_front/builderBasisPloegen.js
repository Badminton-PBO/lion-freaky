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


    var Team = function(fixedId,teamType,vm) {
        var self=this;
        this.fixedId = fixedId;
        this.teamType = teamType;
        this.playersInTeam = ko.observableArray();

        this.teamNumber = ko.computed(function(){
            return vm.teams().filter(teamFilterOfTeamType(this.teamType)).filter(teamFilterHavingFixedIndexSmallerThan(this.fixedId)).length +1
        },self);

        this.teamName = ko.computed(function(){
            return vm.teamBaseName()+" "+this.teamNumber()+this.teamType;
        },self);


        //Duplicate some stuff to make validation easier
        this.playersInTeam.team = this;

        this.removePlayer = function(p) {
            self.playersInTeam.remove(p);
        };

        this.totalFixedIndexInsideTeam = ko.computed(function() {
            var totalI=0;
            var myGameType= this.gameType;
            $.each(this.playersInTeam(), function(index,player) {
                totalI += player.fixedIndexInsideTeam(teamType);
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

        this.allowMorePlayers = ko.computed(function() {
            return this.playersInTeam().length < 4;
        },this);

        this.numberOfPlayersOfGender = function(gender) {
            return self.playersInTeam().filter(playerGenderFilter(gender)).length;
        }

        this.numberOfPlayersWithVblId = function(vblId) {
            return self.playersInTeam().filter(playerVblIdFilter(vblId)).length;
        }

        this.isFull = function() {
            return self.playersInTeam().length==4;
        }

    }


    function myViewModel(games) {
        var self = this;
        self.availablePlayers = ko.observableArray();
        self.teams=ko.observableArray();
        self.teamBaseName = ko.observable("Gentse");
        self.fixedIdTeamCounter = 0;
        self.lastError = ko.observable();
        self.selectedTeamType=ko.observable("H");

        //LOAD PLAYERS
        $.get("basisploegen/clubPlayers/30009", function(data) {
            $.each(data.players, function(index,p) {
                var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking,p.type);
                self.availablePlayers.push(myPlayer);
            });
        });

        self.addTeam = function(teamType) {
            self.fixedIdTeamCounter++;
            debug("Adding team "+teamType+ " "+self.fixedIdTeamCounter);
            $('.nav-tabs a[href="#'+teamType+'"]').tab('show');
            self.showTeams(teamType);
            switch (teamType) {
                case "H":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"H",self));
                case "D":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"D",self));
                case "G":
                    return self.teams.push(new Team(self.fixedIdTeamCounter,"G",self));
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

        self.showTeams = function(teamType) {
            //Active tab
            $('.nav-tabs a[href="#'+teamType+'"]').tab('show');

            //filter teams
            self.selectedTeamType(teamType);
        };

        self.noTeamsLayout = ko.computed(function() {
            switch (self.selectedTeamType()) {
                case "H":
                    return "Geen herenploegen aangemaakt. Gebruik 'Acties > Herenploeg toevoegen' om een herenploeg toe te voegen.";
                case "D":
                    return "Geen damesploegen aangemaakt. Gebruik 'Acties > Damesploeg toevoegen' om een damessploeg toe te voegen.";
                case "G":
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
            var sourceTeamPlayerArray = arg.sourceParent;
            var teamType = targetTeam.teamType;

            debug("Validating drop of "+player.fullName+" in team:"+targetTeam.teamName());

            var sourceTeamFixedId =  (sourceTeamPlayerArray && sourceTeamPlayerArray.team) ? sourceTeamPlayerArray.team.fixedId:  "xx";

            var logError = function(msg,arg) {
                self.lastError(msg);
                arg.cancelDrop = true;
            };

            //VALIDATE GENDER
            if (teamType=="H" && player.gender !== "M") {
                logError("Een herenploeg bestaat enkel uit mannen.",arg);
                return;
            }
            if (teamType=="D" && player.gender !== "F") {
                logError("Een damesploeg bestaat enkel uit dames.",arg);
                return;
            }
            if (teamType=="G" && player.gender=='M' && targetTeam.numberOfPlayersOfGender("M") == 2) {
                logError("Een basis opstelling voor een mixploeg bestaat uit maximaal 2 heren.",arg);
                return;
            }
            if (teamType=="G" && player.gender=='F' && targetTeam.numberOfPlayersOfGender("F") == 2) {
                logError("Een basis opstelling voor een mixploeg bestaat uit maximaal 2 dames.",arg);
                return;
            }

            //PLAYERS WITHIN A TEAM MUST BE UNIQUE
            if (targetTeam.numberOfPlayersWithVblId(player.vblId)==1) {
                logError("1 speler kan maar 1 maal in hetzelfde team ingevoerd worden",arg);
                return;
            }

            //PLAYERS MUST BE UNIQUE PER TEAMTYPE
            if (self.numberOfPlayersWithVblIdForTeamTypeAndIgnoreTeamY(player.vblId,teamType,sourceTeamFixedId) == 1){
                logError("1 speler kan maar 1 maal opsteld binnen dezelfde competitietype (H, D, G)",arg);
                return;
            }

            //TEAMS (HAVING ALREADY 4 PLAYERS) MUST BE ORDERED FROM HIGHEST TO LOWEST TOTAL INDEX
            var newOrder = self.giveOrderedIndexOfFullTeamsPerTeamTypeAndAddPlayerToTeamXAndIgnoreTeamY(teamType,player,targetTeam,sourceTeamFixedId);
            debug(newOrder);
            debug(teamType);
            if(!(self.isOrderedIndexArray(newOrder))) {
                debug(teamType);
                switch (teamType) {
                    case "H":
                        logError("De heren teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;
                    case "D":
                        logError("De dames teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;
                    case "G":
                        logError("De gemengde teams moeten geordend zijn van hoogste naar laagste team index",arg);
                        break;

                }
                return;
            }


        };

        this.verifyAssignmentsAfterMove = function(arg,event,ui) {
            //Reset error msg after a succesful drop
            self.lastError("");
            $("#error").hide();
        }

    };


    var vm = new myViewModel();
    ko.applyBindings(vm);
})(ko, jQuery);
