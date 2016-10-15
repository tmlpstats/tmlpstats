import { modelReducer, formReducer, actions as formActions } from 'react-redux-form'
import { ReduxLoader } from './base'

export default class FormReduxLoader extends ReduxLoader {
    constructor(opts) {
        super(opts)
        this.replace_action = opts.prefix + '/replace'
        this.replace_items_action = opts.prefix + '/replace_items'
    }

    _extraReducers(opts) {
        if (opts.formReducer) {
            return {form: formReducer(opts.model)}
        }
    }

    dataReducer(opts) {
        return modelReducer(opts.model)
    }

    replaceCollection(value) {
        return formActions.load(this.opts.model, value)
    }

    replaceItem(key, value) {
        return formActions.load(`${this.opts.model}.${this.opts.key}`, value)
    }
}


