import moment from 'moment'
import { Link } from 'react-router'

import { PAGES_CONFIG } from '../core/data'
import { connectRedux, rebind } from '../../reusable/dispatch'
import { Field } from 'react-redux-form'
import { CheckBox, Form } from '../../reusable/form_utils'
import { Alert, ButtonStateFlip } from '../../reusable/ui_basic'
import { SubmissionBase, React } from '../base_components'

import { getValidationMessagesIfStale, displayState, submitReport, submitState } from './actions'
import { DISPLAY_STATES } from './data'
import { REVIEW_SUBMIT_FORM_KEY } from './reducers'
import {loadScoreboard} from "../scoreboard/actions";

const CLASSES = {error: 'bg-danger', warning: 'bg-warning'}

@connectRedux()
export default class Review extends SubmissionBase {
    static mapStateToProps(state) {
        return state.submission
    }

    static onRouteEnter(nextState) {
        const { store } = require('../../store')
        store.dispatch(loadScoreboard(nextState.params.centerId, nextState.params.reportingDate))
        store.dispatch(getValidationMessagesIfStale(nextState.params.centerId, nextState.params.reportingDate))
    }

    constructor(props) {
        super(props)
        rebind(this, 'displayPreSubmitModal', 'hidePreSubmitModal', 'onSubmit', 'completeSubmission', 'failSubmission')
        this.props.dispatch(displayState(DISPLAY_STATES.main))
        this.props.dispatch(submitState('new'))
        this.loadPairs = require('./loadPairs').default
    }

    checkLoading() {
        const { centerId, reportingDate } = this.props.params
        let alreadyLoaded = true

        this.loadPairs.forEach((pair) => {
            const handler = pair[0]
            const loadState = pair[1](this.props)
            if (!loadState.loaded && loadState.available) {
                // if it's not available someone else has already started loading
                this.props.dispatch(handler(centerId, reportingDate))
                alreadyLoaded = false
            }
        })

        return alreadyLoaded
    }

    displayPreSubmitModal() {
        this.props.dispatch(displayState(DISPLAY_STATES.preSubmit))
    }

    hidePreSubmitModal() {
        this.props.dispatch(displayState(DISPLAY_STATES.main))
    }

    onSubmit() {
        const { centerId, reportingDate } = this.props.params
        const data = this.props.review.submitData

        this.props.dispatch(submitReport(centerId, reportingDate, data))
    }

    completeSubmission() {
        const { centerId, reportingDate } = this.props.params
        this.props.dispatch(submitState('loading'))
        window.location.href = `/reports/centers/${centerId}/${reportingDate}`
    }

    failSubmission() {
        this.props.dispatch(displayState(DISPLAY_STATES.main))
        // There is a more reduxy way to do this, which we can do later, this method works.
        window.location.reload()
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const { submitResults, displayFlow, reportSubmitting, loaded } = this.props.review

        if (displayFlow.state == DISPLAY_STATES.preSubmit) {
            return (
                <PreSubmitCard dismiss={this.hidePreSubmitModal}
                               onSubmit={this.onSubmit}
                               loadState={reportSubmitting}
                               lookups={this.props.core.lookups} />
            )
        }

        if (displayFlow.state == DISPLAY_STATES.postSubmit && submitResults) {
            let dismiss = this.completeSubmission
            if (!submitResults.isSuccess) {
                dismiss = this.failSubmission
            }
            return (
                <PostSubmitCard dismiss={dismiss}
                                submittedAt={moment(submitResults.submittedAt).format('MMM Do YYYY, h:mm:ss a')}
                                message={submitResults.message}
                                isSuccess={submitResults.isSuccess}
                                loadState={reportSubmitting} />
            )
        }

        let categories = []
        PAGES_CONFIG.forEach((config) => {
            const pageData = this.props[config.key]
            const rawMessages = pageData.messages
            if (rawMessages && Object.keys(rawMessages).length) {
                categories.push(<ReviewCategory key={config.key}
                                                baseUri={this.baseUri()}
                                                config={config}
                                                messages={rawMessages}
                                                pageData={pageData}/>)
            }
        })

        let fetching
        if (loaded.state == 'loading') {
            fetching = <p className="lead">Fetching updated messages....</p>
        }

        let displayed
        if (categories.length) {
            displayed = <ul>{categories}</ul>
        } else if (!fetching) {
            displayed = (
                <Alert alert="success">No errors or messages!</Alert>
            )
        }

        return (
            <div>
                <h3>Review</h3>
                {fetching}
                {displayed}
                <div>
                    <ButtonStateFlip loadState={reportSubmitting}
                                     buttonClass="btn btn-primary btn-lg"
                                     onClick={this.displayPreSubmitModal}>Submit Report</ButtonStateFlip>
                </div>
            </div>
        )
    }
}

export class ReviewCategory extends React.PureComponent {

