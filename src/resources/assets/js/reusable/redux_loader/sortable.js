import { objectAssign } from './ponyfill'
import SortableCollection from './sortable_collection'
import { rebindActionCreators } from './redux_loaders'

export function sortable_wrapper(sortable_opts) {
    if (!sortable_opts) {
        sortable_opts = {}
    }

    return (opts) => {
        const s_opts = objectAssign({}, sortable_opts, {name: opts.prefix})
        const collection = new SortableCollection(s_opts)
        return {
            dataReducer: collection.reducer(),
            actionCreators: rebindActionCreators(['replaceItem', 'replaceCollection', 'changeSortCriteria'], collection)
        }
    }
}
