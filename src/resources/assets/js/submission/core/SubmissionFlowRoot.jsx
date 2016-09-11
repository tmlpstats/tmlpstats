import React from 'react'
import { connect } from 'react-redux'

import { objectAssign } from '../../reusable/ponyfill'
import { SubmissionBase } from '../base_components'
import SubmissionNav from './SubmissionNav'
import * as actions from './actions'

const steps = [
    // The steps key is some metadata about the steps, maybe redundant but we'll leave it for now.
    {key: 'scoreboard', name: 'Scoreboard'},
    {key: 'applications', name: 'Team Expansion'},
    {key: 'class_list', name: 'Class List'},
    {key: 'courses', name: 'Courses'},
    {key: 'review', name: 'Review'}
]

class SubmissionFlowComponent extends SubmissionBase {
    render() {
        if (!this.checkReportingDate()) {
            return this.renderBasicLoading(this.props.coreInit)
        }
        const largeLayout = this.props.browserGreaterThan.large
        const nav = <SubmissionNav params={this.props.params} steps={steps} location={this.props.location} tabbed={!largeLayout} />
        var layout
        if (largeLayout) {
            layout = (
                <div id="submissionWideLayout">
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
                <div className="row">
                    <div className="col-md-12">
                        <h4>This page is a work in progress. Please don't submit anything, but feel free to look around. Currently, this is only visible for the Regional team and site administrators.</h4>
                    </div>
                </div>
                {layout}
            </div>
        )
    }

    checkReportingDate() {
        const { params, reportingDate, dispatch, coreInit } = this.props
        if (params.reportingDate != reportingDate) {
            dispatch(actions.setReportingDate(params.reportingDate))
            return false
        } else if (coreInit.state == 'new') {
            setTimeout(() => {
                dispatch(actions.initSubmission(params.centerId, params.reportingDate))
            })
            return false
        } else if (coreInit.state != 'loaded') {
            return false
        }
        return true
    }
}

const mapStateToProps = (state) => {
    return objectAssign({browserGreaterThan: state.browser.greaterThan}, state.submission.core)
}

const SubmissionFlowRoot = connect(mapStateToProps)(SubmissionFlowComponent)
export default SubmissionFlowRoot
