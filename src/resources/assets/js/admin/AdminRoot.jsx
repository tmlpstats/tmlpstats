import React from 'react'

export default class AdminRoot extends React.Component {
    render() {
        return (
            <div>
                <h1>Admin</h1>
                {this.props.children}
            </div>
        )
    }
}
