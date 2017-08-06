export const CLICK_COLUMN = 'tabular/click-column'
export const CLICK_ITEM = 'tabular/click-item'


export function clickColumn(table, column, modKey) {
    return {type: CLICK_COLUMN, table, column, modKey}
}
