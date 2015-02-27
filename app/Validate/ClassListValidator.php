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
        $yesValidator            = v::string()->regex('/^[Y]$/i');
        $yesOrNullValidator      = v::when(v::nullValue(), v::alwaysValid(), $yesValidator);
        $equalsTeamYearValidator = v::when(v::nullValue(), v::alwaysValid(), v::equals($this->data->teamYear));

        $wdTypes = array(
            '1 AP',
            '1 AP',
            '1 FIN',
            '1 MOA',
            '1 NW',
            '1 OOC',
            '1 T',
            '2 AP',
            '2 FIN',
            '2 MOA',
            '2 NW',
            '2 OOC',
            '2 T',
            'R AP',
            'R FIN',
            'R MOA',
            'R NW',
            'R OOC',
            'R T',
        );

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
        $this->dataValidators['wd']                  = v::when(v::nullValue(), v::alwaysValid(), v::in($wdTypes));
        $this->dataValidators['wbo']                 = $equalsTeamYearValidator;
        $this->dataValidators['rereg']               = $equalsTeamYearValidator;
        $this->dataValidators['excep']               = $equalsTeamYearValidator;
        $this->dataValidators['travel']              = $yesOrNullValidator;
        $this->dataValidators['room']                = $yesOrNullValidator;
        // Skipping comment
        $this->dataValidators['gitw']                = v::when(v::nullValue(), v::alwaysValid(), v::string()->regex('/^[EI]$/i'));
        $this->dataValidators['tdo']                 = v::when(v::nullValue(), v::alwaysValid(), v::string()->regex('/^[YN]$/i'));
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
        } else {
            if (is_null($this->data->tdo)) {
                $this->addMessage("No value provided for TDO.", 'error');
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
            if (!is_null($this->data->wd) && !is_null($this->data->wbo)) {
                $this->addMessage("Both WD and WBO are set. Only one should be set.", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->ctw)) {
                $this->addMessage("Both WD/WBO and CTW are set. CTW should not be set after the team member has withdrawn.", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->wd)) {
                $value = $this->data->wd;
                if ($value[0] != $this->data->teamYear && ($value[0] == 'R' && $this->data->teamYear != 2)) {
                    $this->addMessage("The program year specified for WD doesn't match the team members program year. It should match the value in Wknd or X In", 'error');
                    $this->isValid = false;
                }
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
