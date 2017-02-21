import _ from 'lodash'
import React from 'react'
import { connect } from 'react-redux'

import { objectAssign } from '../../reusable/ponyfill'
import { SubmissionBase } from '../base_components'
import { getValidationMessages } from '../review/actions'
import SubmissionNav from './SubmissionNav'
import * as actions from './actions'
import { PAGES_CONFIG } from './data'

const steps = PAGES_CONFIG
const stepsBeforeCr3 = _.reject(steps, {key: 'qtr_accountabilities'})

class SubmissionFlowComponent extends SubmissionBase {
    render() {
        if (!this.checkReportingDate()) {
            return this.renderBasicLoading(this.props.coreInit)
        }
        const navSteps = (this.props.lookups.pastClassroom[3]) ? steps : stepsBeforeCr3
        const largeLayout = this.props.browser.greaterThan.large

        const nav = <SubmissionNav params={this.props.params} steps={navSteps} location={this.props.location} tabbed={!largeLayout} />
        var layout
        if (largeLayout) {
            layout = (
                <div id="submissionWideLayout" className="row">
                    <div id="swSidebar">{nav}</div>
                    <div id="swContent">
                        <div className="panel panel-default">
                            <div className="panel-body">
                                {this.props.children}
                            </div>
                        </div>
                    </div>
                    <div className="clearfix"></div>
                </div>
            )
        } else {
            layout = (
                <div>
                    {nav}
                    <div className="tab-content">
                        <div className="tab-pane active">
                            {this.props.children}
                        </div>
                    </div>
                </div>
            )
        }
        return (
            <div>
                {layout}
            </div>
        )
    }

    checkReportingDate() {
        const { centerId, reportingDate } = this.props.params
        return checkCoreData(centerId, reportingDate, this.props, this.props.dispatch)
    }
}

export function checkCoreData(centerId, reportingDate, core, dispatch) {
    if (reportingDate != core.reportingDate) {
        dispatch(actions.setReportingDate(reportingDate))
        return false
    } else if (core.coreInit.state == 'new') {
        setTimeout(() => {
            dispatch(actions.initSubmission(centerId, reportingDate))
            dispatch(getValidationMessages(centerId, reportingDate))
        })
        return false
    } else if (core.coreInit.state != 'loaded') {
        return false
    }
    return true
}

const mapStateToProps = (state) => {
    return objectAssign({browser: state.browser}, state.submission.core)
}

const SubmissionFlowRoot = connect(mapStateToProps)(SubmissionFlowComponent)
export default SubmissionFlowRoot
