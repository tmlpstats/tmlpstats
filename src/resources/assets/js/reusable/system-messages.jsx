import Immutable from 'immutable'
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
        onDismiss: PropTypes.func,
        messages: PropTypes.instanceOf(Immutable.List)
    }
    render() {
        const { messages, onDismiss } = this.props
        if (!messages || !messages.size) {
            return <span></span>
        }

        let items = []
        messages.forEach((message) => {
            if (message.dismissed) {
                return
            }
            const parsed = SimpleMarkdown.defaultBlockParse(message.content)
            const title = (message.updatedAt)? `Updated at ${message.updatedAt}` : undefined

            const mDismiss = onDismiss? function() { onDismiss(message.id, ...arguments) } : undefined
            items.push(
                <Alert key={message.id} alert={message.level || 'info'} onDismiss={mDismiss}>
                    &nbsp;<b title={title}>{message.title || 'System Message'}</b>
                    {SimpleMarkdown.defaultOutput(parsed)}
                </Alert>
            )
        })

        return (items.length == 1)? items[0] : <div>{items}</div>
    }
}
