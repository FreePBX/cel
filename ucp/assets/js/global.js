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
		$("#cel-grid").on("click-row.bs.table", function(row, element) {
			$("#cel-detail-grid").bootstrapTable('load', element.actions);
			$('#callpreview').modal('toggle');
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
	},
	formatDuration: function (value, row, index) {
		return sprintf(_("%s seconds"),value);
	},
	formatDate: function(value, row, index) {
		return UCP.dateFormatter(value);
	},
	formatControls: function (value, row, index) {
		if(typeof row.recordings === "undefined") {
			return '';
		}
		var links = '';
		$.each(row.recordings, function(k, v){
			if(v === false) {
				return true;
			}
			links = '<a class="download" alt="'+_("Download")+'" href="?quietmode=1&amp;module=cel&amp;command=download&amp;msgid='+v+'&amp;type=download&amp;ext='+extension+'"><i class="fa fa-cloud-download"></i></a>';
		});
		return links;
	},
	formatPlayback: function (value, row, index) {
		if(typeof row.recordings === "undefined") {
			return '';
		}
		var html = '';
		$.each(row.recordings, function(k, v){
			if(v === false) {
				return true;
			}
			html += '<div id="jquery_jplayer_'+index+'" class="jp-jplayer" data-container="#jp_container_'+index+'" data-id="'+k+'"></div><div id="jp_container_'+index+'" data-player="jquery_jplayer_'+index+'" class="jp-audio-freepbx" role="application" aria-label="media player">'+
				'<div class="jp-type-single">'+
					'<div class="jp-gui jp-interface">'+
						'<div class="jp-controls">'+
							'<i class="fa fa-play jp-play"></i>'+
							'<i class="fa fa-repeat jp-repeat"></i>'+
						'</div>'+
						'<div class="jp-progress">'+
							'<div class="jp-seek-bar progress">'+
								'<div class="jp-current-time" role="timer" aria-label="time">&nbsp;</div>'+
								'<div class="progress-bar progress-bar-striped active" style="width: 100%;"></div>'+
								'<div class="jp-play-bar progress-bar"></div>'+
								'<div class="jp-play-bar">'+
									'<div class="jp-ball"></div>'+
								'</div>'+
								'<div class="jp-duration" role="timer" aria-label="duration">&nbsp;</div>'+
							'</div>'+
						'</div>'+
						'<div class="jp-volume-controls">'+
							'<i class="fa fa-volume-up jp-mute"></i>'+
							'<i class="fa fa-volume-off jp-unmute"></i>'+
						'</div>'+
					'</div>'+
					'<div class="jp-no-solution">'+
						'<span>Update Required</span>'+
						sprintf(_("You are missing support for playback in this browser. To fully support HTML5 browser playback you will need to install programs that can not be distributed with the PBX. If you'd like to install the binaries needed for these conversions click <a href='%s'>here</a>"),"http://wiki.freepbx.org/display/FOP/Installing+Media+Conversion+Libraries")+
					'</div>'+
				'</div>'+
			'</div>';
		});
		return html;
	},
}), Cel = new CelC();