    getDisplayValue(id, pageData, config) {
        let displayValue
        let refObject

        switch (config.className) {
        case 'TeamApplication':
            refObject = pageData.applications.data[id]
            if (refObject) {
                displayValue = `${refObject.firstName} ${refObject.lastName}`
            }
            break
        case 'ProgramLeader':
            refObject = pageData.programLeaders.data[id]
            if (refObject) {
                displayValue = `${refObject.firstName} ${refObject.lastName}`
            }
            break
        case 'Course':
            refObject = pageData.courses.data[id]
            if (refObject) {
                displayValue = refObject.type + ' on ' + moment(refObject.startDate).format('MMM D, YYYY')
            }
            break
        case 'TeamMember':
            refObject = pageData.teamMembers.data[id]
            if (refObject) {
                displayValue = `${refObject.firstName} ${refObject.lastName}`
            }
            break
        case 'Scoreboard':
            displayValue = 'Week of ' + moment(id).format('MMM D, YYYY')
            break
        }

        return displayValue
    }

    getLinkUri(id, pageData, config, baseUri) {
        let uri

        switch (config.className) {
        case 'ProgramLeader':
            let refObject = pageData.programLeaders.data[id]
            if (refObject) {
                uri = `${baseUri}/${config.key}/edit/${refObject.accountability}`
            }
            break
        case 'TeamApplication':
        case 'Course':
        case 'TeamMember':
            uri = `${baseUri}/${config.key}/edit/${id}`
            break
        case 'Scoreboard':
            uri = `${baseUri}/${config.key}`
            break
        }

        return uri
    }

    render() {
        const { config, baseUri, pageData } = this.props
        // Loop each message to create an entry
        let messages = []

        // messages is keyed by ID, so loop this first
        for (let id in this.props.messages) {
            const itemMessages = this.props.messages[id]

            // Skip errors in new (non-stashed) objects
            if (!itemMessages.length || id == 'create') {
                continue
            }

            const firstMessage = itemMessages[0]
            let uri
            if (firstMessage.reference && firstMessage.reference.id) {
                uri = this.getLinkUri(firstMessage.reference.id, pageData, config, baseUri)
            }
            const info = itemMessages.map((message, idx) => {
                return (
                    <div key={idx} className={CLASSES[message.level]}>
                        {message.level}: {message.message}
                    </div>
                )
            })

            const displayValue = this.getDisplayValue(firstMessage.reference.id, pageData, config)

            messages.push(
                <li key={id}>
                    <Link to={uri}>{displayValue}</Link>
                    {info}
                </li>
            )
        }

        if (!messages.length) {
            return <div />
        }

        return (
            <div>
                <h4>{config.name}</h4>
                <ul>{messages}</ul>
            </div>
        )
    }
}

class PreSubmitCard extends React.PureComponent {
    render() {
        const { dismiss, onSubmit, loadState, lookups } = this.props
        const modelKey = REVIEW_SUBMIT_FORM_KEY

        let skipEmailCheckbox
        if (lookups.user.canSkipSubmitEmail) {
            skipEmailCheckbox = <CheckBox label="Don't send submission email" model={modelKey+'.skipSubmitEmail'} />
        }

        return (
            <div>
                <h3>Submit your stats</h3>
                <div>
                    Clicking submit will send your stats to the regional stats team. We will also send a copy to your
                    <ul>
                        <li>Program Manager</li>
                        <li>Classroom Leader</li>
                        <li>Team 2 Team Leader</li>
                        <li>Team 1 Team Leader</li>
                        <li>Statistician</li>
                        <li>Statistician In Training</li>
                    </ul>
                    You can re-submit your stats before 6PM your local time on Friday.
                    <br/><br/>
                    <Form model={modelKey} onSubmit={onSubmit}>
                        {skipEmailCheckbox}
                        <Field model={modelKey+'.comment'}>
                            <div className="form-group">
                                <label htmlFor="comment" className="control-label">Comment:</label>
                                <textarea name="comment" className="form-control" rows="10" />
                            </div>
                        </Field>
                    </Form>
                </div>
                <div>
                    <button className="btn btn-default" onClick={dismiss}>Back</button>
                    <ButtonStateFlip loadState={loadState} onClick={onSubmit}>Submit</ButtonStateFlip>
                </div>
            </div>
        )
    }
}

class PostSubmitCard extends React.PureComponent {
    createMarkup() {
        // TODO: stop passing html from the api. generate it here
        return {__html: this.props.message}
    }

    render() {
        const { submittedAt, isSuccess, dismiss, loadState } = this.props

        let messageHeader
        let messageClass
        if (isSuccess) {
            messageHeader = `You have successfully submitted your stats! We received them on ${submittedAt}.\nCheck to make sure you received an email from TMLP Stats in your center's stats email.`
            messageClass = 'alert-success'
        } else {
            messageHeader = 'There was an issue submitting your stats.'
            messageClass = 'alert-danger'
        }

        return (
            <div>
                <h3>Your stats are submitted!</h3>
                <div>
                    <div>{messageHeader}</div>
                    <br/>
                    <div className={messageClass + ' alert'} role="alert" dangerouslySetInnerHTML={this.createMarkup()}></div>
                </div>
                <div>
                    <ButtonStateFlip loadState={loadState} onClick={dismiss}>Show Me</ButtonStateFlip>
                </div>
            </div>
        )
    }
}
