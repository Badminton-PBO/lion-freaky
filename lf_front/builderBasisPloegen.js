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

    var TeamX = function(teamName,event,devision,series,captainName,baseTeamVblIds) {
            this.teamName = teamName;
            this.event = event;
            this.devision = devision;
            this.series = series;
            this.baseTeamVblIds = baseTeamVblIds;
            this.teamType = teamName.slice(-1);
            this.teamNumber  = teamName.slice(-2,-1);

            this.playersInBaseTeam = ko.observableArray();
            this.captainName = ko.observable(captainName);
    }

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

    var Team = function(fixedId,teamType,vm) {
        var self=this;
        this.fixedId = fixedId;
        this.teamType = teamType;
        this.totalIndex = "TODO";
        this.playersInTeam = ko.observableArray();

        this.teamNumber = ko.computed(function(){
            return vm.teams().filter(teamFilterOfTeamType(this.teamType)).filter(teamFilterHavingFixedIndexSmallerThan(this.fixedId)).length +1
        },self);

        this.teamName = ko.computed(function(){
            return vm.teamBaseName()+" "+this.teamNumber()+this.teamType;
        },self);

        this.removePlayer = function(p) {
            self.playersInTeam.remove(p);
        };


    }


    function myViewModel(games) {
        var self = this;
        self.availablePlayers = ko.observableArray();
        self.teams=ko.observableArray();
        self.teamBaseName = ko.observable("Gentse");
        self.fixedIdTeamCounter = 0;

        //LOAD PLAYERS
        $.get("basisploegen/clubPlayers/30009", function(data) {
            $.each(data.players, function(index,p) {
                var myPlayer = new Player(p.firstName,p.lastName,p.vblId,p.gender,p.fixedRanking,p.ranking,p.type);
                self.availablePlayers.push(myPlayer);
            });
        });

        self.addTeam = function(teamType) {
            debug("Adding team "+teamType);
            self.fixedIdTeamCounter++;
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
        }


    };


    var vm = new myViewModel();
    ko.applyBindings(vm);
})(ko, jQuery);
