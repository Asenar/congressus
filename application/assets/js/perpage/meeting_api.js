/*
    Copyright 2019-2021 Cédric Levieux, Parti Pirate

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
    along with Congressus.  If not, see <http://www.gnu.org/licenses/>.
*/
/* global $ */
/* global socket */
/* global sendEvent */
/* global getUserId */

function m_ping(meetingId, userId) {
//	console.log("m_ping");
	const event = {id: meetingId, event: "m_ping"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event);
	}
	else {
		$.post("meeting_api.php?method=do_ping", event, function(data) {
		}, "json");
	}
}

function m_computeVote(motionId, save, force, callback) {
//	console.log("m_computeVote");
    const event = {motionId: motionId, save: save, force: force, event: "m_computeVote"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting/do_computeVote.php", event, callback, "json");
	}
}

function m_vote(motionId, propositionId, votePower, callback) {
//	console.log("m_vote");
    const event = {motionId: motionId, propositionId: propositionId, power: votePower, event: "m_vote"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_vote", event, callback, "json");
	}
}

function m_getAgendaPoint(meetingId, agendaId, requestId, callback) {
//	console.log("m_getAgendaPoint");
    const event = {id: meetingId, pointId: agendaId, requestId: requestId, event: "m_getAgendaPoint"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_getAgendaPoint", event, callback, "json");
	}
}

function m_getAgenda(meetingId, callback) {
//	console.log("m_getAgenda");
    const event = {id: meetingId, event: "m_getAgenda"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_getAgenda", event, callback, "json");
	}
}

function m_getPeople(meetingId, callback) {
//	console.log("m_getPeople");
    const event = {id: meetingId, event: "m_getPeople"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_getPeople", event, callback, "json");
	}
}

function m_getEvents(meetingId, callback) {
//	console.log("m_getEvents");
    const event = {meetingId: meetingId, event: "m_getEvents"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_getEvents", event, callback, "json");
	}
}

function m_moveObject(meetingId, agendaId, objectId, objectType, destinationMeetingId, destinationAgendaId, callback) {
    const event = {	meetingId: meetingId,
				    agendaId: agendaId,
				    objectId: objectId,
				    objectType: objectType,
				    destinationMeetingId: destinationMeetingId,
				    destinationAgendaId: destinationAgendaId,
    				event: "m_moveObject"};

	if (typeof socket !== "undefined" && socket) {
		sendEvent(event, callback);
	}
	else {
        $.post("meeting_api.php?method=do_moveObject", event, callback, "json");
	}
}