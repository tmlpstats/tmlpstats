<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\Reader as Reader;
use Respect\Validation\Validator as v;

class TmlpRegistrationValidator extends ValidatorAbstract
{
    const MAX_DAYS_TO_SEND_APPLICATION_OUT = 2;
    const MAX_DAYS_TO_APPROVE_APPLICATION = 14;

    protected $classDisplayName = 'Current Weekly Stats';

    protected function populateValidators()
    {
        $nameValidator               = v::string()->notEmpty();
        $rowIdValidator              = v::numeric()->positive();
        $dateValidator               = v::date('Y-m-d');
        $dateOrNullValidator         = v::when(v::nullValue(), v::alwaysValid(), $dateValidator);
        $yesValidator                = v::string()->regex('/^[Y]$/i');
        $yesOrNullValidator          = v::when(v::nullValue(), v::alwaysValid(), $yesValidator);

        $weekendRegTypes = array(
            'before',
            'during',
            'after',
        );

        $incomingWeekendTypes = array(
            'current',
            'future',
        );

        $wdTypes = array(
            '1 AP',
            '1 NW',
            '1 FIN',
            '1 FW',
            '1 MOA',
            '1 NA',
            '1 OOC',
            '1 T',
            '1 RE',
            '1 WB',
            '2 AP',
            '2 NW',
            '2 FIN',
            '2 FW',
            '2 MOA',
            '2 NA',
            '2 OOC',
            '2 T',
            '2 RE',
            '2 WB',
            'R AP',
            'R NW',
            'R FIN',
            'R FW',
            'R MOA',
            'R NA',
            'R OOC',
            'R T',
            'R RE',
            'R WB',
        );



        $incomingTeamYearValidator = v::numeric()->between(1, 2, true);
        $equalsIncomingYearValidator = ($this->data->incomingTeamYear == 1)
            ? v::when(v::nullValue(), v::alwaysValid(), v::equals($this->data->incomingTeamYear))
            : v::when(v::nullValue(), v::alwaysValid(), v::in(array(2,'R'))); // R for reviewer

        $this->dataValidators['firstName']               = $nameValidator;
        $this->dataValidators['lastName']                = $nameValidator;
        $this->dataValidators['weekendReg']              = v::in($weekendRegTypes);
        $this->dataValidators['incomingWeekend']         = v::in($incomingWeekendTypes);
        $this->dataValidators['incomingTeamYear']        = $incomingTeamYearValidator;
        $this->dataValidators['isReviewer']              = v::numeric()->between(0, 1, true);
        $this->dataValidators['bef']                     = $equalsIncomingYearValidator;
        $this->dataValidators['dur']                     = $equalsIncomingYearValidator;
        $this->dataValidators['aft']                     = $equalsIncomingYearValidator;
        $this->dataValidators['appOut']                  = $equalsIncomingYearValidator;
        $this->dataValidators['appIn']                   = $equalsIncomingYearValidator;
        $this->dataValidators['appr']                    = $equalsIncomingYearValidator;
        $this->dataValidators['wd']                      = v::when(v::nullValue(), v::alwaysValid(), v::in($wdTypes));
        $this->dataValidators['regDate']                 = $dateValidator;
        $this->dataValidators['appOutDate']              = $dateOrNullValidator;
        $this->dataValidators['appInDate']               = $dateOrNullValidator;
        $this->dataValidators['apprDate']                = $dateOrNullValidator;
        $this->dataValidators['wdDate']                  = $dateOrNullValidator;
        $this->dataValidators['committedTeamMemberName'] = v::when(v::nullValue(), v::alwaysValid(), $nameValidator);
        $this->dataValidators['travel']                  = $yesOrNullValidator;
        $this->dataValidators['room']                    = $yesOrNullValidator;
        $this->dataValidators['tmlpRegistrationId']      = $rowIdValidator;
        $this->dataValidators['statsReportId']           = $rowIdValidator;
    }

    protected function validate()
    {
        $this->validateWeekendReg();
        $this->validateApprovalProcess();
        $this->validateDates();
        $this->validateComment();
        $this->validateTravel();

        return $this->isValid;
    }

