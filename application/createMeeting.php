<?php /*
    Copyright 2015-2018 Cédric Levieux, Parti Pirate

    This file is part of Congressus.

    Congressus is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Congressus is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Congressus.  If not, see <https://www.gnu.org/licenses/>.
*/
include_once("header.php");
include_once("config/memcache.php");

$groupKeyLabels = array();

foreach($config["modules"]["groupsources"] as $groupSourceKey) {
	$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
	
	if ($groupSource->getGroupKeyLabel()) {
		$groupKeyLabels[] = $groupSource->getGroupKeyLabel();
	}
}

$meetingBo = MeetingBo::newInstance($connection, $config);
$memcache = openMemcacheConnection();

$memcacheKey = "createMeetingMeetings";
$json = $memcache->get($memcacheKey);

if (!$json || true) {
	$filters = array();
    $filters["with_status"] = array("open", "closed", "template");
	$meetings = $meetingBo->getByFilters($filters);
	
	$sortedMeetings = array("template_meeting" => array(), "template_construction" => array(), "meeting" => array(), "construction" => array());
	foreach($meetings as $meeting) {
		$superType = ($meeting["mee_status"] == "template" ? "template_" : "") . $meeting["mee_type"];
		$sortedMeetings[$superType][] = $meeting;
	}
	
	$json = json_encode($sortedMeetings, JSON_NUMERIC_CHECK);

	if (!$memcache->replace($memcacheKey, $json, 60)) {
		$memcache->set($memcacheKey, $json, 60);
	}
}
else {
	$sortedMeetings = json_decode($json, true);
}

$templateId = isset($_REQUEST["templateId"]) ? $_REQUEST["templateId"] : -1;

?>

<div class="container theme-showcase meeting" role="main">
	<ol class="breadcrumb">
		<li><a href="index.php"><?php echo lang("breadcrumb_index"); ?></a></li>
		<li class="active"><?php echo lang("breadcrumb_createMeeting"); ?></li>
	</ol>

	<form action="meeting/do_createMeeting.php" method="post" class="form-horizontal" id="create-meeting-form">

		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist" id="creation-step-tabs">
			<li role="presentation" class="active"><a href="#info" aria-controls="info" role="tab" data-toggle="tab"><?php echo lang("createMeeting_information"); ?></a></li>
			<li role="presentation"><a href="#notice" aria-controls="notice" role="tab" data-toggle="tab"><?php echo lang("createMeeting_convocation"); ?></a></li>
			<li role="presentation"><a href="#agenda" aria-controls="agenda" role="tab" data-toggle="tab"><?php echo lang("createMeeting_agenda"); ?></a></li>
			<li role="presentation"><a href="#location" aria-controls="location" role="tab" data-toggle="tab"><?php echo lang("createMeeting_location"); ?></a></li>
		</ul>

		<!-- Tab panes -->
