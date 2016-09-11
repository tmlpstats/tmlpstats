import { objectAssign } from '../ponyfill'
import SortableCollection from '../sortable_collection'
import { ReduxLoader, rebindActionCreators } from './base'

const SC_ACTIONS = ['replaceItem', 'replaceCollection', 'changeSortCriteria', 'ensureCollection']

export default class SortableReduxLoader extends ReduxLoader {
    constructor(opts) {
        super(opts)

        var sc = this.sc = new SortableCollection(objectAssign({name: opts.prefix}, opts.sortable))

        objectAssign(this, rebindActionCreators(SC_ACTIONS, sc))
    }

    dataReducer(opts) {
        const { collection_reducer, check_resort } = opts
        return this.sc.reducer(collection_reducer, check_resort)
    }

    iterItems(state, callback) {
        if (!state.collection && state.data) {
            state = state.data
        }
        return this.sc.iterItems(state, callback)
    }
}