    protected function validateWeekendReg()
    {
        if ((!is_null($this->data->bef) && !is_null($this->data->dur))
            || (!is_null($this->data->bef) && !is_null($this->data->aft))
            || (!is_null($this->data->dur) && !is_null($this->data->aft))
        ) {
            $this->addMessage("Weekend Reg section contains multiple {$this->data->incomingTeamYear}'s. Only one should be provided", 'error');
            $this->isValid = false;
        }
    }

    protected function validateApprovalProcess()
    {
        if (!is_null($this->data->wd) || !is_null($this->data->wdDate)) {
            $col = 'wd';
            if (is_null($this->data->wd)) {
                $this->addMessage("Withdraw date was provided, but '$col' column does not contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->wdDate)) {
                $this->addMessage("No withdraw date was provided, but '$col' column contains a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }

            $value = $this->data->wd;
            $weekendRegType = $this->getWeekendReg();
            if ($value[0] != $weekendRegType) {
                $this->addMessage("The program year specified for WD doesn't match the incoming program year. It should match the value in the Weekend Reg columns", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOut) || !is_null($this->data->appIn) || !is_null($this->data->appr)) {
                $this->addMessage("If person has withdrawn, only column '$col' should contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appr) || !is_null($this->data->apprDate)) {
            $col = 'appr';
            if (is_null($this->data->appr)) {
                $this->addMessage("Approved date was provided, but '$col' column does not contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->apprDate)) {
                $this->addMessage("No approved date was provided, but '$col' column contains a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->appInDate)) {
                $this->addMessage("No app in date provided", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage("No app out date provided", 'error');
                $this->isValid = false;
            }

            if (!is_null($this->data->appOut) || !is_null($this->data->appIn)) {
                $this->addMessage("If person is approved, only column '$col' should contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appIn) || !is_null($this->data->appInDate)) {
            $col = 'in';
            if (is_null($this->data->appIn)) {
                $this->addMessage("App in date was provided, but '$col' column does not contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->appInDate)) {
                $this->addMessage("No app in date was provided, but '$col' column contains a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage("No app out date provided", 'error');
                $this->isValid = false;
            }

            if (!is_null($this->data->appOut)) {
                $this->addMessage("If person's application is in, only column '$col' should contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
        } else if (!is_null($this->data->appOut) || !is_null($this->data->appOutDate)) {
            $col = 'out';
            if (is_null($this->data->appOut)) {
                $this->addMessage("App out date was provided, but '$col' column does not contain a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
            if (is_null($this->data->appOutDate)) {
                $this->addMessage("No app out date was provided, but '$col' column contains a {$this->data->incomingTeamYear}", 'error');
                $this->isValid = false;
            }
        }

        if (is_null($this->data->committedTeamMemberName) && is_null($this->data->wd)) {
            $this->addMessage("No committed team member provided", 'error');
            $this->isValid = false;
        }
    }

    protected function validateDates()
    {
        $statsReport = $this->getStatsReport();

        // Get Date objects from date strings
        $regDate    = is_null($this->data->regDate) ? null : $this->getDateObject($this->data->regDate);
        $appInDate  = is_null($this->data->appInDate) ? null : $this->getDateObject($this->data->appInDate);
        $appOutDate = is_null($this->data->appOutDate) ? null : $this->getDateObject($this->data->appOutDate);
        $apprDate   = is_null($this->data->apprDate) ? null : $this->getDateObject($this->data->apprDate);
        $wdDate     = is_null($this->data->wdDate) ? null : $this->getDateObject($this->data->wdDate);

        // Make sure dates for each step make sense
        if (!is_null($this->data->wdDate)) {
            if ($wdDate->lt($regDate)) {
                $this->addMessage("Withdraw date is before registration date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->apprDate) && $wdDate->lt($apprDate)) {
                $this->addMessage("Withdraw date is before approval date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appInDate) && $wdDate->lt($appInDate)) {
                $this->addMessage("Withdraw date is before app in date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $wdDate->lt($appOutDate)) {
                $this->addMessage("Withdraw date is before app out date", 'error');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->apprDate)) {
            if ($apprDate->lt($regDate)) {
                $this->addMessage("Approval date is before registration date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appInDate) && $apprDate->lt($appInDate)) {
                $this->addMessage("Approval date is before app in date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $apprDate->lt($appOutDate)) {
                $this->addMessage("Approval date is before app out date", 'error');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->appInDate)) {
            if ($appInDate->lt($regDate)) {
                $this->addMessage("App in date is before registration date", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->appOutDate) && $appInDate->lt($appOutDate)) {
                $this->addMessage("App in date is before app out date", 'error');
                $this->isValid = false;
            }
        }
        if (!is_null($this->data->appOutDate)) {
            if ($appOutDate->lt($regDate)) {
                $this->addMessage("App out date is before registration date", 'error');
                $this->isValid = false;
            }
        }

        // Make sure Weekend Reg fields match registration date
        if ($this->data->incomingWeekend == 'current') {
            $dateStr = $statsReport->quarter->startWeekendDate->format('M d, Y');
            if (!is_null($this->data->bef) && $regDate->gt($statsReport->quarter->startWeekendDate)) {
                $this->addMessage("Registration is not before quarter start date ($dateStr) but has a {$this->data->bef} in Bef column", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->dur) && $regDate->diffInDays($statsReport->quarter->startWeekendDate) > 3) {
                $this->addMessage("Registration date is not during quarter start weekend ($dateStr) but has a {$this->data->dur} in Dur column", 'error');
                $this->isValid = false;
            }
            if (!is_null($this->data->aft) && $regDate->lte($statsReport->quarter->startWeekendDate)) {
                $this->addMessage("Registration date is not after quarter start date ($dateStr) but has a {$this->data->aft} in Aft column", 'error');
                $this->isValid = false;
            }
        }

        $maxAppOutDays = static::MAX_DAYS_TO_SEND_APPLICATION_OUT;
        $maxApplicationDays = static::MAX_DAYS_TO_APPROVE_APPLICATION;
        if (is_null($this->data->wdDate)){
            // Make sure steps are taken in timely manner
            if (is_null($this->data->appOutDate)) {
                if ($regDate->diffInDays($statsReport->reportingDate) > $maxAppOutDays) {
                    $this->addMessage("Application was not sent to applicant within {$maxAppOutDays} days of registration.", 'warn');
                }
            } else if (is_null($this->data->appInDate)) {
                if ($appOutDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage("Application not returned within {$maxApplicationDays} days since sending application out. Application is not in integrity with design of application process.", 'warn');
                }
            } else if (is_null($this->data->apprDate)) {
                if ($appOutDate->diffInDays($statsReport->reportingDate) > $maxApplicationDays) {
                    $this->addMessage("Application not approved within {$maxApplicationDays} days since sending application out.", 'warn');
                }
            }
        }

        // Make sure dates are in the past
        if (!is_null($this->data->regDate) && $statsReport->reportingDate->lt($regDate)) {
            $this->addMessage("Registration date is in the future. Please check date.", 'error');
            $this->isValid = false;
        }
        if (!is_null($this->data->wdDate) && $statsReport->reportingDate->lt($wdDate)) {
            $this->addMessage("Withdraw date is in the future. Please check date.", 'error');
            $this->isValid = false;
        }
        if (!is_null($this->data->apprDate) && $statsReport->reportingDate->lt($apprDate)) {
            $this->addMessage("Approve date is in the future. Please check date.", 'error');
            $this->isValid = false;
        }
        if (!is_null($this->data->appInDate) && $statsReport->reportingDate->lt($appInDate)) {
            $this->addMessage("Application In date is in the future. Please check date.", 'error');
            $this->isValid = false;
        }
        if (!is_null($this->data->appOutDate) && $statsReport->reportingDate->lt($appOutDate)) {
            $this->addMessage("Application Out date is in the future. Please check date.", 'error');
            $this->isValid = false;
        }
    }

    protected function validateComment()
    {
        if (!is_null($this->data->wd)) {
            return; // Not required if withdrawn
        }

        if (is_null($this->data->comment) && $this->data->incomingWeekend == 'future') {
            $this->addMessage("No comment provided specifying incoming weekend for future registration", 'error');
            $this->isValid = false;
        }
    }

    protected function validateTravel()
    {
        if (!is_null($this->data->wd) || $this->data->incomingWeekend == 'future') {
            return; // Not required if withdrawn or future registration
        }

        $statsReport = $this->getStatsReport();
        if ($statsReport->reportingDate->gt($statsReport->quarter->classroom2Date)) {
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

    protected function getWeekendReg()
    {
        if (!is_null($this->data->bef)){
            return $this->data->bef;
        } else if (!is_null($this->data->aft)){
            return $this->data->aft;
        } else {
            return $this->data->dur;
        }
    }
}
