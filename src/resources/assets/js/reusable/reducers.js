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

const REPLACE_MESSAGES = 'messageManager/replace'
const CLEAR_MESSAGES = 'messageManager/clear'
const RESET_MESSAGES = 'messageManager/reset'

export class MessageManager {
    constructor(namespace) {
         this.namespace = namespace
    }

    reducer() {
        return (state={}, action) => {
            if (action.payload && action.payload.namespace && action.payload.namespace == this.namespace) {
                switch (action.type) {
                case REPLACE_MESSAGES:
                    return objectAssign({}, state, {[action.payload.pk]: action.payload.messages})
                case CLEAR_MESSAGES:
                    return objectAssign({}, state, {[action.payload.pk]: []})
                case RESET_MESSAGES:
                    return {}
                }
            }
            return state
        }
    }

    replace(pk, messages) {
        return {type: REPLACE_MESSAGES, payload: {pk, messages, namespace: this.namespace}}
    }

    clear(pk) {
        return {type: CLEAR_MESSAGES, payload: {pk, namespace: this.namespace}}
    }

    reset(pk) {
        return {type: RESET_MESSAGES, payload: {namespace: this.namespace}}
    }
}
