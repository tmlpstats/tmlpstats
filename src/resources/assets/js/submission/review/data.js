import { LoadingMultiState } from '../../reusable/reducers'
import Api from '../../api'

export const reportSubmitting = new LoadingMultiState('review/submitReport')
export const displayFlow = new LoadingMultiState('review/displayFlow')
export const DISPLAY_STATES = {main: 'new', preSubmit: 'loading', postSubmit: 'loaded'}

export const reviewLoaded = new LoadingMultiState('review/loaded')
reviewLoaded.selector = (state) => state.submission.review.loaded
