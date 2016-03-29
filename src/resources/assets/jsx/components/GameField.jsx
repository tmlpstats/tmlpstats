import React from 'react';


var GameField = React.createClass({
    getInitialState: function() {
        return {op: 'default'}
    },
    informParentOfChange: function () {
        // Tell LiveScoreboard it's time to update this value
        this.setState({op: 'updating'});
        var self = this;
        var revert = function() {
            if (self.state.op == 'success' || self.state.op == 'failed') {
                self.setState({op: 'default'})
            }
        }
        this.props.updateGameValue(this.props.field).done(function() {
            self.setState({op: 'success'})
            setTimeout(revert, 5000);
        }).fail(function() {
            self.setState({op: 'failed'})
            setTimeout(revert, 8000);
        })
    },
    handleChange: function(e) {
        var field = this.props.field,
            value = e.target.value;

        // Pass the updated value to LiveScoreboard who owns the state object
        this.props.handleFieldOnChange(field, value);
    },
    handleBlur: function(e) {
        e.target.value = this.props.gameValue;
    },
    handleKeyPress: function (e) {
        if (e.key == 'Enter') {
            this.informParentOfChange();
        }
    },
    renderEditable: function() {
        var after = <span className="glyphicon glyphicon-pencil" data-toggle="tooltip" title="Use the field to the left and hit enter to set a score."></span>
        if (this.props.suffix) {
            after = this.props.suffix;
        }
        var disabled = false;
        var container = '';
        switch (this.state.op) {
            case "updating":
                disabled = true;
                after = <span className="glyphicon glyphicon-refresh"></span>
                break;
            case "success":
                container = "has-success";
                after = <span className="glyphicon glyphicon-ok" style={{color: "green"}}></span>
                break;
            case "failed":
                container = "has-failure";
                after = <span className="glyphicon glyphicon-remove" style={{color: "red"}}></span>
                break;
        }
        
        return (
            <div className="input-group live-scoreboard-group {container}">
                <input
                    type="text"
                    value={this.props.gameValue}
                    onChange={this.handleChange}
                    onBlur={this.handleBlur}
                    onKeyPress={this.handleKeyPress}
                    disabled={disabled}
                    className="form-control"
                />
                <span className="input-group-addon" aria-hidden="true">
                    {after}
                </span>
            </div>
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
