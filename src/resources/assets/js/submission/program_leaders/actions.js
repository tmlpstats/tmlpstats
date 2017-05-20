import { actions as formActions } from 'react-redux-form'
import { objectAssign } from '../../reusable/ponyfill'
import { getMessages } from '../../reusable/ajax_utils'
import { programLeadersData, messages } from './data'
import { REVEIW_LEADER_FORM_KEY } from './reducers'

export function loadProgramLeaders(centerId, reportingDate) {
    const params = {
        center: centerId,
        reportingDate: reportingDate,
        includeInProgress: true
    }
    return programLeadersData.load(params, {
        successHandler: initializeProgramLeaders
    })
}
function initializeProgramLeaders(data) {
    return (dispatch) => {
        dispatch(programLeadersData.replaceItems(data))
        dispatch(programLeadersData.loadState('loaded'))
    }
}

export function chooseProgramLeader(data) {
    return formActions.load(REVEIW_LEADER_FORM_KEY, data)
}

export function stashProgramLeader(center, reportingDate, data) {
    return programLeadersData.runNetworkAction('save', {center, reportingDate, data}, {
        successHandler(result, { dispatch, getState }) {
            // The request failed before creating an id (parser error)
            if (!result.storedId) {
                dispatch(programLeadersData.saveState({error: 'Validation Failed', messages: result.messages}))
                setTimeout(() => { dispatch(programLeadersData.saveState('new')) }, 3000)
                dispatch(messages.replace('create', result.messages))
                return
            }

            const newData = objectAssign({}, data, {id: result.storedId})
            dispatch(messages.replace(newData.id, getMessages(result)))
            dispatch(programLeadersData.replaceItem(newData.id, newData))

            if (!data.id) {
                // If this is a new entry, clear out any messages
                dispatch(messages.replace('create', []))

                // and update the accontability reference
                const { submission: { program_leaders: { programLeaders } } } = getState()
                const meta = objectAssign({}, programLeaders.data.meta, {[data.accountability]: result.storedId})
                dispatch(programLeadersData.replaceItems({meta: meta}))
            }
        },

        failHandler(err, { dispatch }) {
            // If this is a parser error, we won't have an ID yet, use 'create'
            const id = data.id ? data.id : 'create'
            dispatch(messages.replace(id, getMessages(err)))
        }
    })
}
