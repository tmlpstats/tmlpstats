import _ from 'lodash'
import React from 'react'

import { systemMessagesData } from '../../lookups/lookups-system-messages'
import { connectRedux, rebind } from '../../reusable/dispatch'
import { SystemMessages } from '../../reusable/system-messages'
import { SubmissionBase } from '../base_components'

import checkCoreData from './checkCoreData'
import SubmissionNav from './SubmissionNav'
import { PAGES_CONFIG } from './data'

const steps = _.reject(PAGES_CONFIG, {hide_nav: true})
const stepsNoNqa = _.reject(steps, {key: 'next_qtr_accountabilities'})

@connectRedux()
export default class SubmissionFlowRoot extends SubmissionBase {
    static mapStateToProps(state) {
        return {
            browser: state.browser,
            core: state.submission.core,
            systemMessages: systemMessagesData.getMessages(state, 'submission')
        }
    }

    constructor(props) {
        super(props)
        rebind(this, 'onMessageDismiss')
    }

    render() {
        const { core, browser, params, location, systemMessages } = this.props
        if (!this.checkReportingDate()) {
            return this.renderBasicLoading(this.props.coreInit)
        }
        const navSteps = (core.lookups.capabilities.nextQtrAccountabilities) ? steps : stepsNoNqa
        const largeLayout = browser.greaterThan.large

        const nav = <SubmissionNav params={params} steps={navSteps} location={location} tabbed={!largeLayout} />
        var layout
        if (largeLayout) {
            layout = (
                <div id="submissionWideLayout" className="row submission-layout">
                    <div id="swSidebar">{nav}</div>
                    <div id="swContent">
                        <div className="panel panel-default">
                            <div className="panel-body submission-content">
                                {this.props.children}
                            </div>
                        </div>
                    </div>
                    <div className="clearfix"></div>
                </div>
            )
        } else {
            layout = (
                <div className="submission-layout">
                    {nav}
                    <div className="tab-content">
                        <div className="tab-pane active submission-content">
                            {this.props.children}
                        </div>
                    </div>
                </div>
            )
        }
        return (
            <div>
                <SystemMessages messages={systemMessages} onDismiss={this.onMessageDismiss} />
                {layout}
            </div>
        )
    }

    checkReportingDate() {
        const { centerId, reportingDate } = this.props.params
        return checkCoreData(centerId, reportingDate, this.props.core, this.props.dispatch)
    }

    onMessageDismiss(messageId, a) {
        this.props.dispatch(systemMessagesData.dismiss('submission', messageId))
    }
}
