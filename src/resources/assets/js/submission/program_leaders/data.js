import SimpleReduxLoader from '../../reusable/redux_loader/simple'
import { MessageManager } from '../../reusable/reducers'
import Api from '../../api'

export const programLeadersData = new SimpleReduxLoader({
    prefix: 'submission.program_leaders',
    extraLMS: ['saveState'],
    actions: {
        load: {
            api: Api.ProgramLeader.allForCenter,
            setLoaded: true,
        },
        save: {
            api: Api.ProgramLeader.stash,
            setLoaded: true,
        }
    }
})

export const messages = new MessageManager('program_leaders')
