import SortableCollection, { compositeKey } from '../../reusable/sortable_collection'
import { LoadingMultiState, MessageManager } from '../../reusable/reducers'

export const appsSorts = [
    {
        key: 'teamYear_first_last',
        label: 'Default',
        comparator: compositeKey([['teamYear', 'number'], ['firstName', 'string'], ['lastName', 'string']])
    },
    {
        key: 'first_last',
        label: 'First, Last',
        comparator: compositeKey([['firstName', 'string'], ['lastName', 'string']])
    }
]

export const appsCollection = new SortableCollection({
    name: 'submission.applications',
    key_prop: 'id',
    sort_by: 'teamYear_first_last',
    sorts: appsSorts
})

export const applicationsLoad = new LoadingMultiState('applications/initialLoadState')
export const saveAppLoad = new LoadingMultiState('applications/saveAppState')

export const messages = new MessageManager('applications')
