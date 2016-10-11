import moment from 'moment'

import { SubmissionBase, React } from '../base_components'
import { PAGES_CONFIG } from '../core/data'
import { connectRedux } from '../../reusable/dispatch'
import { Link } from 'react-router'
import { loadPairs } from './data'

const CLASSES = {error: 'bg-danger', warning: 'bg-warning'}

@connectRedux()
export default class Review extends SubmissionBase {
    static mapStateToProps(state) {
        return state.submission
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

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }

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

        return (
            <div>
                <h3>Review</h3>
                <ul>{categories}</ul>
            </div>
        )
    }
}

export class ReviewCategory extends React.PureComponent {
    render() {
        const { config, baseUri, pageData } = this.props
        // Loop each message to create an entry
        let messages = []

        // messages is keyed by ID, so loop this first
        for (let id in this.props.messages) {
            const itemMessages = this.props.messages[id]

            if (!itemMessages.length) {
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
            let displayValue
            let refObject
            switch (config.className) {
            case 'Application':
                refObject = pageData.applications.collection[firstMessage.reference.id]
                if (refObject) {
                    displayValue = `${refObject.firstName} ${refObject.lastName}`
                }
                break
            case 'Course':
                refObject = pageData.courses.collection[firstMessage.reference.id]
                if (refObject) {
                    displayValue = refObject.type + ' on ' + moment(refObject.startDate).format('MMM D, YYYY')
                }
                break
            case 'Scoreboard':
                displayValue = 'Week of ' + moment(firstMessage.reference.id).format('MMM D, YYYY')
                break
            case 'TeamMember':
                refObject = pageData.teamMembers.data.collection[firstMessage.reference.id]
                if (refObject) {
                    displayValue = `${refObject.firstName} ${refObject.lastName}`
                }
                break
            }

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
