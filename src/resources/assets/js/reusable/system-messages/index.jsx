import Immutable from 'immutable'
import PropTypes from 'prop-types'
import React, { PureComponent } from 'react'
import { parse, reactOutput } from '../markdown'

import { Alert } from '../ui_basic'

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
            const parsed = parse(message.content)
            const title = (message.updatedAt)? `Updated at ${message.updatedAt}` : undefined

            const mDismiss = onDismiss? function() { onDismiss(message.id, ...arguments) } : undefined
            const renderTitle = (glyph) => <h4 title={title}>{glyph} {message.title || 'System Message'}</h4>
            items.push(
                <Alert key={message.id} alert={message.level || 'info'} onDismiss={mDismiss} renderTitle={renderTitle}>
                    {reactOutput(parsed)}
                </Alert>
            )
        })

        return (items.length == 1)? items[0] : <div>{items}</div>
    }
}
