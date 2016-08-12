import SortableCollection, { compositeKey } from '../../reusable/sortable_collection'
import { LoadingMultiState } from '../../reusable/reducers'

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

export const teamMembersCollection = new SortableCollection({
    name: 'submission.class_list',
    key_prop: 'id',
    sort_by: 'teamYear_first_last',
    sorts: classListSorts
})

export const classListLoad = new LoadingMultiState('class_list/initialLoadState')
