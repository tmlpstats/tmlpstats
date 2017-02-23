import moment from 'moment'
import { Link } from 'react-router'
import { Button, Modal } from 'react-bootstrap'

import { PAGES_CONFIG } from '../core/data'
import { connectRedux, rebind } from '../../reusable/dispatch'
import { Alert } from '../../reusable/ui_basic'
import { SubmissionBase, React } from '../base_components'

import { getValidationMessages, submitReport, setPreSubmitModal, setPostSubmitModal, setSubmitResults } from './actions'
import { loadPairs } from './data'

const CLASSES = {error: 'bg-danger', warning: 'bg-warning'}

@connectRedux()
export default class Review extends SubmissionBase {
    static mapStateToProps(state) {
        return state.submission
    }

    static onRouteEnter(nextState, replace) {
        const { store } = require('../../store')
        store.dispatch(getValidationMessages(nextState.params.centerId, nextState.params.reportingDate))
    }

    constructor(props) {
        super(props)
        rebind(this, 'displayPreSubmitModal', 'hidePreSubmitModal', 'displayPostSubmitModal', 'hidePostSubmitModal', 'onSubmit', 'completeSubmission')
    }

    checkLoading() {
        const { centerId, reportingDate } = this.props.params
        let alreadyLoaded = true

        loadPairs.forEach((pair) => {
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
        this.props.dispatch(setPreSubmitModal(true))
    }

    hidePreSubmitModal() {
        this.props.dispatch(setPreSubmitModal(false))
    }

    displayPostSubmitModal() {
        this.props.dispatch(setPostSubmitModal(true))
    }

    hidePostSubmitModal() {
        this.props.dispatch(setPostSubmitModal(false))
    }

    onSubmit() {
        const { centerId, reportingDate } = this.props.params

        this.props.dispatch(submitReport(centerId, reportingDate)).then((result) => {
            if (!result) {
                throw new Error('Failed to submit report. Please try again.')
            }

            if (!result.success) {
                throw new Error(result.message)
            }

            this.props.dispatch(setSubmitResults({
                message: result.message,
                submittedAt: result.submittedAt,
                isSuccess: true,
            }))
        }).catch((err) => {
            this.props.dispatch(setSubmitResults({
                message: err,
                isSuccess: false,
            }))
            // There is a more reduxy way to do this, which we can do later, this method works.
            window.location.reload()
        })

        this.hidePreSubmitModal()
        this.displayPostSubmitModal()
    }

    completeSubmission() {
        const { centerId, reportingDate } = this.props.params
        window.location.href = `/reports/centers/${centerId}/${reportingDate}`
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

        const { showPreSubmitModal, showPostSubmitModal, submitResults } = this.props.review

        let categories = []
        PAGES_CONFIG.forEach((config) => {
            const pageData = this.props[config.key]
            const rawMessages = pageData.messages
            if (rawMessages) {
                categories.push(<ReviewCategory key={config.key}
                                                baseUri={this.baseUri()}
                                                config={config}
                                                messages={rawMessages}
                                                pageData={pageData}/>)
            }
        })

        let modal
        if (showPreSubmitModal) {
            modal = (
                <PreSubmitModal dismiss={this.hidePreSubmitModal}
                                onSubmit={this.onSubmit} />
            )
        } else if (showPostSubmitModal && submitResults) {
            let dismiss = this.completeSubmission
            if (!submitResults.isSuccess) {
                dismiss = this.hidePostSubmitModal
            }
            modal = (
                <PostSubmitModal dismiss={dismiss}
                                 submittedAt={moment(submitResults.submittedAt).format('MMM Do YYYY, h:mm:ss a')}
                                 message={submitResults.message}
                                 isSuccess={submitResults.isSuccess} />
            )
        }

        return (
            <div>
                <h3>Review</h3>
                <ul>{categories}</ul>
                <div>
                    <Alert alert="warning">Submission is in extreme beta. Note you may need to refresh this page after submitting, which is not the long-term intent.</Alert>
                    <button type="button" className="btn btn-primary btn-lg" onClick={this.displayPreSubmitModal}>Submit Report</button>
                </div>
                {modal}
            </div>
        )
    }
}

export class ReviewCategory extends React.PureComponent {

    getDisplayValue(id, pageData, config) {
        let displayValue
        let refObject

        switch (config.className) {
        case 'Application':
            refObject = pageData.applications.collection[id]
            if (refObject) {
                displayValue = `${refObject.firstName} ${refObject.lastName}`
            }
            break
        case 'Course':
            refObject = pageData.courses.collection[id]
            if (refObject) {
                displayValue = refObject.type + ' on ' + moment(refObject.startDate).format('MMM D, YYYY')
            }
            break
        case 'TeamMember':
            refObject = pageData.teamMembers.data.collection[id]
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
                uri = `${baseUri}/${config.key}`
                if (config.key != 'scoreboard') {
                    uri += `/edit/${firstMessage.reference.id}`
                }
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
                <h3>{config.name}</h3>
                <ul>{messages}</ul>
            </div>
        )
    }
}

class PreSubmitModal extends React.PureComponent {
    render() {
        const { dismiss, onSubmit } = this.props
        return (
            <div className="static-modal">
                <Modal show={true} onHide={dismiss}>
                    <Modal.Header closeButton>
                        <Modal.Title>Submit your stats</Modal.Title>
                    </Modal.Header>

                    <Modal.Body>
                        Clicking submit will send your stats to the regional stats team. We will also send a copy to your
                        <ul>
                            <li>Program Manager</li>
                            <li>Classroom Leader</li>
                            <li>Team 2 Team Leader</li>
                            <li>Team 1 Team Leader</li>
                            <li>Statistician</li>
                            <li>Statistician Apprentice</li>
                        </ul>
                        You can re-submit your stats before 7PM your local time on Friday.
                        <br/><br/>
                    </Modal.Body>

                    <Modal.Footer>
                        <Button bsStyle="default" onClick={dismiss}>Cancel</Button>
                        <Button bsStyle="primary" type="submit" onClick={onSubmit}>Submit</Button>
                    </Modal.Footer>
                </Modal>
            </div>
        )
    }
}

class PostSubmitModal extends React.PureComponent {
    createMarkup() {
        // TODO: stop passing html from the api. generate it here
        return {__html: this.props.message}
    }

    render() {
        const { submittedAt, isSuccess, dismiss } = this.props
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
            <div className="static-modal">
                <Modal show={true} onHide={dismiss}>
                    <Modal.Header closeButton>
                        <Modal.Title>Your stats are submitted!</Modal.Title>
                    </Modal.Header>

                    <Modal.Body>
                        <div>{messageHeader}</div>
                        <br/>
                        <div className={messageClass + ' alert'} role="alert" dangerouslySetInnerHTML={this.createMarkup()}></div>
                    </Modal.Body>

                    <Modal.Footer>
                        <Button bsStyle="primary" onClick={dismiss}>Okay</Button>
                    </Modal.Footer>
                </Modal>
            </div>
        )
    }
}
