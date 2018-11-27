import React from 'react'
import PropTypes from 'prop-types'
import moment from 'moment'
import { withRouter } from 'react-router'
import { createSelector } from 'reselect'

import { rebind, connectRedux } from '../../reusable/dispatch'
import { Form, Select, SimpleField, DateInput, SimpleDateInput, formActions, SimpleFormGroup } from '../../reusable/form_utils'
import { ButtonStateFlip } from '../../reusable/ui_basic'
import { getErrMessage } from '../../reusable/ajax_utils'
import Api from '../../api'

import RegionBase from './RegionBase'
import * as actions from './actions'

const FRIDAY = 5
const ISO_DATE_FORMAT = 'YYYY-MM-DD'

function onlyFridays(date) {
    return date.day() != FRIDAY
}

const WEEK_OPTIONS = [
    {key: '1', label:'1'},
    {key: '2', label:'2'},
    {key: '3', label:'3'},
    {key: '4', label:'4'},
    {key: '5', label:'5'},
    {key: '6', label:'6'},
    {key: '7', label:'7'},
]

const MODEL = 'admin.regions.quarterDates'

@withRouter
@connectRedux()
export class QuarterDates extends RegionBase {
    static mapStateToProps(state) {
        return state.admin.regions
    }

    constructor(props) {
        super(props)
        rebind(this, 'onSubmit', 'outsideQuarter')
        this.initialVisibleMonth = createSelector(
            obj => obj.startWeekendDate,
            (startWeekendDate) => {
                const initial = (startWeekendDate? moment(startWeekendDate) : moment())
                const v = initial.clone().startOf('month');
                return function() {
                    return v
                }
            }
        )
    }

    checkDatesData() {
        if (!this.checkRegions()) {
            return false
        }
        const { quarterDates, params, dispatch } = this.props
        if (!quarterDates || quarterDates.regionAbbr !== params.regionAbbr || quarterDates.quarterId !== params.quarterId) {
            // XXX: Should be dedup'ed somehow
            const input = {region: params.regionAbbr, quarter: params.quarterId}
            Api.Admin.Region.getQuarterConfig(input).then((result) => {
                result.regionAbbr = params.regionAbbr
                result.quarterId = params.quarterId
                dispatch(formActions.change(MODEL, result))
            })
            return false
        }
        return true
    }

    render() {
        if (!this.checkDatesData()) {
            return <div>Loading...</div>
        }
        //const { region, centers } = this.regionCenters()
        let preferences

        const obj = this.props.quarterDates
        const ivm = this.initialVisibleMonth(obj)

        if (obj && obj.endWeekendDate && obj.classroom2Date) {
            let afterWeeks
            if (obj.prefs && obj.prefs.appRegFutureQuarterWeeks) {
                afterWeeks = moment(obj.endWeekendDate).clone().subtract(parseInt(obj.prefs.appRegFutureQuarterWeeks), 'weeks').format(ISO_DATE_FORMAT)
            }
            preferences = (
                <div className="form-horizontal">
                    <h4>Preferences</h4>
                    <SimpleFormGroup label="Travel/Room reporting Due">
                        <p>Default is Milestone 2</p>
                        <DateInput model={MODEL+'.travelDueByDate'} isDayBlocked={onlyFridays} />
                    </SimpleFormGroup>
                    <SimpleFormGroup label="Can register into next-next quarter">
                        <Select model={MODEL+'.appRegFutureQuarterWeeks'} items={WEEK_OPTIONS} />
                        weeks before end of quarter
                        <p> On date: {afterWeeks}</p>
                    </SimpleFormGroup>
                </div>
            )
        }


        return (
            <Form model={MODEL} onSubmit={this.onSubmit}>
                <div className="form-horizontal">
                    <h4>Basic Setup</h4>
                    <SimpleDateInput label="Start Weekend Date" model={MODEL+'.startWeekendDate'} isDayBlocked={onlyFridays} />
                    <SimpleDateInput label="Milestone 1 Date" model={MODEL+'.classroom1Date'} isDayBlocked={onlyFridays} isOutsideRange={this.outsideQuarter} initialVisibleMonth={ivm} />
                    <SimpleDateInput label="Milestone 2 Date" model={MODEL+'.classroom2Date'} isDayBlocked={onlyFridays} isOutsideRange={this.outsideQuarter} initialVisibleMonth={ivm} />
                    <SimpleDateInput label="Milestone 3 Date" model={MODEL+'.classroom3Date'} isDayBlocked={onlyFridays} isOutsideRange={this.outsideQuarter} initialVisibleMonth={ivm} />
                    <SimpleDateInput label="End Weekend Date" model={MODEL+'.endWeekendDate'} isDayBlocked={onlyFridays} initialVisibleMonth={ivm} />
                    <SimpleField label="Weekend Location" model={MODEL+'.location'} />
                </div>
                {preferences}
                <div>
                    <ButtonStateFlip buttonClass="btn btn-primary btn-lg"
                         loadState={this.props.scoreboardLock.saveState}
                         wrapGroup={true}>Save</ButtonStateFlip>
                </div>
            </Form>
        )
    }

    outsideQuarter(date) {
        const { startWeekendDate, endWeekendDate } = this.props.quarterDates
        let outside = false
        if (startWeekendDate) {
            outside = date.isBefore(startWeekendDate)
        }
        if (!outside && endWeekendDate) {
            outside = date.isAfter(endWeekendDate)
        }
        return outside
    }

    onSubmit(data) {
        // TODO do a redux action based debounced saveState style button. This is not the best way long-term
        Api.Admin.Region.saveQuarterConfig({
            region: data.regionAbbr,
            quarter: data.quarterId,
            data: data
        }).then((result) => {
            if (result) {
                this.props.router.push(this.regionQuarterBaseUri())
            }
        }).catch((err) => {
            alert(getErrMessage(err))
        })
    }
}