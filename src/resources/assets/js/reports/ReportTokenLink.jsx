import React,  {PropTypes } from 'react'

import { rebind, connectRedux } from '../reusable/dispatch'
import { Modal } from '../reusable/ui_basic'

@connectRedux()
export default class ReportTokenLink extends React.Component {
    static mapStateToProps(state) {
        return {showToken: state.reports.showToken}
    }

    static propTypes = {
        showToken: PropTypes.bool,
        token: PropTypes.string
    }

    constructor(props) {
        super(props)
        rebind(this, 'showToken', 'hideToken')
    }

    render() {
        if (this.props.showToken) {
            return (
                <Modal title="Report Link" onClose={this.hideToken}>
                    <textarea className="form-control" value={this.props.token} readOnly={true} />
                </Modal>
            )
        } else {
            return <button className="btn btn-default reportTokenLink" onClick={this.showToken}>Show Report Link</button>
        }
    }

    showToken() {
        this.flip(true)
    }

    hideToken() {
        this.flip(false)
    }

    flip(v) {
        this.props.dispatch({type: 'reports/showToken', payload: v})
    }
}
