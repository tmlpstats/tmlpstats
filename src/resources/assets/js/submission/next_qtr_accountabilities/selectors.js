import _ from 'lodash'
import { createSelector } from 'reselect'

import { makeAccountabilitiesSelector } from '../core/selectors'
import { appsCollection } from '../applications/data'

const accSelector = makeAccountabilitiesSelector('team')

const REPROMISABLE_ACCOUNTABILITIES = [
    't1tl',
    't2tl',
    'statistician',
    'logistics',
    'statisticianApprentice',
    'cap',
    'cpc',
    'gitw',
    'lf',
    't1x',
    't2x'
]

export const repromisableAccountabilities = createSelector(
    accSelector,
    (ordered) => {
        // the 'name' property is actually more like a slug... create a lookup via that.
        const rekeyed = _.keyBy(ordered, 'name')
        return REPROMISABLE_ACCOUNTABILITIES.map((id) => rekeyed[id])
    }
)

export const selectablePeople = createSelector(
    (state) => state.submission.core.lookups.team_members,
    (state) => state.submission.applications.applications,
    (team_members, applications) => {
        let teamMembersLookup = _.keyBy(team_members, 'teamMemberId') // yuck, old data format. Fix soon please.
        let orderedApplications = []
        if (applications && applications.data) {
            orderedApplications = appsCollection.opts.getSortedApplications(applications)
        }
        return {
            teamMembers: teamMembersLookup,
            orderedTeamMembers: team_members,
            applications: applications.data,
            orderedApplications,
        }
    })
