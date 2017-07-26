import _ from 'lodash'
import { setSubmissionLookups } from '../../submission/core/actions'
/* eslint-disable quotes */

let currentId = 10000

function buildPopInput(input) {
    return function popInput(key, defaultVal) {
        if (input[key]) {
            return input[key]
            delete input[key]
        }
        return defaultVal
    }
}

function legacyTeamMember(input) {
    const popInput = buildPopInput(input)
    const tmId = popInput('teamMemberId') || currentId++
    const personId = popInput('personId') || currentId++
    let defaults = {
        "id":currentId++,
        "teamMemberId":tmId,
        "atWeekend":true,
        "xferOut":false,
        "xferIn":false,
        "ctw":false,
        "withdrawCodeId":null,
        "wbo":false,
        "rereg":false,
        "excep":false,
        "travel":true,
        "room":true,
        "comment":"",
        "gitw":true,
        "tdo":false,
        "statsReportId":currentId++,
        "createdAt":"2017-07-21 23:22:47",
        "updatedAt":"2017-07-21 23:22:47",
        "teamMember":{
            "id":tmId,
            "personId":personId,
            "teamYear":"1",
            "incomingQuarterId":33,"isReviewer":false,"createdAt":"2016-08-27 00:37:43","updatedAt":"2016-08-27 00:37:43",
            "person":{
                "id":personId,
                "firstName":popInput('firstName', 'First Name'),
                "lastName":popInput('lastName', 'Last Name'),
                "phone":popInput('phone', "(123) 456-7890"),
                "email": popInput('email', "email123@gmail.com"),
                "centerId":popInput('centerId', 16),
                "identifier":"q:2016-08-19:1",
                "unsubscribed":false,
                "createdAt":"2016-04-23 00:37:05",
                "updatedAt":"2017-02-25 01:27:43"
            }
        }
    }
    // assign over any remaining input
    return Object.assign(defaults, input)
}

const DENVER_CENTER = {
    "id":16,"name":"Denver","abbreviation":"DEN","teamName":null,"regionId":2,"statsEmail":"statsemail@example.com","active":true,"sheetFilename":"Denver","sheetVersion":"17.1.3",
    "timezone":"America\/Denver","createdAt":"2015-02-27 16:41:37","updatedAt":"2017-04-12 23:22:55"
}

const DENVER_CENTER_WITH_REGION = Object.assign({}, DENVER_CENTER, {
    "region":{"id":2,"abbreviation":"West","name":"West","email":"west@example.com","parentId":1,"createdAt":"2015-10-14 03:56:40","updatedAt":"2015-10-14 03:56:40","parent":{"id":1,"abbreviation":"NA","name":"North America","email":"na@example.com","parentId":null,"createdAt":"2015-10-14 03:56:40","updatedAt":"2015-10-14 03:56:40"}}
})

function defaultAccountabilities() {
    return [
        {"id":11,"name":"cap","context":"team","display":"Access to Power"},
        {"id":9,"name":"classroomLeader","context":"team","display":"Classroom Leader"},
        {"id":12,"name":"cpc","context":"team","display":"Power to Create"},
        {"id":15,"name":"gitw","context":"team","display":"Game in the World"},
        {"id":3,"name":"globalLeader","context":"program","display":"Global Leader"},
        {"id":2,"name":"globalStatistician","context":"program","display":"Global Statistician"},
        {"id":16,"name":"lf","context":"team","display":"Landmark Forum"},
        {"id":17,"name":"logistics","context":"team","display":"Logistics"},
        {"id":8,"name":"programManager","context":"team","display":"Program Manager"},
        {"id":1,"name":"regionalStatistician","context":"program","display":"Regional Statistician"},
        {"id":4,"name":"statistician","context":"team","display":"Statistician"},
        {"id":5,"name":"statisticianApprentice","context":"team","display":"Statistician Apprentice"},
        {"id":6,"name":"t1tl","context":"team","display":"Team 1 Team Leader"},
        {"id":13,"name":"t1x","context":"team","display":"T1 Expansion"},
        {"id":7,"name":"t2tl","context":"team","display":"Team 2 Team Leader"},
        {"id":14,"name":"t2x","context":"team","display":"T2 Expansion"}
    ]
}

