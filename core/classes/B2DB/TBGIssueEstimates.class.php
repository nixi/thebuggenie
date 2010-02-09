<?php

	/**
	 * Issue estimates table
	 *
	 * @author Daniel Andre Eikeland <zegenie@zegeniestudios.net>
	 * @version 2.0
	 * @license http://www.opensource.org/licenses/mozilla1.1.php Mozilla Public License 1.1 (MPL 1.1)
	 * @package thebuggenie
	 * @subpackage tables
	 */

	/**
	 * Issue estimates table
	 *
	 * @package thebuggenie
	 * @subpackage tables
	 */
	class TBGIssueEstimates extends B2DBTable
	{

		const B2DBNAME = 'issue_estimates';
		const ID = 'issue_estimates.id';
		const SCOPE = 'issue_estimates.scope';
		const ISSUE_ID = 'issue_estimates.issue_id';
		const EDITED_BY = 'issue_estimates.edited_by';
		const EDITED_AT = 'issue_estimates.edited_at';
		const ESTIMATED_MONTHS = 'issue_estimates.estimated_months';
		const ESTIMATED_WEEKS = 'issue_estimates.estimated_weeks';
		const ESTIMATED_DAYS = 'issue_estimates.estimated_days';
		const ESTIMATED_HOURS = 'issue_estimates.estimated_hours';
		const ESTIMATED_POINTS = 'issue_estimates.estimated_points';

		public function __construct()
		{
			parent::__construct(self::B2DBNAME, self::ID);
			parent::_addForeignKeyColumn(self::ISSUE_ID, B2DB::getTable('TBGIssuesTable'), TBGIssuesTable::ID);
			parent::_addForeignKeyColumn(self::EDITED_BY, B2DB::getTable('TBGUsersTable'), TBGUsersTable::ID);
			parent::_addInteger(self::EDITED_AT, 10);
			parent::_addInteger(self::ESTIMATED_MONTHS, 10);
			parent::_addInteger(self::ESTIMATED_WEEKS, 10);
			parent::_addInteger(self::ESTIMATED_DAYS, 10);
			parent::_addInteger(self::ESTIMATED_HOURS, 10);
			parent::_addFloat(self::ESTIMATED_POINTS);
			parent::_addForeignKeyColumn(self::SCOPE, B2DB::getTable('TBGScopesTable'), TBGScopesTable::ID);
		}

		public function saveEstimate($issue_id, $months, $weeks, $days, $hours, $points)
		{
			$crit = $this->getCriteria();
			$crit->addInsert(self::ESTIMATED_MONTHS, $months);
			$crit->addInsert(self::ESTIMATED_WEEKS, $weeks);
			$crit->addInsert(self::ESTIMATED_DAYS, $days);
			$crit->addInsert(self::ESTIMATED_HOURS, $hours);
			$crit->addInsert(self::ESTIMATED_POINTS, $points);
			$crit->addInsert(self::ISSUE_ID, $issue_id);
			$crit->addInsert(self::EDITED_AT, time());
			$crit->addInsert(self::EDITED_BY, TBGContext::getUser()->getID());
			$crit->addInsert(self::SCOPE, TBGContext::getScope()->getID());
			$this->doInsert($crit);
		}

		public function getEstimatesByDateAndIssueIDs($startdate, $enddate, $issue_ids)
		{
			$retarr = array();
			$sd = $startdate;
			while ($sd < $enddate)
			{
				$retarr[date('md', $sd)] = array();
				$sd += 86400;
			}

			if (count($issue_ids))
			{
				$crit = $this->getCriteria();
				//$crit->addWhere(self::EDITED_AT, $startdate, B2DBCriteria::DB_GREATER_THAN_EQUAL);
				$crit->addWhere(self::EDITED_AT, $enddate, B2DBCriteria::DB_LESS_THAN_EQUAL);
				$crit->addWhere(self::ISSUE_ID, $issue_ids, B2DBCriteria::DB_IN);
				$crit->addOrderBy(self::EDITED_AT, B2DBCriteria::SORT_ASC);
				$res = $this->doSelect($crit);

				if ($res)
				{
					while ($row = $res->getNextRow())
					{
						$date = ($row->get(self::EDITED_AT) >= $startdate) ? $row->get(self::EDITED_AT) : $startdate;
						$retarr[date('md', $date)][$row->get(self::ISSUE_ID)] = $row->get(self::ESTIMATED_POINTS);
					}
				}
			}
			
			foreach ($retarr as $key => $vals)
			{
				//$sum = array_sum($vals);
				//$retarr[$key] = ($sum) ? $sum : null;
				$retarr[$key] = (count($vals)) ? array_sum($vals) : null;
			}

			return $retarr;
		}
		
	}
