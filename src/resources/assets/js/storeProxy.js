/**
 * StoreProxy is needed currently to deal with potential circular import issues.
 *
 * If anything is imported by a thing which might be part of the tree involving
 * the store (reducers, data.js files, etc) then it will have weird reference
 * errors importing the store.
 *
 * Once the store is connected, the `dispatch` and `getState` functions point
 * to the ones on the real store.
 */
class StoreProxy {
    constructor() {
        this.store = null
        this.delayed = []
    }

    /**
     * Queues actions that might've been dispatched before the store is connected.
     *
     * We expect this to be replaced by 'setStore' with the real dispatcher later.
     */
    dispatch(x) {
        this.delayed.push(x)
    }

    /** Fake getState should be replaced by the real store later. */
    getState() {
        return this.store? this.store.getState() : undefined
    }

    setStore(store) {
        this.store = store
        // Overwrite dispatch and getState, rebinding them just in case.
        this.dispatch = store.dispatch.bind(store)
        this.getState = store.getState.bind(store)

        // Dispatch any previously delayed items.
        while (this.delayed.length) {
            store.dispatch(this.delayed.shift())
        }
    }
}

export default new StoreProxy()
