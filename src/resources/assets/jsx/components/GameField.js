import React from 'react';


var GameField = React.createClass({
    informParentOfChange: function () {
        // Tell LiveScoreboard it's time to update this value
        this.props.updateGameValue(this.props.field);
    },
    handleChange: function(e) {
        var field = this.props.field,
            value = e.target.value;

        // Pass the updated value to LiveScoreboard who owns the state object
        this.props.handleFieldOnChange(field, value);
    },
    handleBlur: function(e) {
        this.informParentOfChange();
    },
    handleKeyPress: function (e) {
        if (e.key == 'Enter') {
            this.informParentOfChange();
        }
    },
    renderEditable: function() {
        return (
            <input
                value={this.props.gameValue}
                onChange={this.handleChange}
                onBlur={this.handleBlur}
                onKeyPress={this.handleKeyPress}
                style={{width:"50px", textAlign:"center"}}
            />
        );
    },
    renderNotEditable: function() {
        var value = this.props.gameValue;

        if (this.props.suffix) {
            value += this.props.suffix;
        }

        return (
            <span>{value}</span>
        );
    },
    render: function() {
        if (this.props.editable) {
            return this.renderEditable();
        } else {
            return this.renderNotEditable();
        }
    }
});

export default GameField;
