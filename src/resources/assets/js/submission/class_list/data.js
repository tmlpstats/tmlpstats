import { compositeKey } from '../../reusable/sortable_collection'
import SortableReduxLoader from '../../reusable/redux_loader/sortable'
import { LoadingMultiState, InlineBulkWork } from '../../reusable/reducers'
import Api from '../../api'

export const classListSorts = [
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
    prefix: 'submission.class_list',
    extraLMS: ['saveState'],
    loader: Api.TeamMember.allForCenter,
    sortable: {
        key_prop: 'id',
        sort_by: 'teamYear_first_last',
        sorts: classListSorts
    }
})

export const weeklyReportingSave = new LoadingMultiState('class_list/saveWeeklyReporting')
export const weeklyReportingData = new InlineBulkWork('class_list/weeklyReporting')
