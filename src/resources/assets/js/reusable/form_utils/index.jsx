import ImmutablePropTypes from 'react-immutable-proptypes'
import PropTypes from 'prop-types'
import React from 'react'
import { connect } from 'react-redux'
import { Control as FormControl, Form, Field, actions as formActions } from 'react-redux-form'
import _ from 'lodash'
import { Link } from 'react-router'

import { objectAssign } from '../ponyfill'

export { Form, Field, formActions }
export { DateInput, SimpleDateInput } from './DateInput'

export class SimpleField extends React.Component {
    static propTypes = {
        customField: PropTypes.bool,
        controlProps: PropTypes.object,
        model: PropTypes.string,
        disabled: PropTypes.bool,
        children: PropTypes.node,
        required: PropTypes.bool,
        labelClass: PropTypes.string,
        divClass: PropTypes.string
    }
    static defaultProps = {
        labelClass: 'col-md-2',
        divClass: 'col-md-8',
        required: false
    }
    render() {
        const computedId = 'fl-' + this.props.model.replace(/\./, '_')
        var field
        if (this.props.customField) {
            field = <Field model={this.props.model} aria-labelledby={computedId}>{this.props.children}</Field>
        } else {
            let controlProps = this.props.controlProps || {}
            if (this.props.disabled) {
                controlProps = objectAssign({}, controlProps, {disabled: this.props.disabled})
            }
            field = <NullableTextControl model={this.props.model} controlProps={controlProps} aria-labelledby={computedId} />
        }

        const { labelClass, divClass, required } = this.props

        let requiredClass = ''
        if (required) {
            requiredClass = 'required'
        }

        return (
            <Field model={this.props.model}>
                <div className={requiredClass + ' form-group'}>
                    <label className={labelClass + ' control-label'} id={computedId}>{this.props.label}</label>
                    <div className={divClass}>{field}</div>
                </div>
            </Field>
        )
    }
}

export class SimpleFormGroup extends React.PureComponent {
    static defaultProps = {
        labelClass: 'col-md-2',
        divClass: 'col-md-8',
        required: false
    }
    static propTypes = {
        labelClass: PropTypes.string.isRequired,
        labelFor: PropTypes.string,
        divClass: PropTypes.string,
        label: PropTypes.any.isRequired,
        required: PropTypes.bool,
        children: PropTypes.node
    }
    render() {
        const { label, labelClass, divClass, required, labelFor, children } = this.props

        const requiredClass = required? 'required' : ''
        let labelProps = {className: `${labelClass} control-label`}
        if (labelFor) {
            labelProps.htmlFor = labelFor
        }

        return (
            <div className={requiredClass + ' form-group'}>
                <label {...labelProps}>{label}</label>
                <div className={divClass}>{children}</div>
            </div>
        )
    }
}

export class AddOneLink extends React.PureComponent {
    render() {

        var label = this.props.label
        if (!label) {
            label = '+ Add One'
        }

        return (
            <Link to={this.props.link}>{label}</Link>
        )
    }
}

export class Select extends React.PureComponent {
    static defaultProps = {
        keyProp: 'key',
        labelProp: 'label',
        multiple: false,
        rows: 1,
        required: false
    }
    static propTypes = {
        changeAction: PropTypes.func,
        emptyChoice: PropTypes.node,
        getKey: PropTypes.func,
        getLabel: PropTypes.func,
        id: PropTypes.string,
        items: PropTypes.oneOfType([
            PropTypes.arrayOf(PropTypes.object),
            ImmutablePropTypes.listOf(PropTypes.object)
        ]).isRequired,
        keyProp: PropTypes.string,
        labelProp: PropTypes.string,
        model: PropTypes.string,
        multiple: PropTypes.bool,
        required: PropTypes.bool,
        rows: PropTypes.number,
        selectClasses: PropTypes.string
    }

    render() {
        const { id, items, emptyChoice, required, rows } = this.props
        let { getKey, getLabel } = this.props
        if (!getKey) {
            getKey = (obj) => obj[this.props.keyProp]
        }
        if (!getLabel) {
            getLabel = (obj) => obj[this.props.labelProp]
        }

        let requiredClass = ''
        if (required) {
            requiredClass = 'required'
        }

        const options = []
        if (emptyChoice) {
            options.push(<option key={-1} value="">{emptyChoice}</option>)
        }
        items.forEach((item, i) => {
            options.push(
                <option key={i} value={getKey(item)}>{getLabel(item)}</option>
            )
        })

        return (
            <Control.select
                    model={this.props.model} multiple={this.props.multiple} changeAction={this.props.changeAction}
                    className={requiredClass + ' form-control'} rows={rows} id={id} >
                {options}
            </Control.select>
        )
    }
}

export const SimpleSelect = Select

