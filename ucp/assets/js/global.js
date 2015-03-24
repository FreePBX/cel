var CelC = UCPMC.extend({
	init: function() {
		this.playing = null;
	},
	poll: function(data, url) {

	},
	display: function(event) {
		$(document).on("click", "[vm-pjax] a, a[vm-pjax]", function(event) {
			var container = $("#dashboard-content");
			$.pjax.click(event, { container: container });
		});
		$(".clickable").click(function(e) {
			var text = $(this).text();
			if (UCP.validMethod("Contactmanager", "showActionDialog")) {
				UCP.Modules.Contactmanager.showActionDialog("number", text, "phone");
			}
		});
		$(".cel-header th[class!=\"noclick\"]").click( function() {
			var icon = $(this).children("i"),
					visible = icon.is(":visible"),
					direction = icon.hasClass("fa-chevron-down") ? "up" : "down",
					type = $(this).data("type"),
					search = (typeof $.url().param("search") !== "undefined") ? "&search=" + $.url().param("search") : "",
					uadd = null;
			if (!visible) {
				$(".cel-header th i").addClass("hidden");
				icon.removeClass("hidden");
			}
			if (direction == "up") {
				uadd = "&order=asc&orderby=" + type + search;
				icon.removeClass("fa-chevron-down").addClass("fa-chevron-up");
			} else {
				uadd = "&order=desc&orderby=" + type + search;
				icon.removeClass("fa-chevron-up").addClass("fa-chevron-down");
			}
			$(".cel-header th[class!=\"noclick\"]").off("click");
			$.pjax({ url: "?display=dashboard&mod=cel&sub=" + $.url().param("sub") + uadd, container: "#dashboard-content" });
		});
		$(".subplay").click(function() {
			var id = $(this).data("id");
			var file = $(this).data("file");
			if (Cel.playing === null || Cel.playing != id) {
				if (Cel.playing !== null) {
					$("#jquery_jplayer_" + Cel.playing).jPlayer("stop", 0);
				}
				$("#jquery_jplayer_" + id).jPlayer({
					ready: function() {
					$(this).jPlayer("setMedia", {
						title: "",
						wav: "?quietmode=1&module=cel&command=listen&filename=" + file + "&format=wav&type=playback&ext=" + extension,
						oga: "?quietmode=1&module=cel&command=listen&filename=" + file + "&format=oga&type=playback&ext=" + extension,
					});
					},
					swfPath: "/js",
					supplied: "oga,wav",
					cssSelectorAncestor: "#jp_container_" + id
				}).bind($.jPlayer.event.loadstart, function(event) {
					$("#jp_container_" + id + " .jp-message-window").show();
					$("#jp_container_" + id + " .jp-message-window .message").css("color","");
					$("#jp_container_" + id + " .jp-seek-bar").css("background", 'url("modules/Cel/assets/images/jplayer.blue.monday.seeking.gif") 0 0 repeat-x');
				});

				$(".cel-playback").slideUp("fast");
				$("#cel-playback-" + id).slideDown("fast", function() {
					$("#jquery_jplayer_" + id).bind($.jPlayer.event.error, function(event) {
						$("#jp_container_" + id + " .jp-message-window").show();
						$("#jp_container_" + id + " .message").text(event.jPlayer.error.message).css("color","red");
						$("#jp_container_" + id + " .jp-seek-bar").css("background","");
					});
					$("#jquery_jplayer_" + id).bind($.jPlayer.event.canplay, function(event) {
						$(".jp-message-window").fadeOut("fast");
						$("#jp_container_" + id + " .jp-seek-bar").css("background","");
					});
					$("#jquery_jplayer_" + id).bind($.jPlayer.event.play, function(event) { // Add a listener to report the time play began
						$("#cel-item-" + id + " .subplay i").removeClass("fa-play").addClass("fa-pause");
					});
					$("#jquery_jplayer_" + id).bind($.jPlayer.event.pause, function(event) { // Add a listener to report the time play began
						$("#cel-item-" + id + " .subplay i").removeClass("fa-pause").addClass("fa-play");
					});
					$("#jquery_jplayer_" + id).bind($.jPlayer.event.stop, function(event) { // Add a listener to report the time play began
						$("#cel-item-" + id + " .subplay i").removeClass("fa-pause").addClass("fa-play");
					});
					$("#jquery_jplayer_" + id).jPlayer("play", 0);

				});
				Cel.playing = id;
			} else {
				if ($("#cel-item-" + Cel.playing + " .subplay i").hasClass("fa-pause")) {
					$("#jquery_jplayer_" + Cel.playing).jPlayer("pause");
				} else {
					$("#jquery_jplayer_" + Cel.playing).jPlayer("play");
				}
			}
		});
		$(".search-param").keypress(function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			if (code == 13) {
				Cel.search();
				e.preventDefault();
			}
		});
		$("#search-btn").click(function() {
			Cel.search();
		});
	},
	search: function() {
		var params = "";
		$(".search-param").each(function() {
			params += "&" + $(this).attr("name") + "=" + encodeURIComponent($(this).val());
		});

		$.pjax({
			url: "?display=dashboard&mod=cel&sub=" + $.url().param("sub") + params,
			container: "#dashboard-content"
		});
	},
	hide: function(event) {
		$(document).off("click", "[vm-pjax] a, a[vm-pjax]");
		$(".clickable").off("click");
		if(Cel.playing !== null) {
			$("#jquery_jplayer_" + Cel.playing).jPlayer("stop", 0);
			Cel.playing = null;
		}
	},
	windowState: function(state) {
		//console.log(state);
	}
}), Cel = new CelC();
