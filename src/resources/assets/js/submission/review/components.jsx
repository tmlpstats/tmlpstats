import { SubmissionBase, React } from '../base_components'
import { PAGES_CONFIG } from '../core/data'
import { connectRedux } from '../../reusable/dispatch'
import { Link } from 'react-router'
import * as actions from './actions'

const CLASSES = {error: 'bg-danger', warning: 'bg-warning'}

@connectRedux()
export default class Review extends SubmissionBase {
    static mapStateToProps(state) {
        return state.submission
    }

    initializeMessages() {
        const { centerId, reportingDate } = this.props.params
        this.props.dispatch(actions.getValidationMessages(centerId, reportingDate))
    }

    render() {
        let categories = []
        PAGES_CONFIG.forEach((config) => {
            const rawMessages = this.props[config.key].messages
            if (rawMessages) {
                categories.push(<ReviewCategory key={config.key} baseUri={this.baseUri()} config={config} messages={rawMessages}/>)
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
        const { config, baseUri } = this.props
        // Loop each message to create an entry
        let messages = []

        // messages is keyed by ID, so loop this first
        for (let id in this.props.messages) {
            const itemMessages = this.props.messages[id]
            const firstMessage = itemMessages[0]
            let uri
            if (firstMessage.reference && firstMessage.reference.id) {
                uri = `${baseUri}/${config.key}/edit/${firstMessage.reference.id}`
            }
            const info = itemMessages.map((message, idx) => {
                return (
                    <div key={idx} className={CLASSES[message.level]}>
                        {message.level}: {message.message}
                    </div>
                )
            })
            messages.push(
                <li key={id}>
                    <Link to={uri}>
                        {info}
                    </Link>
                </li>
            )
            itemMessages.forEach((message, idx) => {

            })
        }

        return (
            <div>
                <h3>{config.name}</h3>
                <ul>{messages}</ul>
            </div>
        )
    }
}
