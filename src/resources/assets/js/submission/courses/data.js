import SortableCollection, { compositeKey } from '../../reusable/sortable_collection'
import { LoadingMultiState, MessageManager } from '../../reusable/reducers'

export const coursesSorts = [
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
]

export const coursesCollection = new SortableCollection({
    name: 'submission.courses',
    key_prop: 'id',
    sort_by: 'type_startDate',
    sorts: coursesSorts
})

export const courseTypeMap = {
    'CAP': 'Access to Power',
    'CPC': 'Power to Create'
}

export const coursesLoad = new LoadingMultiState('courses/initialLoadState')
export const saveCourseLoad = new LoadingMultiState('courses/saveCourseState')

export const messages = new MessageManager('courses')
