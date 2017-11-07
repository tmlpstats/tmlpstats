import _ from 'lodash'
import Immutable from 'immutable'
import { createSelector } from 'reselect'

import { makeAccountabilitiesSelector } from '../core/selectors'
import { appsCollection } from '../applications/data'
import { teamMembersData } from '../team_members/data'

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
    (state) => state.submission.team_members.teamMembers,
    (state) => state.submission.applications.applications,
    (tmd, applications) => {
        const rawSorted = teamMembersData.opts.getSortedMembers(tmd)
        const orderedTeamMembers = Immutable.List(rawSorted)
            .filter(x => parseInt(x.id) > 0)

        const teamMembers = Immutable.Map(orderedTeamMembers.map(x => [x.id, x]))


        let orderedApplications = []
        if (applications && applications.data) {
            orderedApplications = appsCollection.opts.getSortedApplications(applications)
        }
        return {
            teamMembers: teamMembers,
            orderedTeamMembers,
            applications: applications.data,
            orderedApplications,
        }
    })
