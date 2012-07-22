<?php
	# Copyright (C) 2010 by Sam Hughes

	# Permission is hereby granted, free of charge, to any person obtaining a copy
	# of this software and associated documentation files (the "Software"), to deal
	# in the Software without restriction, including without limitation the rights
	# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	# copies of the Software, and to permit persons to whom the Software is
	# furnished to do so, subject to the following conditions:

	# The above copyright notice and this permission notice shall be included in
	# all copies or substantial portions of the Software.

	# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	# THE SOFTWARE.

	# http://qntm.org/chomsky

	# A nonterminal can be ANYTHING.
	# A terminal has to be a single-character string at the moment (multi-character strings are forthcoming).

	class Rule {

		# attributes
		private $left = null;
		private $rights = null;

		# setters
		public function __construct($left, $rights) {
			$this->setLeft($left);
			$this->setRights($rights);
		}

		private function setLeft($left) {
			$this->left = $left;
		}
		
		private function setRights($rights) {
			if(!is_array($rights)) {
				throw new Exception("Right-hand string '".print_r($rights, true)."' is not an array.");
			}
			$this->rights = $rights;
		}

		# getters
		public function getLeft() {
			return $this->left;
		}

		public function getRights() {
			return $this->rights;
		}

		public function equals($other) {
			if(!is_a($other, "Rule")) {
				return false;
			}
			if($this->getLeft() !== $other->getLeft()) {
				return false;
			}
			if($this->getRights() !== $other->getRights()) {
				return false;
			}
			return true;
		}
	}

	class ContextFreeGrammar {
	
		# attributes
		private $alphabet = array();
		private $nonterminals = array();
		private $rules = array();
		private $startSymbol = null;
		
		# setters
		public function __construct($startSymbol, $terminals=array(), $otherNonterminals=array(), $rules=array()) {
			$this->addNonterminal($startSymbol);
			$this->setStartSymbol($startSymbol);
			$this->addTerminals($terminals);
			$this->addNonterminals($otherNonterminals);
			$this->addRules($rules);
		}
		
		public function addTerminal($terminal) {
			if(!is_string($terminal)) {
				throw new Exception("Can't add terminal '".print_r($terminal, true)."', not a string.");
			}
			if(in_array($terminal, $this->getTerminals(), true)) {
				throw new Exception("Can't add terminal '".$terminal."', already in A.");
			}
			if(in_array($terminal, $this->getNonterminals(), true)) {
				throw new Exception("Can't add terminal '".$terminal."', already in N.");
			}
			$this->alphabet[] = $terminal;
		}
		
		public function addTerminals($terminals) {
			if(!is_array($terminals)) {
				throw new Exception("Can't add terminals '".print_r($terminals, true)."', not an array.");
			}
			foreach($terminals as $terminal) {
				$this->addTerminal($terminal);
			}
		}
		
		public function deleteTerminal($terminalToDelete) {
			if(!in_array($terminalToDelete, $this->getTerminals(), true)) {
				throw new Exception("Can't delete terminal '".print_r($terminalToDelete, true)."', not in A.");
			}
			if($this->referencesTerminal($terminalToDelete)) {
				throw new Exception("Can't delete terminal '".print_r($terminalToDelete, true)."', there is a rule which references it.");
			}
			$newTerminals = array();
			foreach($this->getTerminals() as $terminal) {
				if($terminal === $terminalToDelete) {
					continue;
				}

				$newTerminals[] = $terminal;
			}
			$this->terminals = $newTerminals;
		}
		
		public function deleteTerminals($terminals) {
			if(!is_array($terminals)) {
				throw new Exception("Can't delete terminals '".print_r($terminals, true)."', not an array.");
			}
			foreach($terminals as $terminal) {
				$this->deleteTerminal($terminal);
			}
		}
		
		public function addNonterminal($nonterminal) {
			if(in_array($nonterminal, $this->getTerminals(), true)) {
				throw new Exception("Can't add nonterminal '".print_r($nonterminal, true)."', already in A.");
			}
			if(in_array($nonterminal, $this->getNonterminals(), true)) {
				throw new Exception("Can't add nonterminal '".print_r($nonterminal, true)."', already in N.");
			}
			$this->nonterminals[] = $nonterminal;
		}
		
		public function addNonterminals($nonterminals) {
			if(!is_array($nonterminals)) {
				throw new Exception("Can't add nonterminals '".print_r($nonterminals, true)."', not an array.");
			}
			foreach($nonterminals as $nonterminal) {
				$this->addNonterminal($nonterminal);
			}
		}
		
		public function deleteNonterminal($nonterminalToDelete) {
			if(!in_array($nonterminalToDelete, $this->getNonterminals(), true)) {
				throw new Exception("Can't delete nonterminal '".print_r($nonterminalToDelete, true)."', doesn't exist in N.");
			}
			if($this->referencesNonterminal($nonterminalToDelete)) {
				throw new Exception("Can't delete nonterminal '".print_r($nonterminalToDelete, true)."', there is a rule which references it.");
			}
			$newNonterminals = array();
			foreach($this->getNonterminals() as $nonterminal) {
				if($nonterminal === $nonterminalToDelete) {
					continue;
				}
				
				$newNonterminals[] = $nonterminal;
			}
			$this->nonterminals = $newNonterminals;
		}
		
		public function deleteNonterminals($nonterminals) {
			if(!is_array($nonterminals)) {
				throw new Exception("Can't delete nonterminals '".print_r($nonterminals, true)."', not an array.");
			}
			foreach($nonterminals as $nonterminal) {
				$this->deleteNonterminal($nonterminal);
			}
		}
		
		public function addRule($rule) {
			if(!is_a($rule, "Rule")) {
				throw new Exception("Can't add rule '".print_r($rule, true)."', not a rule.");
			}
			$left = $rule->getLeft();
			if(!in_array($left, $this->getNonterminals(), true)) {
				throw new Exception("Can't add rule with left symbol '".print_r($left, true)."', not in N.");
			}
			$rights = $rule->getRights();
			if(!is_array($rights)) {
				throw new Exception("Can't add rule with right-hand side '".print_r($rights, true)."', not an array.");
			}
			foreach($rights as $right) {
				if(!in_array($right, $this->getAllSymbols(), true)) {
					throw new Exception("Can't add rule with right symbol '".print_r($right, true)."', not in A or N.");
				}
			}
			if($this->ruleExists($rule)) {
				throw new Exception("Can't add rule '".print_r($rule, true)."', already exists.");
			}
			$this->rules[] = $rule;
		}
		
		public function addRules($rules) {
			if(!is_array($rules)) {
				throw new Exception("Can't add rules '".print_r($rules, true)."', not an array.");
			}
			foreach($rules as $rule) {
				$this->addRule($rule);
			}
		}
		
		public function deleteRule($ruleToDelete) {
			if(!$this->ruleExists($ruleToDelete)) {
				throw new Exception("Can't delete rule '".print_r($ruleToDelete, true)."', doesn't exist.");
			}
			$newRules = array();
			foreach($this->getRules() as $rule) {
				if($rule->equals($ruleToDelete)) {
					continue;
				}

				$newRules[] = $rule;
			}
			$this->rules = $newRules;
		}
		
		public function deleteRules($rulesToDelete) {
			if(!is_array($rulesToDelete)) {
				throw new Exception("Can't delete rules '".print_r($rulesToDelete, true)."', not an array.");
			}
			foreach($rulesToDelete as $ruleToDelete) {
				$this->deleteRule($ruleToDelete);
			}
		}
		
		public function setStartSymbol($startSymbol) {
			if(!in_array($startSymbol, $this->getNonterminals(), true)) {
				throw new Exception("Can't set starting symbol to '".print_r($startSymbol, true)."', is not in N.");
			}
			$this->startSymbol = $startSymbol;
		}
		
		# getters
		public function getTerminals() {
			return $this->alphabet;
		}

		public function getReferencedTerminals() {
			$referenced = array();
			foreach($this->getTerminals() as $terminal) {
				if($this->referencesTerminal($terminal)) {
					$referenced[] = $terminal;
				}
			}
			
			return $referenced;
		}

		public function getUnreferencedTerminals() {
			return array_diff($this->getTerminals(), $this->getReferencedTerminals());
		}

		public function referencesTerminal($terminal) {
			foreach($this->getRules() as $rule) {
				if(in_array($terminal, $rule->getRights(), true)) {
					return true;
				}
			}
			return false;
		}
		
		public function getNonterminals() {
			return $this->nonterminals;
		}

		public function getRulelessNonterminals() {
			$ruleless = array();
			foreach($this->getNonterminals() as $nonterminal) {
				if(count($this->getRulesFor($nonterminal)) === 0) {
					$ruleless[] = $nonterminal;
				}
			}
			return $ruleless;
		}
		
		public function getReachableNonterminals() {

			// fixed point calculation
			// crawl the grammar making a list of reachable nonterminals
			$reachable = array($this->getStartSymbol());
			for($i = 0; $i < count($reachable); $i++) {
				$nonterminal = $reachable[$i];
				
				foreach($this->getRulesFor($nonterminal) as $rule) {
					foreach($rule->getRights() as $right) {
						// ignore everything except more nonterminals
						if(!in_array($right, $this->getNonterminals(), true)) {
							continue;
						}

						// no dupes!
						if(in_array($right, $reachable, true)) {
							continue;
						}
						
						// add one
						$reachable[] = $right;
					}
				}
			}
			
			return $reachable;
		}
		
		public function getUnreachableNonterminals() {
			return array_diff($this->getNonterminals(), $this->getReachableNonterminals());
		}
		
		public function referencesNonterminal($nonterminal) {
			foreach($this->getRules() as $rule) {
				if($rule->getLeft() === $nonterminal) {
					return true;
				}
				if(in_array($nonterminal, $rule->getRights(), true)) {
					return true;
				}
			}
			return false;
		}
		
		public function getAllSymbols() {
			return array_merge($this->getTerminals(), $this->getNonterminals());
		}
		
		public function getRules() {
			return $this->rules;
		}
		
		public function getRulesFor($nonterminal) {
			if(!in_array($nonterminal, $this->getNonterminals(), true)) {
				throw new Exception("Can't get rules for symbol '".print_r($nonterminal, true)."', is not in N.");
			}
			$rules = array();
			foreach($this->getRules() as $rule) {
				if($rule->getLeft() === $nonterminal) {
					$rules[] = $rule;
				}
			}
			return $rules;
		}

		public function getRulesReferencing($symbol) {
			if(!in_array($symbol, $this->getAllSymbols(), true)) {
				throw new Exception("Can't get rules referencing symbol '".print_r($symbol, true)."', not in A or N.");
			}
			$rules = array();
			foreach($this->getRules() as $rule) {
				if(in_array($symbol, $rule->getRights(), true)) {
					$rules[] = $rule;
				}
			}
			return $rules;
		}
		
		public function ruleExists($needle) {
			foreach($this->getRules() as $straw) {
				if($straw->equals($needle)) {
					return true;
				}
			}
			return false;
		}
		
		public function getStartSymbol() {
			return $this->startSymbol;
		}

		public function printRules() {
			foreach($this->getRules() as $rule) {
				print($rule->getLeft()." ->");
				if(count($rule->getRights()) === 0) {
					print(" epsilon");
				} else {
					foreach($rule->getRights() as $right) {
						print(" ");
						if(in_array($right, $this->getTerminals(), true)) {
							print("'".$right."'");
						} elseif(in_array($right, $this->getNonterminals(), true)) {
							print($right);
						} else {
							throw new Exception("Malformed rule.");
						}
					}
				}
				print("\n");
			}
			print("\n");
		}

		// Eliminate cruft. This step is technically unnecessary but the results
		// are frequently cluttered otherwise.
		public function eliminateUselesses() {
			// Some nonterminals may have no rules. That means they cannot be developed
			// further, so there is no point in creating one, so any rule referring to
			// such nonterminals can be deleted along with the nonterminals themselves
			// Loop until done
			do {
				$rulelessNonterminals = $this->getRulelessNonterminals();
				foreach($rulelessNonterminals as $rulelessNonterminal) {
					$this->deleteRules($this->getRulesReferencing($rulelessNonterminal));
					$this->deleteNonterminal($rulelessNonterminal);
				}
			} while(count($rulelessNonterminals) > 0);

			// any unreachable nonterminal can be destroyed along with all its rules
			$unreachableNonterminals = $this->getUnreachableNonterminals();
			foreach($unreachableNonterminals as $nonterminal) {
				$this->deleteRules($this->getRulesFor($nonterminal));
			}
			$this->deleteNonterminals($unreachableNonterminals);

			// likewise any unreferenced terminal
			$this->deleteTerminals($this->getUnreferencedTerminals());			
		}
		
		// eliminate all other rules depending on the specified unit rule,
		// "<A> => <B>"
		private function eliminateUnit($A, $B) {
			foreach($this->getRules() as $rule) {
				if($rule->getLeft() === $B) {
					$newRule = new Rule($A, $rule->getRights());

					// when e.g. eliminating "<A> => <B>" from "<B> => <A>",
					// we may get the useless "<B> => <B>" result which should be ignored
					if(array($newRule->getLeft()) === $newRule->getRights()) {
						continue;
					}
					
					if(!$this->ruleExists($newRule)) {
						$this->addRule($newRule);
					}
				}
			}
		}

		// eliminate rules of the form "<A> => <B>"
		private function eliminateUnits() {
			while(1) {
				foreach($this->getRules() as $rule) {
					$rights = $rule->getRights();

					// Not a singleton, no problem
					if(count($rights) !== 1) {
						continue;
					}
					
					// must be a non-terminal symbol, or no problem
					$right = $rights[0];
					if(!in_array($right, $this->getNonterminals(), true)) {
						continue;
					}

					// that's a unit! Must eliminate it!
					$this->eliminateUnit($rule->getLeft(), $right);
					$this->deleteRule($rule);

					// and START OVER
					continue 2;
				}
				
				// all rules passed, no units found: exit
				break;
			}
		}

		// e.g. if $right = array("<A>", "b", "<A>"), $left = "<A>", returns
		// array(
			// array("<A>", "b", "<A>"),
			// array("<A>", "b"),
			// array("b", "<A>"),
			// array("b")
		// )
		private function getCombos($rights, $left) {

			// find the "pivot point" in the listing
			$firstLeft = array_search($left, $rights, true);
			// e.g. 0

			// If not found, just return verbatim
			if($firstLeft === false) {
				return array($rights);
			}

			$combos = array();

			// pivot around this location

			$prefix = array_slice($rights, 0, $firstLeft);
			// e.g. array()

			$suffix = array_slice($rights, $firstLeft + 1, count($rights) - ($firstLeft + 1));
			// e.g. array("b", "<A>")

			$subCombos = $this->getCombos($suffix, $left);
			// e.g. array( array("b"), array("b", "<A>") )

			foreach($subCombos as $subCombo) {
				$combo = array_merge($prefix, array($left), $subCombo);
				// e.g. array("<A>", "b")
				// e.g. array("<A>", "b", "<A>")
				if(!in_array($combo, $combos, true)) {
					$combos[] = $combo;
				}
				
				$combo = array_merge($prefix, $subCombo);
				// e.g. array("b")
				// e.g. array("b", "<A>")
				if(!in_array($combo, $combos, true)) {
					$combos[] = $combo;
				}
			}

			return $combos;
		}
		
		// eliminate all dependence on the rule "<A> => epsilon"
		private function eliminateEpsilon($A) {
			foreach($this->getRules() as $rule) {
				
				// eliminate "<A> => epsilon" from e.g. "<S> => <A> b <A>"
				// gives four rules: "<S> => <A> b <A>", "<S> => <A> b", "<S> => b <A>", "<S> => b"
				// (the original "<A> => epsilon" will be removed later...)
				$combos = $this->getCombos($rule->getRights(), $A);
				foreach($combos as $combo) {
					$newRule = new Rule($rule->getLeft(), $combo);
					
					// when e.g. eliminating "<A> => epsilon" from "<S> => <S> <A>"
					// we may get the useless "<S> => <S>" result which should be ignored
					if(array($newRule->getLeft()) === $newRule->getRights()) {
						continue;
					}
					
					if(!$this->ruleExists($newRule)) {
						$this->addRule($newRule);
					}
				}
			}
		}

		// eliminate rules of the form <A> => epsilon where <A> is not <S>
		private function eliminateEpsilons() {
			while(1) {
				foreach($this->getRules() as $rule) {

					// not an epsilon, no problem, next rule
					if($rule->getRights() !== array()) {
						continue;
					}
					
					// "<S0> => epsilon" is still okay
					if($rule->getLeft() === $this->getStartSymbol()) {
						continue;
					}

					// an epsilon was found!!
					// get rid of it
					$this->eliminateEpsilon($rule->getLeft());
					$this->deleteRule($rule);

					// and START OVER
					continue 2;
				}
				
				// all rules passed,
				// no epsilons found: exit
				break;
			}
		}

		// return a new, unused nonterminal symbol e.g. <Z0>
		private function getNewNonterminal() {
			$i = 0;
			while(1) {
				$nonterminal = "<Z".$i.">";
				if(!in_array($nonterminal, $this->getAllSymbols(), true)) {
					return $nonterminal;
				}
				$i++;
			}
		}

		// create a new unit of the form "<Z0> => <S>" where <S> is the old start symbol
		// and <Z0> is the new one
		private function introduceS0() {
			// get <Z0>
			$newStartSymbol = $this->getNewNonterminal();

			// add <Z0>
			$this->addNonterminal($newStartSymbol);
			
			// "<Z0> => <S>"
			$this->addRule(new Rule($newStartSymbol, array($this->getStartSymbol())));

			// <Z0> becomes start symbol
			$this->setStartSymbol($newStartSymbol);
		}

		// remove all rules of the form <A> => <B> <C> <D> ...
		private function eliminateMultiples() {

			while(1) {
				foreach($this->getRules() as $oldRule) {
					// no problem, next rule
					if(count($oldRule->getRights()) <= 2) {
						continue;
					}

					// problem, eliminate rule
					
					// delete <A> => <B> <C> <D> ...
					$this->deleteRule($oldRule);

					// add new terminal <Z0>,
					$newNonterminal = $this->getNewNonterminal();
					$this->addNonterminal($newNonterminal);

					// add new rules "<A> => <B> <Z0>" and "<Z0> => <C> <D> ..."
					$oldRights = $oldRule->getRights();
					$this->addRule(new Rule($oldRule->getLeft(), array($oldRights[0], $newNonterminal)));
					$this->addRule(new Rule($newNonterminal, array_slice($oldRights, 1)));

					// and START OVER
					continue(2);
				}
				
				// all rules successfully passed with no problem
				break;
			}
		}
		
		// eliminate all rules which contain the specified terminal symbol on the
		// right, but not JUST the specified terminal.
		private function eliminateTerminal($oldTerminal) {

			// new nonterminal to stand in for the old terminal, and new rule
			$newNonterminal = $this->getNewNonterminal();
			$this->addNonterminal($newNonterminal);
			$this->addRule(new Rule($newNonterminal, array($oldTerminal)));

			// and rewrite any applicable rules
			foreach($this->getRules() as $oldRule) {

				// no rewrite
				if(count($oldRule->getRights()) < 2) {
					continue;
				}

				// no ref: no problem
				$oldRights = $oldRule->getRights();
				if(!in_array($oldTerminal, $oldRights, true)) {
					continue;
				}

				// yes rewrite
				$newRights = array();
				foreach($oldRights as $oldRight) {
					if($oldRight === $oldTerminal) {
						$newRights[] = $newNonterminal;
					} else {
						$newRights[] = $oldRight;
					}
				}
				$newRule = new Rule($oldRule->getLeft(), $newRights);

				$this->addRule($newRule);
				$this->deleteRule($oldRule);
			}
		}

		// eliminate all rules which contain a terminal symbol on the right side
		// but that don't contain JUST a terminal symbol on the right side
		private function eliminateTerminals() {

			// compile a list of all the offending terminals
			while(1) {
				foreach($this->getRules() as $rule) {
					// no problem, next rule
					if(count($rule->getRights()) < 2) {
						continue;
					}

					foreach($rule->getRights() as $right) {
					
						// no problem, next Right
						if(!in_array($right, $this->getTerminals(), true)) {
							continue;
						}

						// problem! Eliminate terminal... 
						$this->eliminateTerminal($right);

						// and START OVER
						continue(3);
					}
				}
				
				// all rules successfully consumed and
				// no terminals found: exit
				break;
			}
		}

		// Convert a CFG to Chomsky Normal Form
		public function toCnf() {
			$this->eliminateTerminals();
			$this->eliminateMultiples();
			$this->introduceS0(); // introduces a unit
			$this->eliminateEpsilons(); // may introduce units
			$this->eliminateUnits();
			$this->eliminateUselesses();
		}
	}
?>