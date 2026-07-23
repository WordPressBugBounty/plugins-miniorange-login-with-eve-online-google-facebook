/**
 * MO OAuth Setup Wizard Inline Script
 *
 * JS extracted from inline <script> blocks that used to live inside the
 * setup wizard partials, consolidated here so it can be enqueued instead
 * of printed inline. Relies on globals from generic.min.js and the
 * mo_oauth_ajax_object localization (ajax_url, app, site_url).
 *
 * NOTE: intentionally not in strict mode — `target`, `mo_oauth_test_ajax_count`,
 * `mo_oauth_trace_test_progress` and `app_scopes` are implicit globals shared
 * between the handlers below, exactly as in the original inline scripts.
 *
 * @package    setup-wizard
 * @author     miniOrange <info@miniorange.com>
 * @license    Expat
 * @link       https://miniorange.com
 */

/* partials/callback.php: copy-to-clipboard for the callback URL. */
function mooauth_outFunc() {
	var tooltip = document.getElementById("moTooltip");
	setTimeout(function() {
		tooltip.innerText = "";
	}, 3000);
}

function mooauth_copyUrl() {
	var copyText = document.getElementById("callbackurl");
	mooauth_outFunc();
	copyText.select();
	copyText.setSelectionRange(0, 99999);
	document.execCommand("copy");
	var tooltip = document.getElementById("moTooltip");
	tooltip.innerText = "Copied";
}

/* partials/test.php: poll and render the SSO test result. */
function mooauth_get_test_result(){
	var data = {
		"action": "mo_outh_ajax",
		"mo_oauth_option": "test_result",
		"mo_oauth_nonce" : jQuery("#nonce").val(),
	}
	jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
		jQuery(".mo-oauth-test-in-progress").hide();
		jQuery(".mo-oauth-test-in-failed").hide();
		jQuery(".mo-oauth-test-successed").hide();
		jQuery(".mo-oauth-result-test").empty();
		if("wait" == response[0]){
			jQuery(".mo-oauth-test-in-progress").show();
			mooauth_get_test_log(response);
		}
		if("fail" == response[0]){
			jQuery(".mo-oauth-test-in-failed").show();
			clearInterval(mo_oauth_trace_test_progress);
			mooauth_get_test_log(response);
		}
		if("success" == response[0]){
			jQuery(".mo-oauth-test-successed").show();
			mooauth_get_success_div(response[1],response[2]);
			clearInterval(mo_oauth_trace_test_progress);
		}
	});
}

function mooauth_get_attr(attr_list){
	jQuery.each(attr_list, function (key, data) {
		if("object" == typeof data ){
			mooauth_get_attr(data);
		}else{
			jQuery(".mo-oauth-test-attr-table").append("<tr><td class=mo_summary_col_wid>"+key+"</td><td>"+data+"</td></tr>");
		}
	})
}

function mooauth_get_success_div(attr_list,username_attr){
	var currentStep = jQuery("#step").val();
	jQuery(".mo-oauth-test-attr-table").find("tr:gt(0)").remove();
	jQuery(".mo-oauth-test-prefered-attr").empty();
	if(undefined != username_attr && "" != username_attr && null != username_attr){
		jQuery(".mo-oauth-test-prefered-attr").append("<b>"+username_attr+"</b> has been mapped to username attribute.&nbsp;<a href='#' id='mo-submit_click_here'>Click here</a> to change it.");
	}
	else{
		jQuery(".mo-oauth-test-prefered-attr").append("<a id='mo-submit_click_here'>Click here</a> for attribute mapping configuration.");
	}
	add_event_listner_to_click_here();
	mooauth_get_attr(attr_list);
	if(currentStep == "4"){
		mooauth_get_suggestion_troubleshooting("general"); }
}

