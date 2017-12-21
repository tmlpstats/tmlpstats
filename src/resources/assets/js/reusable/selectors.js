

// Create a memoizer that does efficient work on key-based mappings (objects).
//
// If you need to do some work like annotating objects, but only want to do so
// if values at a key have changed, in a reduxy triple-equals kind of way, then
// this selector is your ticket.
//
export function createKeyBasedMemoizer() {
    let inputCache = {}
    let outputCache = {}

    function keyMemoizer(input, callback) {
        let output = {}
        for (const k in input) {
            const d = input[k]
            if (inputCache[k] === d) {
                output[k] = outputCache[k]
            } else {
                inputCache[k] = d
                output[k] = outputCache[k] = callback(d, k)
            }
        }
        return output
    }

    return keyMemoizer
}
