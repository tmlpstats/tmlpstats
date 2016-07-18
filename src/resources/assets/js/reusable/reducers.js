import { objectAssign } from './ponyfill'

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
            case 'new':
                return NEW
            default:
                return action.payload
            }
        }
        return state
    }
}

export class LoadingMultiState {
    constructor(actionType) {
        this.actionType = actionType
    }

    actionCreator() {
        const actionType = this.actionType
        return (newState) => {
            if (newState.error) {
                newState = objectAssign({}, FAILED, newState)
            }
            return {type: actionType, payload: newState}
        }
    }

    reducer() {
        return loadingMultiState(this.actionType)
    }
}