function mooauth_get_test_log(logs){
	var currentStep = jQuery("#step").val();
	var length = logs.length -1;
	mo_oauth_test_ajax_count++;
	let ajaxLimit = 10;
	if(ajaxLimit < mo_oauth_test_ajax_count){
		jQuery(".mo-oauth-test-in-progress").hide();
		jQuery(".mo-oauth-test-in-failed").show();
		clearInterval(mo_oauth_trace_test_progress);
	}
	var display_log_arr = {"Authorization Request Sent":0,"Token Request Sent":0,"Token Response Received":0,"Authorization Response Received":0,"Resource Owner Response":0};
	for(var i=1; i<length;i++){
		for(var key in display_log_arr){
			var icon_class = "mo-oauth-test-right-tick";
			var info_class = "mo-oauth-test-success-info";
			if(undefined !== logs[i][1] && (-1 !== logs[i][1].indexOf("ERROR") || -1 !== logs[i][1].indexOf("error") )){
				var icon_class = "mo-oauth-test-cross-tick";
				var info_class = "mo-oauth-test-error-info";
			}
			if(-1 !== logs[i][0].indexOf(key) && 0 === display_log_arr[key]){
				display_log_arr[key] = 1;
				jQuery(".mo-oauth-result-test").append("<div class="+icon_class+"><label>"+key+"</label></div>");
				if(undefined !== logs[i][1])
					jQuery(".mo-oauth-result-test").append("<div class="+info_class+">"+logs[i][1]+"</div>");
				jQuery(".mo-oauth-result-test").append("<div class=mo-oauth-log></div>");
				break;
			}
			if(mo_oauth_test_ajax_count > ajaxLimit){
				if(-1 === logs[i][0].indexOf(key) && key == "Token Request Sent" && currentStep== "4" ){
					mooauth_get_suggestion_troubleshooting("timeExceed");
					jQuery("#mo-btn-test-re-run").show();
				}
			}
			if((undefined !== logs[i][1] && (-1 !== logs[i][1].indexOf("ERROR") || -1 !== logs[i][1].indexOf("error") )) && key == "Token Response Received" && currentStep== "4"){
				jQuery("#mo-btn-test-re-run").show();
				mooauth_get_suggestion_troubleshooting("noAccessToken");
			}
		}
	}
}

function add_event_listner_to_click_here(){
	jQuery("#mo-submit_click_here").click(function(e){
		e.preventDefault();
		var data = {
			"action": "mo_outh_ajax",
			"mo_oauth_option": "test_finish",
			"mo_oauth_nonce" : jQuery("#nonce").val()
		}
		jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
			window.location.href = window.location.pathname + "?page=mo_oauth_settings&tab=attributemapping";
		});
	});
}

