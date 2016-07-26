import React from 'react'
import { connect } from 'react-redux'

import { SubmissionBase } from '../base_components'
import SubmissionNav from './SubmissionNav'
import * as actions from './actions'

const steps = [
    // The steps key is some metadata about the steps, maybe redundant but we'll leave it for now.
    {key: 'scoreboard', name: 'Scoreboard'},
    {key: 'applications', name: 'Team Expansion'},
    {key: 'classlist', name: 'Class List'},
    {key: 'courses', name: 'Courses'},
    {key: 'review', name: 'Review'}
]

class SubmissionFlowComponent extends SubmissionBase {
    render() {
        if (!this.checkReportingDate()) {
            const { coreInit } = this.props
            if (coreInit.state == 'failed') {
                return <div className="bg-danger">{coreInit.error}</div>
            } else {
                return this.renderBasicLoading()
            }
        }
        return (
            <div>
                <div className="row">
                    <div className="col-md-12">
                        <h4>This page is a work in progress. Please don't submit anything, but feel free to look around. Currently, this is only visible for the Regional team and site administrators.</h4>
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-2"><SubmissionNav params={this.props.params} steps={steps} location={this.props.location} /></div>
                    <div className="col-md-10">
                        <div className="panel panel-default">
                            <div className="panel-body">
                                {this.props.children}
                            </div>
                        </div>
                    </div>
                </div>
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

const mapStateToProps = (state) => state.submission.core

const SubmissionFlowRoot = connect(mapStateToProps)(SubmissionFlowComponent)
export default SubmissionFlowRoot