function defaultWithdrawCodes() {
    let base = [
        {"id":1,"code":"AP","display":"Chose another program"},
        {"id":2,"code":"NW","display":"Doesn't want the training"},
        {"id":3,"code":"FIN","display":"Financial"},
        {"id":4,"code":"FW","display":"Moved to a future weekend"},
        {"id":5,"code":"MOA","display":"Moved out of area"},
        {"id":6,"code":"NA","display":"Not approved"},
        {"id":7,"code":"OOC","display":"Out of communication"},
        {"id":8,"code":"T","display":"Time conversation"},
        {"id":9,"code":"RE","display":"Registration error"},
        {"id":10,"code":"WB","display":"Well-being out"}
    ]
    base.forEach((item) => {
        Object.assign(item, {description: null, "createdAt":"2015-10-14 03:56:40","updatedAt":"2015-10-14 03:56:40"})
    })
    return base
}

export function buildLookups() {
    return {
        "success":true,"id":16,
        "user":{"canSkipSubmitEmail":true},
        "validRegQuarters":[{"quarterId":37,"centerId":16,"firstWeekDate":"2017-08-25","quarter":{"t1Distinction":"Opportunity","year":2017,"id":37},"startWeekendDate":"2017-08-18","endWeekendDate":"2017-11-17","classroom1Date":"2017-09-08","classroom2Date":"2017-10-06","classroom3Date":"2017-11-03"},{"quarterId":38,"centerId":16,"firstWeekDate":"2017-11-24","quarter":{"t1Distinction":"Action","year":2017,"id":38},"startWeekendDate":"2017-11-17","endWeekendDate":"2018-02-23","classroom1Date":"2017-12-08","classroom2Date":"2018-01-05","classroom3Date":"2018-02-02"}],
        "validStartQuarters":[{"quarterId":33,"centerId":16,"firstWeekDate":"2016-08-26","quarter":{"t1Distinction":"Action","year":2016,"id":33},"startWeekendDate":"2016-08-19","endWeekendDate":"2016-11-18","classroom1Date":"2016-09-09","classroom2Date":"2016-09-30","classroom3Date":"2016-10-28"},{"quarterId":34,"centerId":16,"firstWeekDate":"2016-11-25","quarter":{"t1Distinction":"Completion","year":2016,"id":34},"startWeekendDate":"2016-11-18","endWeekendDate":"2017-02-17","classroom1Date":"2016-12-09","classroom2Date":"2017-01-06","classroom3Date":"2017-02-03"},{"quarterId":35,"centerId":16,"firstWeekDate":"2017-02-24","quarter":{"t1Distinction":"Relatedness","year":2017,"id":35},"startWeekendDate":"2017-02-17","endWeekendDate":"2017-06-02","classroom1Date":"2017-03-10","classroom2Date":"2017-04-07","classroom3Date":"2017-05-05"},{"quarterId":36,"centerId":16,"firstWeekDate":"2017-06-09","quarter":{"t1Distinction":"Possibility","year":2017,"id":36},"startWeekendDate":"2017-06-02","endWeekendDate":"2017-08-18","classroom1Date":"2017-06-16","classroom2Date":"2017-07-07","classroom3Date":"2017-07-28"}],
        "lookups":{
            "withdraw_codes": defaultWithdrawCodes(),
            "team_members":[
                legacyTeamMember({firstName: 'P1', lastName: 'Last1'}),
                legacyTeamMember({firstName: 'P2', lastName: 'Last2'})
            ],
            "center": DENVER_CENTER_WITH_REGION,
            "centers":[DENVER_CENTER],  // TODO: decide whether we're going to give more centers later.
        },
        "accountabilities": defaultAccountabilities(),
        "currentQuarter":{"quarterId":36,"centerId":16,"firstWeekDate":"2017-06-09","quarter":{"t1Distinction":"Possibility","year":2017,"id":36},"startWeekendDate":"2017-06-02","endWeekendDate":"2017-08-18","classroom1Date":"2017-06-16","classroom2Date":"2017-07-07","classroom3Date":"2017-07-28"},
        "systemMessages":[
            {"id":1,"createdAt":"2017-06-29 04:23:20","centerId":null,"regionId":1,"section":"submission","level":"info","title":"TITLE HERE","content":"MARKDOWN HERE"}
        ]
    }
}

export function defaultSubmissionLookups() {
    return setSubmissionLookups(buildLookups())
}
