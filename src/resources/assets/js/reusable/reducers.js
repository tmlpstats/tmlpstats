

const
    NEW = {state: 'new', loaded: false},
    LOADING = {state: 'loading', loaded: false},
    LOADED = {state: 'loaded', loaded: true},
    FAILED = {state: 'failed', loaded: false, failed: true}

export function loadingMultiState(actionType) {
    return function(state = NEW, action){
        if (action.type == actionType) {
            switch (action.payload) {
            case 'loading':
                return LOADING
            case 'loaded':
                return LOADED
            case 'failed':
                return FAILED
            case NEW, LOADING, LOADED:
                return action.payload
            }
        }
        return state
    }
}
