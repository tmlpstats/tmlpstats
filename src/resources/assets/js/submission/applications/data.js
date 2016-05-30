import SortableCollection, { compositeKey } from '../../reusable/sortable_collection'

const appsSortsRaw = [
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

export const appsSorts = new Map(appsSortsRaw.map((v) => [v.key, v]))

export const appsCollection = new SortableCollection({
    name: 'submission.applications',
    key_prop: 'tmlpRegistrationId',
    sort_by: 'teamYear_first_last',
    sorts: appsSorts
})
