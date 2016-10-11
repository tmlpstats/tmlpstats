import { compositeKey } from '../../reusable/sortable_collection'
import SortableReduxLoader from '../../reusable/redux_loader/sortable'
import { LoadingMultiState, InlineBulkWork, MessageManager } from '../../reusable/reducers'
import Api from '../../api'

export const teamMembersSorts = [
    {
        key: 'teamYear_first_last',
        label: 'Default',
        comparator: compositeKey([['teamYear', 'number'], ['firstName', 'string'], ['lastName', 'string']])
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
]

export const teamMembersData = new SortableReduxLoader({
    prefix: 'submission.team_members',
    extraLMS: ['saveState'],
    loader: Api.TeamMember.allForCenter,
    sortable: {
        key_prop: 'id',
        sort_by: 'teamYear_first_last',
        sorts: teamMembersSorts
    }
})

export const weeklyReportingSave = new LoadingMultiState('team_members/saveWeeklyReporting')
export const weeklyReportingData = new InlineBulkWork('team_members/weeklyReporting')

export const messages = new MessageManager('team_members')