const customFieldMSP = (state, props) => {
    const modelValue = _.get(state, props.model)
    return {modelValue}
}
export const connectCustomField = connect(customFieldMSP)


/**
 * BooleanSelect lets you use a select for a simple yes/no style select.
 *
 * Unfortunately, the SimpleSelect component cannot work with boolean/null values easily
 * because the value portion of a select always has to be a string, so false gets replaced with
 * the string "false" and so on.  This makes it hard to use it as a boolean like checking if a
 * value is set.
 */
export class BooleanSelectView extends React.Component {
    static propTypes = {
        model: PropTypes.string.isRequired,
        modelValue: PropTypes.any,
        emptyChoice: PropTypes.node,
        labels: PropTypes.arrayOf(PropTypes.string)
    };
    static defaultProps = {
        labels: ['N', 'Y'],
        className: 'form-control'
    }
    _renderOmit = ['modelValue', 'emptyChoice', 'labels', 'dispatch', 'model', 'params']

    componentWillMount() {
        this.onChange = this.onChange.bind(this)
    }

    render() {
        const { modelValue, model, emptyChoice, labels } = this.props
        const rest = _.omit(this.props, this._renderOmit)
        const sValue = this.selectValue(modelValue)

        var empty
        if (emptyChoice) {
            empty = <option value="">{emptyChoice}</option>
        }

        return (
            <select name={model} value={sValue} onChange={this.onChange} {...rest}>
                {empty}
                <option value="0">{labels[0]}</option>
                <option value="1">{labels[1]}</option>
            </select>
        )
    }

    // return the value for the select box
    selectValue(modelValue) {
        if (modelValue === false || modelValue === 0 || modelValue === '0' || modelValue === '') {
            return '0'
        } else if (modelValue) {
            return '1'
        }
        return ''
    }

    onChange(e) {
        const v = e.target.value
        const newValue = (v === '')? null : ((v === '1') ? true : false)
        this.props.dispatch(formActions.change(this.props.model, newValue))
    }
}

export const BooleanSelect = connectCustomField(BooleanSelectView)


/**
 * A checkbox that is wrapped in a label in the bootstrappy way.
 *
 * Mostly this includes the extra div which does proper checkbox spacing, and also handles
 * when a field is disabled, showing appropriate hover cursor on it.
 */
export class CheckBox extends React.PureComponent {
    static propTypes = {
        model: PropTypes.string.isRequired,
        disabled: PropTypes.bool,
        label: PropTypes.string,
        children: PropTypes.node
    }
    render() {
        const { disabled, label, children, model } = this.props
        const className = (disabled)? 'checkbox disabled' : 'checkbox'
        return (
            <div className={className}>
                <label>
                    <Control.checkbox model={model} disabled={disabled} />
                    {label || children}
                </label>
            </div>
        )
    }
}

// Return props.viewValue as empty string if null or undefined, return unchanged otherwise.
function filterNullValue(props) {
    const viewValue = props.viewValue
    return (viewValue === null || viewValue === undefined) ? '' : viewValue
}

/**
 * Control changeAction for removing '%' character from inputs
 */
export function changeRemovePercent(model, value) {
    value = (value || '').replace(/%/, '')
    return formActions.change(model, value)
}

const mapPropsFilterNull = {value: filterNullValue}


export class NullableTextControl extends React.Component {
    static propTypes = {
        model: PropTypes.string.isRequired,
        className: PropTypes.string
    };
    static defaultProps = {
        type: 'text',
        className: 'form-control',
        mapProps: mapPropsFilterNull
    }

    render() {
        return <Control.text {...this.props} />
    }
}


export class NullableTextAreaControl extends React.PureComponent {
    static propTypes = {
        model: PropTypes.string.isRequired,
        className: PropTypes.string
    };
    static defaultProps = {
        className: 'form-control',
        mapProps: mapPropsFilterNull
    }

    render() {
        return <Control.textarea {...this.props} />
    }
}


let Control = FormControl

// For Unit tests: Wrap the control element.
// This code block will be compiled out in tests anyway.
if (process.env.NODE_ENV == 'test') {
    function makeInputFake(inputType) {
        function inputFake(props) {
            const className = 'testing-control ' + (props.className || '')
            return (
                <input
                    type={inputType} value={props.modelValue}
                    name={props.model} className={className} />
            )
        }
        return connectCustomField(inputFake)
    }

    function textareaFake(props) {
        return <textarea name={props.model} value={props.modelValue} className='testing-control' />
    }

    function selectFake(props) {
        return <select name={props.model} value={props.modelValue}>{props.children}</select>
    }

    Control = makeInputFake('text')
    Control.checkbox = makeInputFake('checkbox')
    Control.text = makeInputFake('text')
    Control.textarea = connectCustomField(textareaFake)
    Control.select = connectCustomField(selectFake)
}
export { Control }
