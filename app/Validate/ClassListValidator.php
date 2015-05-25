<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use Respect\Validation\Validator as v;

class ClassListValidator extends ValidatorAbstract
{
    protected $sheetId = ImportDocument::TAB_CLASS_LIST;

    protected function populateValidators()
    {
        $nameValidator           = v::string()->notEmpty();
        $rowIdValidator          = v::numeric()->positive();
        $yesValidator            = v::string()->regex('/^[Y]$/i');
        $yesOrNullValidator      = v::when(v::nullValue(), v::alwaysValid(), $yesValidator);

        $teamYearValidator = v::numeric()->between(1, 2, true);

        if ($this->data->teamYear == 1) {
            $indicator = 1;
        } else {
            $indicator = 2;
            if ($this->data->wknd == 'R' || $this->data->xferIn == 'R') {
                $indicator = 'R';
            }
        }
        $equalsTeamYearValidator = v::when(v::nullValue(), v::alwaysValid(), v::equals($indicator));

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
        $this->dataValidators['teamYear']            = $teamYearValidator;
        $this->dataValidators['completionQuarterId'] = $rowIdValidator;
        $this->dataValidators['statsReportId']       = $rowIdValidator;
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
        $this->dataValidators['gitw']                = v::when(v::nullValue(), v::alwaysValid(), v::string()->regex('/^[EI]$/i'));
        $this->dataValidators['tdo']                 = v::when(v::nullValue(), v::alwaysValid(), v::string()->regex('/^[YN]$/i'));

        // Skipping comment
        // Skipping accountability
        // Skipping center (auto-generated)
        // Skipping reporting date (auto-generated)
        // Skipping team member id (auto-generated)
        // Skipping quarter (auto-generated)
    }

    protected function validate()
    {
        $this->validateGitw();
        $this->validateTdo();
        $this->validateTeamYear();
        $this->validateTransfer();
        $this->validateWithdraw();
        $this->validateTravel();

        return $this->isValid;
    }

    protected function validateGitw()
    {
        if (!is_null($this->data->xferOut) || !is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (!is_null($this->data->gitw)) {
                $this->addMessage('CLASSLIST_GITW_LEAVE_BLANK');
                $this->isValid = false;
            }
        } else {
            if (is_null($this->data->gitw)) {
                $this->addMessage('CLASSLIST_GITW_MISSING');
                $this->isValid = false;
            }
        }
    }

    protected function validateTdo()
    {
        if (!is_null($this->data->xferOut) || !is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (!is_null($this->data->tdo)) {
                $this->addMessage('CLASSLIST_TDO_LEAVE_BLANK');
                $this->isValid = false;
            }
        } else {
            if (is_null($this->data->tdo)) {
                $this->addMessage('CLASSLIST_TDO_MISSING');
                $this->isValid = false;
            }
        }
    }

    protected function validateTeamYear()
    {
        if (is_null($this->data->wknd) && is_null($this->data->xferIn)) {
            $this->addMessage('CLASSLIST_WKND_MISSING', $this->data->teamYear);
            $this->isValid = false;
        } else if (!is_null($this->data->wknd) && !is_null($this->data->xferIn)) {
            $this->addMessage('CLASSLIST_WKND_XIN_ONLY_ONE');
            $this->isValid = false;
        }
    }

    protected function validateTransfer()
    {
        if (!is_null($this->data->xferIn) || !is_null($this->data->xferOut)) {

            // TODO: We probably don't need to show this every week. We need a better way to alert something for
            //       the first week.
            // Always display this message.
            $this->addMessage('CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER');

            if (is_null($this->data->comment)) {
                $this->addMessage('CLASSLIST_XFER_COMMENT_MISSING');
                $this->isValid = false;
            }
        }
    }

    protected function validateWithdraw()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wbo)) {
            if (!is_null($this->data->wd) && !is_null($this->data->wbo)) {
                $this->addMessage('CLASSLIST_WD_WBO_ONLY_ONE');
                $this->isValid = false;
            }
            if (!is_null($this->data->ctw)) {
                $this->addMessage('CLASSLIST_WD_CTW_ONLY_ONE');
                $this->isValid = false;
            }
            if (!is_null($this->data->wd)) {
                $value = $this->data->wd;
                if ($value[0] != $this->data->teamYear && ($value[0] == 'R' && $this->data->teamYear != 2)) {
                    $this->addMessage('CLASSLIST_WD_DOESNT_MATCH_YEAR');
                    $this->isValid = false;
                }
            }
            if (is_null($this->data->comment)) {
                $this->addMessage('CLASSLIST_WD_COMMENT_MISSING');
                $this->isValid = false;
            }
        } else if (!is_null($this->data->ctw)) {
            if (is_null($this->data->comment)) {
                $this->addMessage('CLASSLIST_CTW_COMMENT_MISSING');
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

        // Travel and Rooming must be reported starting after the 2nd Classroom
        $statsReport = $this->getStatsReport();
        if ($statsReport->reportingDate->gt($statsReport->quarter->classroom2Date)) {
            if (is_null($this->data->travel)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($this->data->comment)) {
                    $this->addMessage('CLASSLIST_TRAVEL_COMMENT_MISSING');
                    $this->isValid = false;
                } else {
                    $this->addMessage('CLASSLIST_TRAVEL_COMMENT_REVIEW');
                }
            }
            if (is_null($this->data->room)) {
                // Error if no comment provided, warning to look at it otherwise
                if (is_null($this->data->comment)) {
                    $this->addMessage('CLASSLIST_ROOM_COMMENT_MISSING');
                    $this->isValid = false;
                } else {
                    $this->addMessage('CLASSLIST_ROOM_COMMENT_REVIEW');
                }
            }

            // Any team member without travel AND rooming booked by 2 weeks before the end of the quarter
            // is considered in a Conversation To Withdraw
            $twoWeeksBeforeWeekend = $statsReport->quarter->endWeekendDate->subWeeks(2);
            if ($statsReport->reportingDate->gte($twoWeeksBeforeWeekend)) {
                if ((is_null($this->data->travel) || is_null($this->data->room)) && is_null($this->data->ctw)) {
                    $this->addMessage('CLASSLIST_TRAVEL_ROOM_CTW_MISSING');
                    $this->isValid = false;
                }
            }
        }

    }
}
