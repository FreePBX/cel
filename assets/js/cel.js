var playing = null;
function recording_play(rowNum, link, title) {
	var playerId = rowNum;
	$(".playback").hide("fast");

	if (playing !== null && playing != playerId) {
		$("#jquery_jplayer_" + playing).jPlayer("stop", 0);
		playing = playerId;
	} else if (playing !== null && playing === playerId) {
		$("#jquery_jplayer_" + playing).jPlayer("stop", 0);
		playing = null;

		return true;
	} else if (playing === null) {
		playing = playerId;
	}
	$("#jquery_jplayer_" + playerId).jPlayer({
		ready: function() {
			$(this).jPlayer("setMedia", {
				title: title,
				wav: link
			});
			var $this = this;
			$("#jquery_jplayer_" + playerId).find(".jp-restart").click(function() {
				if($($this).data("jPlayer").status.paused) {
					$($this).jPlayer("pause",0);
				} else {
					$($this).jPlayer("play",0);
				}
			});
		},
		swfPath: "/js",
		supplied: "wav",
		cssSelectorAncestor: "#jp_container_" + playerId
	});
	$("#playback-" + playerId).slideDown("fast", function(event) {
		$("#jquery_jplayer_" + playerId).jPlayer("play", 0);
	});
}
