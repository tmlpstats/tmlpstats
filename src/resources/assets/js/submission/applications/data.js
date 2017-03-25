import Immutable from 'immutable'
import { compositeKey, createSorters, SORT_BY } from '../../reusable/sort-helpers'
import { LoadingMultiState, MessageManager } from '../../reusable/reducers'
import SimpleReduxLoader from '../../reusable/redux_loader/simple'

export const appsSorts = createSorters([
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
])

export const appsCollection = new SimpleReduxLoader({
    prefix: 'submission.applications',
    useMeta: true,
    initialMeta: Immutable.Map().set(SORT_BY, 'teamYear_first_last'),
})

export const applicationsLoad = new LoadingMultiState('applications/initialLoadState')
export const saveAppLoad = new LoadingMultiState('applications/saveAppState')

export const messages = new MessageManager('applications')
