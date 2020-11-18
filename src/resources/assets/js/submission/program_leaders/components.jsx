import { Form, SimpleField, BooleanSelect, SimpleFormGroup } from '../../reusable/form_utils'
import { connectRedux, delayDispatch, rebind } from '../../reusable/dispatch'
import { ButtonStateFlip, MessagesComponent, scrollIntoView } from '../../reusable/ui_basic'
import { objectAssign } from '../../reusable/ponyfill'
import { React } from '../base_components'

import { ProgramLeadersBase, ATTENDING_LABELS, ACCOUNTABILITY_DISPLAY } from './components-base'
import { ProgramLeadersIndex } from './components-index'

import * as actions from './actions'
import { REVEIW_LEADER_FORM_KEY } from './reducers'

export { ProgramLeadersIndex }

export class _EditCreate extends ProgramLeadersBase {

    constructor(props) {
        super(props)
        rebind(this, 'saveProgramLeader', 'goBack')
    }

    isNewProgramLeader() {
        const { currentLeader } = this.props
        return (!currentLeader.id || parseInt(currentLeader.id) < 0)
    }

    goBack() {
        this.context.router.push(this.indexBaseUri())
    }

    renderMessages() {
        let messages = []
        const programLeader = this.props.currentLeader

        if (programLeader && programLeader.id) {
            messages = this.props.messages[programLeader.id]
        } else if (this.isNewProgramLeader() && this.props.messages['create']) {
            messages = this.props.messages['create']
        }

        return <MessagesComponent messages={messages} />
    }

    renderBackButton() {
        return <button type="button" className="btn btn-default" onClick={this.goBack}>Back</button>
    }

    render() {
        if (!this.checkLoading()) {
            return this.renderBasicLoading()
        }
        const column = 'col-md-6'
        const modelKey = REVEIW_LEADER_FORM_KEY

        return (
            <div>
                {this.renderTitle()}

                {this.renderMessages()}

                <Form className="form-horizontal" model={modelKey} onSubmit={this.saveProgramLeader}>
                    {this.renderContent(modelKey, column)}
                    <div className="form-group">
                        <div className={column+' col-md-offset-1'}>
                            {this.renderBackButton()}
                            <ButtonStateFlip loadState={this.props.programLeaders.saveState} isSubmit={true}>Save</ButtonStateFlip>
                            {this.renderSecondaryButton()}
                        </div>
                    </div>
                </Form>
            </div>
        )
    }

    saveProgramLeader(data) {
        const { centerId, reportingDate, accountability } = this.props.params

        let stashData = objectAssign({}, data, {accountability: accountability})

        this.props.dispatch(actions.stashProgramLeader(centerId, reportingDate, stashData)).then((result) => {
            if (!result) {
                return
            }

            if (result.messages && result.messages.length) {
                scrollIntoView('react-routed-flow', 10)

                // Redirect to edit view if there are warning messages
                if (this.isNewProgramLeader() && result.valid) {
                    this.context.router.push(`${this.programLeadersBaseUri()}/edit/${accountability}`)
                }
            } else if (result.valid) {
                this.context.router.push(this.indexBaseUri())
                // Reset currentLeader so if we visit Add again, it'll be blank
                delayDispatch(this.props.dispatch, actions.chooseProgramLeader({}))
            }

            return result
        })
    }
}

@connectRedux()
export class ProgramLeadersEdit extends _EditCreate {

    constructor(props) {
        super(props)
        rebind(this, 'addNew')
    }

    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }

        const { currentLeader, params, dispatch, programLeaders } = this.props
        const id = programLeaders.data.meta[params.accountability]
        const currentAccountable = programLeaders.data[id]

        if (!currentLeader || (currentAccountable && currentLeader.id != currentAccountable.id)) {
            delayDispatch(dispatch, actions.chooseProgramLeader(currentAccountable))
            return false
        }

        return true
    }

    addNew() {
        this.context.router.push(`${this.programLeadersBaseUri()}/add/${this.props.params.accountability}`)
    }

    renderTitle() {
        const { accountability } = this.props.params
        return <h3>Edit {ACCOUNTABILITY_DISPLAY.get(accountability)}</h3>
    }

    renderSecondaryButton() {
        return <button type="button" className="btn btn-success" onClick={this.addNew}>New</button>
    }

    renderContent(modelKey, column) {
        return (
            <div className="row">
                <div className={column+' tmBox'}>
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass={column} />
                    <SimpleField label="Last Name Initial" model={modelKey+'.lastName'} divClass={column} />
                    <SimpleField label="Email" model={modelKey+'.email'} divClass={column} customField={true}>
                        <input type="email" className="form-control" />
                    </SimpleField>
                    <SimpleField label="Phone" model={modelKey+'.phone'} divClass={column} />

                    <SimpleFormGroup label="Attending Weekend" divClass={column}>
                        <BooleanSelect  labels={ATTENDING_LABELS}
                                        model={modelKey+'.attendingWeekend'}
                                        emptyChoice=" "
                                        className="form-control boolSelect" />
                    </SimpleFormGroup>
                </div>
            </div>
        )
    }
}

@connectRedux()
export class ProgramLeadersAdd extends _EditCreate {
    defaultProgramLeader = {firstName: '', lastName: '', email: '', phone: ''}
    constructor(props) {
        super(props)
        rebind(this, 'goBack')
    }

    checkLoading() {
        if (!super.checkLoading()) {
            return false
        }
        const { currentLeader, dispatch } = this.props
        if (!currentLeader || currentLeader.firstName == undefined || currentLeader.id) {
            delayDispatch(dispatch, actions.chooseProgramLeader(this.defaultProgramLeader))
            return false
        }
        return true
    }

    renderTitle() {
        const { accountability } = this.props.params
        return <h3>Add {ACCOUNTABILITY_DISPLAY.get(accountability)}</h3>
    }

    renderSecondaryButton() {
        return
    }

    renderContent(modelKey, column) {
        return (
            <div className="row">
                <div className={column+' tmBox'}>
                    <SimpleField label="First Name" model={modelKey+'.firstName'} divClass={column} />
                    <SimpleField label="Last Name Initial" model={modelKey+'.lastName'} divClass={column} />
                    <SimpleField label="Email" model={modelKey+'.email'} divClass={column} customField={true}>
                        <input type="email" className="form-control" />
                    </SimpleField>
                    <SimpleField label="Phone" model={modelKey+'.phone'} divClass={column} />

                    <SimpleFormGroup label="Attending Weekend" divClass={column}>
                        <BooleanSelect  labels={ATTENDING_LABELS}
                                        model={modelKey+'.attendingWeekend'}
                                        emptyChoice=" "
                                        className="form-control boolSelect" />
                    </SimpleFormGroup>
                </div>
            </div>
        )
    }
}