jQuery(function() {

	/* partials/apps.php: suppress Enter-key submit and handle default app selection. */
	jQuery("#mo_setup_wizard_form").on("keypress", function (event) {
		var keyPressed = event.keyCode || event.which;
		if (keyPressed === 13) {
			event.preventDefault();
			return false;
		}
	});

	jQuery("#mo_oauth_client_default_apps li").click(function(){
		var appId = jQuery(this).data("appid");
		jQuery("#displayName").val(appId);
		jQuery("#moauth_show_desc").html("This will displayed on SSO login button as <b>\"Login with " +appId +"\"</b>. The entire button name is customizable in paid versions.");
		var selected_app_child = jQuery(this).children();
		var jsonStr = jQuery(selected_app_child[1]).val();
		if (jsonStr) {
			try {
				var selected_app = jQuery.parseJSON(jsonStr);
			} catch (e) {
				return;
			}
		} else {
			return;
		}
		var selected_app = jQuery.parseJSON(jQuery(selected_app_child[1]).val());
		var discovery = jQuery(".mo-discovery");
		jQuery("#type").val(selected_app["type"]);
		if("oauth1" == selected_app["type"])
			jQuery("#mo-oauth-scope").hide();
		else
			jQuery("#mo-oauth-scope").show();

		var inputs = "";
		jQuery(discovery).empty();
		if(undefined != selected_app["input"]){
			for(i in selected_app["input"]){
				jQuery(discovery).append('<div class="field-group"><label>'+i+'</label><input type="text" class="mo-normal-text long-field" name="'+i+'" id="'+i+'" placeholder="'+selected_app["input"][i]+'"><i class="fa mo-valid-icon"></i></div>');
				inputs = inputs+" "+i;
			}
			jQuery("#discInput").val(jQuery.trim(inputs));
			if(undefined != selected_app["avl_domain"]){
				jQuery("#Domain").val(selected_app["avl_domain"]);
			}
		}
		else{
			jQuery("#discInput").val("");
			if(undefined != selected_app["authorize"])
				jQuery(discovery).append('<div class="field-group"><label>Authorization Endpoint</label><input type="text" class="mo-normal-text long-field" name="authorize" id="authorize" value="'+selected_app["authorize"]+'" placeholder="Enter authorization endpoint"></div>');
			if(undefined != selected_app["token"])
				jQuery(discovery).append('<div class="field-group"><label>Token Endpoint</label><input type="text" class="mo-normal-text long-field" name="token" id="token" value="'+selected_app["token"]+'" placeholder="Enter token endpoint"></div>');
			if("openidconnect" != selected_app["type"] && undefined != selected_app["userinfo"])
				jQuery(discovery).append('<div class="field-group"><label>Userinfo Endpoint</label><input type="text" class="mo-normal-text long-field" name="userinfo" id="userinfo" value="'+selected_app["userinfo"]+'" placeholder="Enter userinfo endpoint"></div>');
			if(undefined != selected_app["setup_notice"])
				jQuery(discovery).append('<div id= "notice" class="mo-setup-notice">Note : '+selected_app["setup_notice"]+' </div>');
			if(undefined != selected_app["requesturl"])
				jQuery(discovery).append('<div class="field-group"><label>Request Token Endpoint</label><input type="text" class="mo-normal-text long-field" name="requesturl" id="requesturl" value="'+selected_app["requesturl"]+'" placeholder="Enter request token endpoint"></div>');
		}
		if(undefined != selected_app["scope"] && "" != selected_app["scope"]){
			app_scopes = selected_app["scope"].split(" ");
			jQuery(".ui.dropdown.fluid").dropdown({allowAdditions: true,hideAdditions: false});
			jQuery(".ui.fluid.dropdown").dropdown("clear");
			jQuery(".ui.fluid.dropdown").dropdown("set selected",app_scopes);
		}
		if(undefined != selected_app["send_header"] && "1" == selected_app["send_header"]){
			jQuery("#send_header").prop("checked",true);
		}
		if(undefined != selected_app["send_body"] && "1" == selected_app["send_body"]){
			jQuery("#send_body").prop("checked",true);
		}
		jQuery(".mo-oauth-setup-guide").empty();
		if(undefined != selected_app["guide"] && "" != selected_app["guide"]){
			jQuery(".mo-oauth-setup-guide").append('<a href="'+selected_app["guide"]+'" class="mo-oauth-setup-guide-link" target="_blank">&nbspSetup Guide</a>&nbsp');
		}
		if(undefined != selected_app["video"] && "" != selected_app["video"]){
			jQuery(".mo-oauth-setup-guide").append('<a href="'+selected_app["video"]+'" class="mo-oauth-setup-video-link" target="_blank">&nbspVideo Guide</a>');
		}
		jQuery("#appId").val(appId);
		mooauth_steps_icr();
		var data= mooauth_get_data("save_draft","next");
		jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
		});
	});

	/* partials/client.php: init the scopes dropdown. */
	if (jQuery(".ui.dropdown.fluid").length) {
		jQuery(".ui.dropdown.fluid")
			.dropdown({
				allowAdditions: true,
				hideAdditions: false,
				clearable:true
			});
	}

	/* partials/summary.php: edit-step links. */
	jQuery(document.body).on("click", ".mo-editstep" ,function(e){
		mooauth_get_step(jQuery(e.target).attr("data-step"));
	});

	/* partials/support.php: submit a support query. */
	jQuery("#mo-oauth-submit-support").click(function(){
		var data={
			"action"			: "mo_outh_ajax",
			"mo_oauth_option"	: "query_submit",
			"mo_oauth_email" 	: jQuery("#person_email").val(),
			"mo_oauth_query"  	: jQuery("#person_query").val(),
			"mo_oauth_nonce" 	: jQuery("#nonce").val()
		};
		jQuery("#mo-support-msg").empty();
		jQuery("#mo-support-msg").append("We are processing your request. Please wait!!");
		jQuery("#help-container").show();
		jQuery(".support-form-container").hide();
		jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
			jQuery("#mo-support-msg").empty();
			jQuery("#mo-support-msg").append(response);
		});
	});

	/* class-mo-oauth-client-setup-wizard.php: wizard navigation and test flow. */
	mooauth_auto_fill_form();
	jQuery("#mo-btn-next, .mo-button--secondary, .mo-oauth-save-draft, .close, .mo-oauth-skip-setup, #mo-btn-finish").click(function(e){
		target = e.target.id;
		jQuery("#"+target).prop("disabled",true);
		if("mo-btn-next" == target){
			var data= mooauth_get_data("save_draft","next");
		}
		if("mo-btn-back" == target){
			var data= mooauth_get_data("save_draft","back");
		}
		if("mo-link-draft" == target){
			var data= mooauth_get_data("save_draft","draft");
		}
		if("mo-btn-finish" == target){
			var data= mooauth_get_data("save_app","finish");
		}
		if("mo-btn-close" == target){
			var data= mooauth_get_data("save_draft","close");
		}
		if("mo-btn-skip" == target){
			var data= mooauth_get_data("save_draft","skip");
		}
		if("mo-btn-next" == target || "mo-btn-back" == target){
			jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
				jQuery("#"+target).prop("disabled",false);
				if (undefined != response.mo_oauth_discovery_validation)
					var discovery_validation = response.mo_oauth_discovery_validation;
				else
					var discovery_validation = "";
				if("invalid" == discovery_validation){
					jQuery(".mo-valid-icon").addClass("fa-thumbs-down");
					jQuery(".mo-valid-icon").removeClass("fa-thumbs-up");
					jQuery(".mo-oauth-troubleshooting").show();
					mooauth_get_discovery_troubleshooting(response.mo_oauth_discovery_url,response.mo_oauth_input,response.mo_oauth_appId);
				}else{
					jQuery("#Domain").val(response.domain);
					jQuery(".mo-oauth-troubleshooting").hide();
					if(undefined != response.mo_oauth_scopes_list && "" != response.mo_oauth_scopes_list ){
						if(!Array.isArray(response.mo_oauth_scopes_list))
							var scope_list = JSON.parse(response.mo_oauth_scopes_list);
						else
							var scope_list = response.mo_oauth_scopes_list;
						jQuery(".ui.fluid.dropdown").dropdown({values:scope_list});
						jQuery("#scope_list").val(response.mo_oauth_scopes_list);
					}else{
						jQuery(".ui.fluid.dropdown").dropdown({values:[]});
						jQuery("#scope_list").val("");
					}
					if(undefined != response.mo_oauth_scopes && "" != response.mo_oauth_scopes && "[\"\"]" != response.mo_oauth_scopes  ){
						if(!Array.isArray(response.mo_oauth_scopes))
							var scopes = JSON.parse(response.mo_oauth_scopes);
						else
							var scopes = response.mo_oauth_scopes;

						jQuery(".ui.dropdown.fluid").dropdown({allowAdditions: true,clearable:true});
						jQuery(".ui.fluid.dropdown").dropdown("clear");
						jQuery(".ui.fluid.dropdown").dropdown("set selected",scopes);
					}else{
						jQuery(".ui.dropdown.fluid").dropdown({allowAdditions: true,clearable:true});
					}
					if("valid" == discovery_validation){
						jQuery(".mo-valid-icon").addClass("fa-thumbs-up");
						jQuery(".mo-valid-icon").removeClass("fa-thumbs-down");
					}else{
						jQuery(".mo-valid-icon").removeClass("fa-thumbs-up");
						jQuery(".mo-valid-icon").removeClass("fa-thumbs-down");
					}
					if("mo-btn-next" == target){
						mooauth_steps_icr();
					}
					else{
						mooauth_steps_dcr();
					}
				}
			});
		}
		else if("mo-btn-close" == target || "mo-link-draft" == target || "mo-btn-skip" == target){
			jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
				window.location.href = window.location.pathname + "?page=mo_oauth_settings&tab=config";
			});
		}
		else{
			var mo_response = mooauth_input_validation();
			if(mo_response == "success"){
				jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
					if("mo-btn-finish" == target){
						mooauth_testConfiguration(mo_oauth_ajax_object.site_url);
						mo_oauth_test_ajax_count = 0;
						mooauth_get_test_result();
						mo_oauth_trace_test_progress = setInterval(mooauth_get_test_result, 5000);
					}
					jQuery("#"+target).removeAttr("disabled");
					jQuery(".mo-oauth-test-in-progress").show();
					jQuery(".mo-oauth-test-in-failed").hide();
					jQuery(".mo-oauth-test-successed").hide();
					mooauth_steps_icr();
				});
			}else{
				jQuery("#"+target).removeAttr("disabled");
			}
		}
	});

	jQuery(".mo-btn-test-finish_class").click(function(){
		var data = {
			"action": "mo_outh_ajax",
			"mo_oauth_option": "test_finish",
			"mo_oauth_nonce" : jQuery("#nonce").val()
		}
		jQuery.post(mo_oauth_ajax_object.ajax_url, data, function(response){
			window.location.href = window.location.pathname + "?page=mo_oauth_settings&tab=config";
		});
	});

	jQuery("#mo-btn-test-re-run").click(function(){
		jQuery(".mo-oauth-troubleshooting").hide();
		var data= mooauth_get_data("save_app","finish");
		if("mo-btn-finish" == target || "mo-btn-next" == target){
			mooauth_testConfiguration(mo_oauth_ajax_object.site_url);
			mo_oauth_test_ajax_count = 0;
			mooauth_get_test_result();
			mo_oauth_trace_test_progress = setInterval(mooauth_get_test_result, 5000);
		}
		jQuery("#"+target).removeAttr("disabled");
		jQuery(".mo-oauth-test-in-progress").show();
		jQuery(".mo-oauth-test-in-failed").hide();
		jQuery(".mo-oauth-test-successed").hide();
	});

	jQuery("#mo-support-msg-hide").click(function(){
		jQuery("#help-container").hide();
	});
	jQuery("#service-btn").click(function(){
		jQuery(".support-form-container").show();
	});
	jQuery("#mo-support-form-hide").click(function(){
		jQuery(".support-form-container").hide();
	});
});
