//Window.console.log not available in IE9 when developper tools are not activated
if (!window.console) window.console = {};
if (!window.console.log) window.console.log = function () { };

moment.locale("nl");

(function(ko, $, undefined) {


	function myViewModel() {
		var self = this;
		self.meetingsWithActionForPBO = ko.observable();
        self.meetingWithOpenRequest = ko.observable();
		$.get("verplaatsing/meetingsWithActionForPBO", function(data) {
			self.meetingsWithActionForPBO(data);
		});
        $.get("verplaatsing/meetingWithOpenRequest", function(data) {
            self.meetingWithOpenRequest(data);
        });

	}
	var vm = new myViewModel();	
	ko.applyBindings(vm);
})(ko, jQuery);
