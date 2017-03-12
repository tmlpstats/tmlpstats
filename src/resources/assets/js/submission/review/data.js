import { LoadingMultiState } from '../../reusable/reducers'

export const reportSubmitting = new LoadingMultiState('review/submitReport')
export const displayFlow = new LoadingMultiState('review/displayFlow')
export const DISPLAY_STATES = {main: 'new', preSubmit: 'loading', postSubmit: 'loaded'}
