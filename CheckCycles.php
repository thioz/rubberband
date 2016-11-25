<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExpCalc {

	protected $type = 'd';
	protected $recAmount = 1;
	protected $recDow;
	protected $recDom;

	public function __construct() {
		;
	}

	function setType($t) {
		$this->type = $t;
		return $this;
	}

	function setAmount($a) {
		$this->recAmount = $a;
		return $this;
	}

	function setDow($a) {
		$this->recDow = $a;
		return $this;
	}

	function setDom($a) {
		$this->recDom = $a;
		return $this;
	}

	function getNext($date = false) {
		if (!$date) {
			$ts = time();
		}
		else {
			$ts = strtotime($date);
		}

		$nextTs = $this->calcNextDate($ts);
		return $nextTs;
	}

	function calcNextDate($ts) {
		$int = $this->getInterval();
		$nextdate = strtotime('+' . $this->recAmount . $int, $ts);
		if ($int == 'days') {
			return $nextdate;
		}

		if ($int == 'months') {

			if ($this->recDom == -1) {
				$nextdate = strtotime(date('Y-m-01', $nextdate));
			}
			elseif ($this->recDom == -2) {
				$numdays = date('t', $nextdate);
				$nextdate = strtotime(date('Y-m-' . $numdays, $nextdate));
			}
			else {
				$nextdate = strtotime(date('Y-m-' . $this->recDom, $nextdate));
			}
		}

		if ($int == 'weeks') {
			$dow = $this->recDow;
			$cday = date('N', $nextdate);
			$nextdate = strtotime('+' . ($dow - $cday) . 'days', $nextdate);
		}

		return $nextdate;
	}

	function getInterval() {
		switch ($this->type) {
			case 'd':
				return 'days';
			case 'w':
				return 'weeks';
			case 'm':
				return 'months';
			case 'y':
				return 'years';
		}
	}

}

class CheckCycles extends Command {

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'cycles:check';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		$subscriptions = \App\Model\Subscription::where('expires_at', '<=', date('Y-m-d'))
			->where('subscription_status_id','=',1)->orWhere('account_cycle_rule_id', '=', null)->get();
		foreach ($subscriptions as $subscription) {
			if (!$subscription->account_cycle_rule_id) {
				$cycle = $subscription->cycle;

				$rule = $cycle->rules()->orderBy('recurrence_order')->first();
				if ($rule->recurrence_type == 'o') {
					$subscription->account_cycle_rule_id = $rule->id;
					$subscription->expires_at = date('Y-m-d', strtotime('+1 days'));
					$subscription->save();
				}
			}
			else {
				$cycle = $subscription->cycle;
				$currentRule = $subscription->rule;

				print_r($currentRule->toArray());
				if ($currentRule->recurrence_type == 'o') {

					$nextRule = $cycle->rules()->where('id', '>', $subscription->account_cycle_rule_id)->orderBy('recurrence_order')->first();
					if ($nextRule) {
						$subscription->account_cycle_rule_id = $nextRule->id;
						$subscription->recurrence_length = 0;
						$calc = new ExpCalc();
						$calc->setType($nextRule->recurrence_type)->setDom($nextRule->recurrence_amount_dom)
							->setDow($nextRule->recurrence_amount_weekday)
							->setAmount($nextRule->recurrence_amount);

						$nextTs = $calc->getNext();
						$subscription->expires_at = date('Y-m-d', $nextTs);
						$subscription->save();
					}
				}
				else {


					if ($currentRule->recurrence_length != 0) {
						if ($subscription->recurrence_length >= $currentRule->recurrence_length) {
							$nextRule = $cycle->rules()->where('id', '>', $subscription->account_cycle_rule_id)->orderBy('recurrence_order')->first();
							if ($nextRule) {
								$subscription->account_cycle_rule_id = $nextRule->id;
								$subscription->recurrence_length = 0;
								$subscription->save();
							}
							else{
								$subscription->subscription_status_id=3;
								$subscription->save();
							}
							continue;
						}
					}
					$subscription->recurrence_length+=1;
					$calc = new ExpCalc();
					$calc->setType($currentRule->recurrence_type)->setDom($currentRule->recurrence_amount_dom)
						->setDow($currentRule->recurrence_amount_weekday)
						->setAmount($currentRule->recurrence_amount);

					$nextTs = $calc->getNext($subscription->expires_at->format('Y-m-d'));
					$subscription->expires_at = date('Y-m-d', $nextTs);
					$subscription->save();
				}
			}
		}
		echo '<pre>';
		print_r(count($subscriptions));
		echo '</pre>';
	}

}
