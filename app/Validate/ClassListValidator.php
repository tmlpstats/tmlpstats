<?php
namespace TmlpStats\Validate;

use Respect\Validation\Validator as v;

class ClassListValidator extends ValidatorAbstract
{
    protected $classDisplayName = 'Class List';

    protected function populateValidators()
    {
        $nameValidator           = v::string()->notEmpty();
        $rowIdValidator          = v::numeric()->positive();
        $yesValidator            = v::when(v::nullValue(), v::alwaysValid(), v::string()->regex('/^[Yy]$/'));
        $equalsTeamYearValidator = v::when(v::nullValue(), v::alwaysValid(), v::equals($this->data->teamYear));

        $this->dataValidators['firstName']           = $nameValidator;
        $this->dataValidators['lastName']            = $nameValidator;
        $this->dataValidators['teamYear']            = v::numeric()->between(1, 2, true);
        // Skipping accountability
        $this->dataValidators['completionQuarterId'] = $rowIdValidator;
        // Skipping center (auto-generated)
        $this->dataValidators['statsReportId']       = $rowIdValidator;


        // Skipping reporting date (auto-generated)
        // Skipping team member id (auto-generated)
        $this->dataValidators['wknd']                = $equalsTeamYearValidator;
        $this->dataValidators['xferOut']             = $equalsTeamYearValidator;
        $this->dataValidators['xferIn']              = $equalsTeamYearValidator;
        $this->dataValidators['ctw']                 = $equalsTeamYearValidator;
        $this->dataValidators['wd']                  = $equalsTeamYearValidator;
        $this->dataValidators['wbo']                 = $equalsTeamYearValidator;
        $this->dataValidators['rereg']               = $equalsTeamYearValidator;
        $this->dataValidators['excep']               = $equalsTeamYearValidator;
        // Skipping reason_withdraw
        $this->dataValidators['travel']              = $yesValidator;
        $this->dataValidators['room']                = $yesValidator;
        // Skipping comment
        $this->dataValidators['gitw']                = v::when(v::nullValue(), v::alwaysValid(), v::string()->notEmpty()->regex('/[IiEe]/'));
        $this->dataValidators['tdo']                 = v::when(v::nullValue(), v::alwaysValid(), v::numeric()->between(0, 2, true));
        $this->dataValidators['additionalTdo']       = v::when(v::nullValue(), v::alwaysValid(), v::numeric()->between(0, 3, true));
        // Skipping quarter (auto-generated)
    }

    protected function validate()
    {
        $this->validateGitw();
        $this->validateTdo();
        $this->validateTeamYear();
        $this->validateWithdraw();
        $this->validateTravel();

        return $this->isValid;
    }

    protected function validateGitw()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (!is_null($this->data->gitw)) {
                $this->addMessage("If team member has withdrawn, please leave GITW empty.", 'error');
                $this->isValid = false;
            }
        } else {
            if (is_null($this->data->gitw)) {
                $this->addMessage("No value provided for GITW.", 'error');
                $this->isValid = false;
            }
        }
    }

    protected function validateTdo()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (!is_null($this->data->tdo)) {
                $this->addMessage("If team member has withdrawn, please leave TDO empty.", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->additionalTdo) && $this->data->additionalTdo > 0) {
                $this->addMessage("If team member has withdrawn, please leave Additional TDO empty.", 'error');
                $this->isValid = false;
            }
        } else {
            if (is_null($this->data->tdo)) {
                $this->addMessage("No value provided for TDO.", 'error');
                $this->isValid = false;
            }
            if ((is_null($this->data->tdo) || $this->data->tdo == 0) && (!is_null($this->data->additionalTdo) && $this->data->additionalTdo > 0)) {
                $this->addMessage("Additional TDO provided, but regular TDO is 0.", 'error');
                $this->isValid = false;
            }
        }
    }

    protected function validateTeamYear()
    {
        if (is_null($this->data->wknd) && is_null($this->data->xferIn)) {
            $this->addMessage("No value provided for Wknd or X In. One should be {$this->data->teamYear}.", 'error');
            $this->isValid = false;
        } else if (!is_null($this->data->wknd) && !is_null($this->data->xferIn)) {
            $this->addMessage("Only one of Wknd and X In should be set.", 'error');
            $this->isValid = false;
        }
    }

    protected function validateWithdraw()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (is_null($this->data->reasonWithdraw)) {
                $this->addMessage("No Reason for Withdraw provided.", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->wd) && !is_null($this->data->wbo)) {
                $this->addMessage("Both WD and WBO are set. Only one should be set.", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->ctw)) {
                $this->addMessage("Both WD/WBO and CTW are set. CTW should not be set after the team member has withdrawn.", 'error');
                $this->isValid = false;
            }
        }
    }

    protected function validateTravel()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wbo))
        {
            return; // Not required if withdrawn
        }

        $statsReport = $this->getStatsReport();
        $secondClassroomDate = $statsReport->quarter->classroom2Date;
        if ($statsReport->reportingDate->gt($secondClassroomDate)) {
            if (is_null($this->data->travel) && is_null($this->data->comment)) {
                $this->addMessage("Either travel must be complete and marked with a Y in the Travel colunm, or a comment providing a specific promise must be provided", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->room) && is_null($this->data->comment)) {
                $this->addMessage("Either rooming must be complete and marked with a Y in the Room colunm, or a comment providing a specific promise must be provided", 'error');
                $this->isValid = false;
            }
        }
    }
}
