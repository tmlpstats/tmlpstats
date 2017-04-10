class StoreProxy {
    constructor() {
        this.store = null
        this.delayed = []
    }

    dispatch(x) {
        this.delayed.push(x)
    }

    getState() {
        return this.store? this.store.getState() : undefined
    }

    setStore(store) {
        this.store = store
        this.dispatch = store.dispatch
        this.getState = store.getState
        this.delayed.forEach(x => store.dispatch(x))
    }
}

export default new StoreProxy()
