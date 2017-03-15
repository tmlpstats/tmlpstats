import moment from 'moment'
import _ from 'lodash'
import React, { PropTypes } from 'react'
import { actions as formActions, getField } from 'react-redux-form'
import { SingleDatePicker } from 'react-dates'

import { SimpleFormGroup } from './index'
import { objectAssign } from '../ponyfill'
import { rebind, connectRedux } from '../dispatch'

const sdpProps = _.omit(SingleDatePicker.propTypes, ['id', 'onDateChange', 'focused', 'onFocusChange'])
const datePickerProps = Object.keys(sdpProps)


/**
 * Provide a calendar date picker based on react-dates and integrated with react-redux-form.
 * There must be a formReducer on this form because it uses the RRF form state to manage focused value.
 *
 * Only required prop is "model" which is the RRF model.
 */
@connectRedux()
export class DateInput extends React.Component {
    static mapStateToProps(state, props) {
        const modelValue = _.get(state, props.model)
        const field = getField(state, props.model)
        const focused = (field)? field.focus : false
        return {modelValue, focused}
    }

    static propTypes = objectAssign({
        model: PropTypes.string,
        modelValue: PropTypes.string,
    }, sdpProps)

    static defaultProps = {
        simple: true,
        focused: false
    }

    componentWillMount() {
        rebind(this, 'onDateChange', 'onFocusChange')
    }

    render() {
        const { modelValue, model, focused, defaultValue } = this.props
        let date
        if (modelValue) {
            date = moment(modelValue)
        } else if (defaultValue) {
            date = defaultValue
        }

        const id = 'dp-' + model.replace('.', '-')
        const pickerForwardProps = _.pick(this.props, datePickerProps)
        return (
            <SingleDatePicker
                    { ...pickerForwardProps}
                    id={id} date={date} onDateChange={this.onDateChange}
                    focused={focused} onFocusChange={this.onFocusChange}
                    isOutsideRange={() => false} />
        )
    }

    onDateChange(value) {
        // Convert Moment back to iso8601 datetime representation
        if (value !== null) {
            value = value.format('YYYY-MM-DD')
        }
        this.props.dispatch(formActions.change(this.props.model, value))
    }

    onFocusChange({focused}) {
        if (focused) {
            this.props.dispatch(formActions.focus(this.props.model))
        } else {
            this.props.dispatch(formActions.blur(this.props.model))
        }
    }
}

export default DateInput



// DateInput wrapped in a SimpleFormGroup, for supreme laziness.
export class SimpleDateInput extends React.PureComponent {
    render() {
        const fgProps = Object.keys(SimpleFormGroup.propTypes)
        const formGroupProps = _.pick(this.props, fgProps)
        const dateInputProps = _.omit(this.props, fgProps)

        return (
            <SimpleFormGroup {...formGroupProps}>
                <DateInput {...dateInputProps} />
            </SimpleFormGroup>
        )
    }
}
