import PropTypes from 'prop-types'
import React, { PureComponent } from 'react'
import SimpleMarkdown from 'simple-markdown'

import { Alert } from './ui_basic'

/**
 * simple SystemMessages class for the overarching system messages.
 *
 * This is a 'pure' component which expects data to be passed to it.
 */
export class SystemMessages extends PureComponent {
    static propTypes = {
        messages: PropTypes.array
    }
    render() {
        const { messages } = this.props
        if (!messages || !messages.length) {
            return <span></span>
        }

        let items = messages.map((message, i) => {
            const parsed = SimpleMarkdown.defaultBlockParse(message.content)
            return (
                <Alert key={i} alert={message.level || 'info'}>
                    &nbsp;<b>{message.title || 'System Message'}</b> at {message.createdAt}
                    {SimpleMarkdown.defaultOutput(parsed)}
                </Alert>
            )
        })

        return (items.length == 1)? items[0] : <div>{items}</div>
    }
}
