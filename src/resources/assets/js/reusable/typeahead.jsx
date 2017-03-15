/**
 * Helpers for doing the typeahead pattern in a bootstrappy way.
 *
 * We are also trying to do a minimal use case of react-typeahead here, because
 * the long term desire here is to move to a controlled component plus a higher
 * order component, both of which are intended to work with redux, and the latter
 * to work with react-redux-form.
 */
import React, { PureComponent, Component } from 'react'
import {Typeahead as RTypeahead} from 'react-bootstrap-typeahead'
import { defaultMemoize } from 'reselect'
import _ from 'lodash'

import { connectCustomField, formActions } from './form_utils'
import { rebind } from './dispatch'
import { objectAssign } from './ponyfill'


export class Typeahead extends PureComponent {
    render() {
        return <RTypeahead {...this.props} />
    }
}

/**
 * Helpful typeahead for when you want to select on an object
 * where the object has a key property that is the 'value' of the item.
 */
export class SimpleTypeahead extends PureComponent {
    static defaultProps = {
        keyProp: 'id',
        labelProp: 'label'
    }

    constructor(props) {
        super(props)
        this.keyedItems = defaultMemoize(
            (input, keyProp) => {
                console.log('keyBy:', input, keyProp)
                return _.keyBy(input, keyProp)
            }
        )

    }
    render() {
        const { value, items, keyProp, labelProp, ...rest } = this.props
        let oprops = objectAssign({}, rest, {
            labelKey: labelProp,
            options: items,
            selected: this.transformValue(value)
        })
        return <RTypeahead {...oprops} />
    }

    transformValue(value) {
        const keyed = this.keyedItems(this.props.items, this.props.keyProp)
        if (value) {
            if (value.map) {
                return value.map(v => { return keyed[v] || 'wot' })
            } else {
                return [value]
            }
        }
        return []
    }
}

@connectCustomField
export class FormTypeahead extends Component {
    constructor(props) {
        super(props)
        rebind(this, 'onChange')
    }

    render() {
        const { modelValue, model, ...rest} = this.props
        return <SimpleTypeahead value={modelValue} onChange={this.onChange} {...rest} />
    }

    onChange(value) {
        if (this.props.keyProp) {
            value = value.map(x => x[this.props.keyProp])
        }
        this.props.dispatch(formActions.change(this.props.model, this.props.multiple? value : value[0]))
    }
}
