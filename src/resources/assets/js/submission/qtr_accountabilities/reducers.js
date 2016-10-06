import { modelReducer } from 'react-redux-form'


const mr = modelReducer('submission.qtr_accountabilities')

export default function qaReducer(state={},action) {
    return mr(state, action)
}
