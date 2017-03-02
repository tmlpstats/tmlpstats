import _ from 'lodash'
import React from 'react'

import { connectRedux } from '../../reusable/dispatch'
import { objectAssign } from '../../reusable/ponyfill'
import { SubmissionBase } from '../base_components'

import checkCoreData from './checkCoreData'
import SubmissionNav from './SubmissionNav'
import { PAGES_CONFIG } from './data'

const steps = PAGES_CONFIG
const stepsBeforeCr3 = _.reject(steps, {key: 'next_qtr_accountabilities'})

@connectRedux()
export default class SubmissionFlowRoot extends SubmissionBase {
    static mapStateToProps(state) {
        return objectAssign({browser: state.browser}, state.submission.core)
    }

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
