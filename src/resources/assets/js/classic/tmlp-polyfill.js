// Some systems don't have console.log.... we should make sure things don't break when we use console.log by putting in a dead function
if (!window.console) {
    window.console = {}
}
if (!window.console.log) {
    window.console.log = function() {
        // does nothing
    }
}

if (!String.prototype.startsWith) {
    String.prototype.startsWith = function(searchString, position) {
        position = position || 0
        return this.substr(position, searchString.length) === searchString
    }
}
