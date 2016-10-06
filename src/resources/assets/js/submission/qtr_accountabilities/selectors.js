import _ from 'lodash'
import { createSelector } from 'reselect'

import { makeAccountabilitiesSelector, getLabelTeamMember } from '../core/selectors'
import { appsCollection } from '../applications/data'

const accSelector = makeAccountabilitiesSelector('team')

const chosenAccountabilities = [
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
        return chosenAccountabilities.map((id) => rekeyed[id])
    }
)

export const selectablePeople = createSelector(
    (state) => state.submission.core.lookups.team_members,
    (state) => state.submission.applications.applications,
    (team_members, applications) => {
        console.log('selector', team_members, applications)
        var allNames = []
        var nameToKey = {}
        if (team_members) {
            team_members.forEach((tmd, idx) => {
                const fullName = getLabelTeamMember(tmd)
                allNames.push(fullName)
                nameToKey[fullName] = ['teamMember', tmd.teamMemberId, idx]
            })
        }
        if (applications && applications.collection) {
            appsCollection.iterItems(applications, (app, appId) => {
                console.log('doApps', app)
                const fullName = `${app.firstName} ${app.lastName} (incoming)`
                allNames.push(fullName)
                nameToKey[fullName] = ['application', appId]
            })
            applications.sortedKeys.forEach
        }
        return {
            team_members,
            applications,
            allNames,
            nameToKey
        }
    })
