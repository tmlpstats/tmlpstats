import Immutable from 'immutable'

import { compositeKey, createSorters, SORT_BY } from '../../reusable/sort-helpers'
import { LoadingMultiState, MessageManager } from '../../reusable/reducers'
import SimpleReduxLoader from '../../reusable/redux_loader/simple'

export const coursesSorts = createSorters([
    {
        key: 'type_startDate',
        label: 'Default',
        comparator: compositeKey([['type', 'string'], ['startDate', 'string']])
    },
    {
        key: 'startDate_type',
        label: 'Date, Type',
        comparator: compositeKey([['startDate', 'string'], ['type', 'string']])
    }
])

export const coursesCollection = new SimpleReduxLoader({
    prefix: 'submission.courses',
    key_prop: 'id',
    useMeta: true,
    initialMeta: Immutable.Map().set(SORT_BY, 'type_startDate')
})

export const courseTypeMap = {
    'CAP': 'Access to Power',
    'CPC': 'Power to Create'
}

export const coursesLoad = new LoadingMultiState('courses/initialLoadState')
export const saveCourseLoad = new LoadingMultiState('courses/saveCourseState')

export const messages = new MessageManager('courses')

export const BLANK_COURSE = {
    startDate: '',
    type: 'CAP',
    quarterStartTer: 0,
    quarterStartStandardStarts: 0,
    quarterStartXfer: 0
}