<div class="tab-content">
	<div role="tabpanel" class="tab-pane active padding-top-5" id="info">

		<div class="form-group">
			<label for="mee_label" class="col-md-4 control-label"><?php echo lang("meeting_name"); ?> :</label>
			<div class="col-md-8">
				<input type="text" class="form-control input-md" id="mee_label" name="mee_label" />
			</div>
		</div>
		<div class="alert alert-danger simply-hidden" id="label-error-alert">
			<?php echo lang("createMeeting_labelError"); ?>
		</div>
		<div class="form-group">
			<label for="mee_date" class="col-md-4 control-label"><?php echo lang("createMeeting_datetime"); ?></label>
			<div class="col-md-2">
				<input type="date" class="form-control input-md"
					placeholder="aaaa-mm-jj" id="mee_date" name="mee_date" />
			</div>
			<div class="col-md-2">
				<input type="time" class="form-control input-md"
					placeholder="hh:mm" id="mee_time" name="mee_time" />
			</div>
		</div>
		<div class="alert alert-danger simply-hidden" id="date-time-error-alert">
			<?php echo lang("createMeeting_datetimeError"); ?>
		</div>
		<div class="form-group">
			<label for="mee_expected_duration" class="col-md-4 control-label"><?php echo lang("createMeeting_length"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_expected_duration" name="mee_expected_duration">
					<option    value="60">1 <?php echo lang("createMeeting_length_hour"); ?></option>
					<option   value="120">2 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option   value="180">3 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option   value="240">4 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option   value="480">8 <?php echo lang("createMeeting_length_hours"); ?></option>
					<option  value="1440">1 <?php echo lang("createMeeting_length_day"); ?></option>
					<option  value="2880">2 <?php echo lang("createMeeting_length_days"); ?></option>
					<option  value="4320">3 <?php echo lang("createMeeting_length_days"); ?></option>
					<option value="10080">7 <?php echo lang("createMeeting_length_days"); ?></option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_tyoe" class="col-md-4 control-label"><?php echo lang("createMeeting_base_type"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_type" name="mee_type">
					<option value="meeting"><?php echo lang("createMeeting_base_type_meeting"); ?></option>
					<option value="construction"><?php echo lang("createMeeting_base_type_construction"); ?></option>
				</select>
			</div>
		</div>

		<div class="well well-sm type-meeting type-explanation" style="display: none;">
			<p><?php echo lang("createMeeting_type_meeting_explanation"); ?></p>
		</div>

		<div class="well well-sm type-construction type-explanation" style="display: none;">
			<p><?php echo lang("createMeeting_type_construction_explanation"); ?></p>
		</div>

		<div class="row text-center">
			<button class="btn btn-primary show-notice" type="button" ><?php echo lang("common_next"); ?></button>
		</div>

		<hr>
		
		<label><?php echo lang("createMeeting_copy_label"); ?></label>

		<div class="form-group">
			<label for="mee_id" class="col-md-4 control-label"><?php echo lang("createMeeting_copy_from"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_id" name="mee_id">
					<option value="-1" <?php if ($templateId == -1) echo 'selected="selected"'; ?>><?php echo lang("createMeeting_select_meeting"); ?></option>
					<?php
						foreach($sortedMeetings as $type => $typeMeetings) {
							if (count($typeMeetings)) {
					?>
					<optgroup label="<?php echo lang("createMeeting_base_type_$type"); ?>" data-type="<?php echo str_replace("template_", "", $type); ?>">
						<?php	foreach($typeMeetings as $meeting) { ?>
						<option value="<?php echo $meeting["mee_id"]; ?>" <?php if ($templateId == $meeting["mee_id"]) echo 'selected="selected"'; ?>><?php echo $meeting["mee_label"]; ?></option>
						<?php	} ?>
					</optgroup>
					<?php
							}
						}
					?>
				</select>
			</div>
		</div>

		<div class="row text-center">
			<button class="btn btn-primary copy-meeting-btn" type="button" disabled=disabled><?php echo lang("common_copy"); ?></button>
		</div>

	</div>
<!-- NOTICE -->
	<div role="tabpanel" class="tab-pane padding-top-5" id="notice">

		<div class="form-group">
			<label for="not_target_type" class="col-md-4 control-label"><?php echo lang("notice_source"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="not_target_type" name="not_target_type">

					<?php	foreach($groupKeyLabels as $groupKeyLabel) { ?>
						<option value="<?php echo $groupKeyLabel["key"]; ?>"><?php echo $groupKeyLabel["label"]; ?></option>
					<?php	} ?>

				</select>
			</div>
		</div>

		<div class="form-group not_mails">
			<label for="not_target_id" class="col-md-4 control-label"><?php echo lang("notice_secondary_source"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="not_target_id" name="not_target_id">

					<?php					
					foreach($config["modules"]["groupsources"] as $groupSourceKey) {
						$groupSource = GroupSourceFactory::getInstance($groupSourceKey);
						$groupSource->getGroupOptions();
					}
					?>

				</select>
			</div>
		</div>

		<div class="form-group mails">
			<label for="not_external_mails" class="col-md-4 control-label">Mails :</label>
			<div class="col-md-4">
				<input type="text" class="form-control input-md" id="not_external_mails" name="not_external_mails" />
			</div>
		</div>

		<div class="form-group">
			<div class="col-md-4 text-right">
				<input type="checkbox" name="not_voting" id="not_voting"
					placeholder="" class=""
					value="1"/>
			</div>
			<div class="col-md-4">
				<label class="form-control labelForCheckbox" for="not_voting"><?php echo lang("notice_has_voting_rights"); ?> <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" title="<?php echo lang("notice_has_voting_rights_help"); ?>"></span></label>
			</div>
		</div>


		<div class="row text-center">
			<button class="btn btn-primary show-agenda" type="button" ><?php echo lang("common_next"); ?></button>
		</div>

	</div>
<!-- END NOTICE -->
<!-- AGENDA -->
	<div role="tabpanel" class="tab-pane padding-top-5" id="agenda">

		<div class="form-group">
			<label for="age_lines" class="col-md-4 control-label"><?php echo lang("agenda_lines"); ?> <span class="glyphicon glyphicon-info-sign" data-toggle="tooltip" data-placement="top" title="<?php echo lang("agenda_lines_help"); ?>"></span></label>
			<div class="col-md-4">                     
				<textarea class="form-control autogrow" id="age_lines" name="age_lines" style="font-family: monospace; "></textarea>
			</div>
		</div>

		<div class="row text-center">
			<button class="btn btn-primary show-location" type="button" ><?php echo lang("common_next"); ?></button>
		</div>

	</div>
<!-- END AGENDA -->
	<div role="tabpanel" class="tab-pane padding-top-5" id="location">


		<div class="form-group">
			<label for="mee_meeting_type_id" class="col-md-4 control-label"><?php echo lang("createMeeting_type"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_meeting_type_id" name="mee_meeting_type_id">
					<option value="1"><?php echo lang("createMeeting_type_meeting"); ?></option>
					<option value="2"><?php echo lang("createMeeting_type_aperitif"); ?></option>
					<option value="3"><?php echo lang("createMeeting_type_generalMeeting"); ?></option>
					<option value="4"><?php echo lang("createMeeting_type_extraordinaryGeneralMeeting"); ?></option>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label for="mee_class" class="col-md-4 control-label"><?php echo lang("createMeeting_visualIndication"); ?></label>
			<div class="col-md-4">
				<select class="form-control input-md" id="mee_class" name="mee_class">
					<option class="event-info" style="color: black;" value="event-info"><?php echo lang("createMeeting_visualIndication_info"); ?></option>
					<option class="event-important" style="color: white;" value="event-important"><?php echo lang("createMeeting_visualIndication_important"); ?></option>
					<option class="event-warning" style="color: white;" value="event-warning"><?php echo lang("createMeeting_visualIndication_warning"); ?></option>
					<option class="event-inverse" style="color: white;" value="event-inverse"><?php echo lang("createMeeting_visualIndication_reversed"); ?></option>
					<option class="event-success" style="color: white;" value="event-success"><?php echo lang("createMeeting_visualIndication_success"); ?></option>
					<option class="event-special" style="color: white;" value="event-special"><?php echo lang("createMeeting_visualIndication_special"); ?></option>
				</select>
			</div>
		</div>

<?php	include("location_form.php"); ?>

		<div class="row text-center">
			<button class="btn btn-success" type="submit"><?php echo lang("common_create"); ?></button>
		</div>

	</div>
</div>


</form>


</div>

<div class="container otbHidden">
</div>

<div class="lastDiv"></div>

<script>
var mumble_count = "<?php echo count($mumble);?>";
var mumble_server = "<?php echo $mumble_server;?>";
var mumble_title = "<?php echo $mumble_title;?>";
var mumble_version = "<?php echo $mumble_version;?>";
</script>
<?php include("footer.php");?>
<script src="assets/js/perpage/location_form.js"></script>

</body>
</html>
