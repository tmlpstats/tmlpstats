import Immutable from 'immutable'

import { compositeKey, createSorters, collectionSortSelector } from '../../reusable/sort-helpers'
import FormReduxLoader from '../../reusable/redux_loader/rrf'
import { LoadingMultiState, InlineBulkWork, MessageManager } from '../../reusable/reducers'
import Api from '../../api'

export const teamMembersSorts = createSorters([
    {
        key: 'teamYear_quarter_first_last',
        label: 'Year, Quarter',
        comparator: compositeKey([['teamYear', 'number'], ['quarterNumber', 'number'], ['firstName', 'string'], ['lastName', 'string']])
    },
    {
        key: 'first_last',
        label: 'First, Last',
        comparator: compositeKey([['firstName', 'string'], ['lastName', 'string']])
    },
    {
        key: 'last_first',
        label: 'Last, First',
        comparator: compositeKey([['lastName', 'string'], ['firstName', 'string']])
    }
])

export const teamMembersData = new FormReduxLoader({
    prefix: 'submission.team_members',
    model: 'submission.team_members.teamMembers.data',
    extraLMS: ['saveState'],
    actions: {
        load: {
            api: Api.TeamMember.allForCenter,
            setLoaded: true
        },
        save: {
            api: Api.TeamMember.stash,
            setLoaded: true
        }
    },
    useMeta: true,
    initialMeta: Immutable.Map({sort_by: 'first_last'}),
    getSortedMembers: collectionSortSelector(teamMembersSorts),
    getLabel(tm) {
        return `${tm.firstName} ${tm.lastName} (T${tm.teamYear}Q${tm.quarterNumber})`
    }
})

export const weeklyReportingSave = new LoadingMultiState('team_members/saveWeeklyReporting')
export const weeklyReportingData = new InlineBulkWork('team_members/weeklyReporting')

export const messages = new MessageManager('team_members')

export function teamMemberText(teamMember) {
    return (teamMember.firstName || '(First Name Blank)') + ' ' + (teamMember.lastName || '(Last Name Blank)')
}
